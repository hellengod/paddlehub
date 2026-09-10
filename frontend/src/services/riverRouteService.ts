import type { GeoJsonFeatureCollection } from '@/services/hydrographyDiagnosticService';
import type { RiverCoordinate } from '@/types/rivers';

export type HydrographyRouteSource = 'ANA' | 'OSM' | 'ANA + OSM';

export interface HydrographyRoute {
    coordinates: RiverCoordinate[];
    source: HydrographyRouteSource;
    distanceKm: number;
    startSnapDistanceKm: number;
    endSnapDistanceKm: number;
}

interface GraphNode {
    coordinate: RiverCoordinate;
    edges: Map<string, number>;
}

interface GraphSegment {
    startKey: string;
    endKey: string;
    start: RiverCoordinate;
    end: RiverCoordinate;
    distanceKm: number;
}

interface SegmentProjection {
    segment: GraphSegment;
    coordinate: RiverCoordinate;
    ratio: number;
    distanceKm: number;
}

interface RouteCandidate extends HydrographyRoute {
    score: number;
}

const MAX_SNAP_DISTANCE_KM = 0.25;
const GRAPH_PRECISION = 5;

export function findHydrographyRoute(
    start: RiverCoordinate,
    end: RiverCoordinate,
    overpass: GeoJsonFeatureCollection | null,
    ana: GeoJsonFeatureCollection | null,
): HydrographyRoute | null {
    const candidates = [
        createCandidate(start, end, overpass ? [overpass] : [], 'OSM'),
        createCandidate(start, end, ana ? [ana] : [], 'ANA'),
        createCandidate(start, end, [overpass, ana].filter(isFeatureCollection), 'ANA + OSM'),
    ].filter((candidate): candidate is RouteCandidate => candidate !== null);

    if (candidates.length === 0) {
        return null;
    }

    const bestCandidate = candidates.sort((left, right) => left.score - right.score)[0]!;

    return {
        coordinates: bestCandidate.coordinates,
        source: bestCandidate.source,
        distanceKm: bestCandidate.distanceKm,
        startSnapDistanceKm: bestCandidate.startSnapDistanceKm,
        endSnapDistanceKm: bestCandidate.endSnapDistanceKm,
    };
}

export function calculateRouteDistanceKm(coordinates: RiverCoordinate[]) {
    const distance = coordinates.slice(1).reduce((total, coordinate, index) => {
        return total + calculateDistanceKm(coordinates[index]!, coordinate);
    }, 0);

    return Number(distance.toFixed(1));
}

export function insertRouteCoordinate(
    coordinates: RiverCoordinate[],
    coordinate: RiverCoordinate,
) {
    if (coordinates.length < 2) {
        return coordinates;
    }

    let nearestSegmentIndex = 0;
    let nearestDistance = Number.POSITIVE_INFINITY;

    for (let index = 0; index < coordinates.length - 1; index += 1) {
        const projection = projectPointToSegment(coordinate, {
            startKey: '',
            endKey: '',
            start: coordinates[index]!,
            end: coordinates[index + 1]!,
            distanceKm: calculateDistanceKm(coordinates[index]!, coordinates[index + 1]!),
        });

        if (projection.distanceKm < nearestDistance) {
            nearestDistance = projection.distanceKm;
            nearestSegmentIndex = index;
        }
    }

    return [
        ...coordinates.slice(0, nearestSegmentIndex + 1),
        coordinate,
        ...coordinates.slice(nearestSegmentIndex + 1),
    ];
}

function createCandidate(
    start: RiverCoordinate,
    end: RiverCoordinate,
    collections: GeoJsonFeatureCollection[],
    source: HydrographyRouteSource,
): RouteCandidate | null {
    if (collections.length === 0) {
        return null;
    }

    const { graph, segments } = buildGraph(collections);

    if (segments.length === 0) {
        return null;
    }

    const startProjection = findNearestProjection(start, segments);
    const endProjection = findNearestProjection(end, segments);

    if (
        !startProjection
        || !endProjection
        || startProjection.distanceKm > MAX_SNAP_DISTANCE_KM
        || endProjection.distanceKm > MAX_SNAP_DISTANCE_KM
    ) {
        return null;
    }

    const startKey = '__route_start__';
    const endKey = '__route_end__';
    graph.set(startKey, { coordinate: startProjection.coordinate, edges: new Map() });
    graph.set(endKey, { coordinate: endProjection.coordinate, edges: new Map() });
    connectProjection(graph, startKey, startProjection);
    connectProjection(graph, endKey, endProjection);

    if (startProjection.segment === endProjection.segment) {
        connectNodes(
            graph,
            startKey,
            endKey,
            Math.abs(startProjection.ratio - endProjection.ratio) * startProjection.segment.distanceKm,
        );
    }

    const pathKeys = findShortestPath(graph, startKey, endKey);

    if (!pathKeys) {
        return null;
    }

    const waterCoordinates = removeConsecutiveDuplicates(
        pathKeys.map((key) => graph.get(key)!.coordinate),
    );
    // Keep the complete river geometry: removing bends also shortens the measured route.
    const distanceKm = calculateRouteDistanceKm(waterCoordinates);
    const snapPenalty = (startProjection.distanceKm + endProjection.distanceKm) * 4;

    return {
        coordinates: waterCoordinates,
        source,
        distanceKm,
        startSnapDistanceKm: startProjection.distanceKm,
        endSnapDistanceKm: endProjection.distanceKm,
        score: distanceKm + snapPenalty,
    };
}

function buildGraph(collections: GeoJsonFeatureCollection[]) {
    const graph = new Map<string, GraphNode>();
    const segments: GraphSegment[] = [];

    collections.forEach((collection) => {
        collection.features.forEach((feature) => {
            const coordinates = feature.geometry.coordinates
                .filter(isRiverCoordinate)
                .map(([longitude, latitude]) => [longitude, latitude] as RiverCoordinate);

            for (let index = 0; index < coordinates.length - 1; index += 1) {
                const start = coordinates[index]!;
                const end = coordinates[index + 1]!;
                const startKey = coordinateKey(start);
                const endKey = coordinateKey(end);
                const distanceKm = calculateDistanceKm(start, end);

                if (startKey === endKey || distanceKm === 0) {
                    continue;
                }

                ensureNode(graph, startKey, start);
                ensureNode(graph, endKey, end);
                connectNodes(graph, startKey, endKey, distanceKm);
                segments.push({ startKey, endKey, start, end, distanceKm });
            }
        });
    });

    return { graph, segments };
}

function ensureNode(graph: Map<string, GraphNode>, key: string, coordinate: RiverCoordinate) {
    if (!graph.has(key)) {
        graph.set(key, { coordinate, edges: new Map() });
    }
}

function connectNodes(graph: Map<string, GraphNode>, leftKey: string, rightKey: string, distanceKm: number) {
    const left = graph.get(leftKey);
    const right = graph.get(rightKey);

    if (!left || !right) {
        return;
    }

    const currentDistance = left.edges.get(rightKey) ?? Number.POSITIVE_INFINITY;
    const safeDistance = Math.min(currentDistance, distanceKm);
    left.edges.set(rightKey, safeDistance);
    right.edges.set(leftKey, safeDistance);
}

function connectProjection(graph: Map<string, GraphNode>, projectionKey: string, projection: SegmentProjection) {
    connectNodes(
        graph,
        projectionKey,
        projection.segment.startKey,
        projection.ratio * projection.segment.distanceKm,
    );
    connectNodes(
        graph,
        projectionKey,
        projection.segment.endKey,
        (1 - projection.ratio) * projection.segment.distanceKm,
    );
}

function findNearestProjection(point: RiverCoordinate, segments: GraphSegment[]) {
    return segments.reduce<SegmentProjection | null>((nearest, segment) => {
        const projection = projectPointToSegment(point, segment);

        return !nearest || projection.distanceKm < nearest.distanceKm ? projection : nearest;
    }, null);
}

function projectPointToSegment(point: RiverCoordinate, segment: GraphSegment): SegmentProjection {
    const latitudeScale = 111.32;
    const longitudeScale = latitudeScale * Math.cos(toRadians(point[1]));
    const startX = (segment.start[0] - point[0]) * longitudeScale;
    const startY = (segment.start[1] - point[1]) * latitudeScale;
    const endX = (segment.end[0] - point[0]) * longitudeScale;
    const endY = (segment.end[1] - point[1]) * latitudeScale;
    const deltaX = endX - startX;
    const deltaY = endY - startY;
    const squaredLength = deltaX ** 2 + deltaY ** 2;
    const ratio = squaredLength === 0
        ? 0
        : Math.min(1, Math.max(0, -(startX * deltaX + startY * deltaY) / squaredLength));
    const coordinate: RiverCoordinate = [
        segment.start[0] + ((segment.end[0] - segment.start[0]) * ratio),
        segment.start[1] + ((segment.end[1] - segment.start[1]) * ratio),
    ];

    return {
        segment,
        coordinate,
        ratio,
        distanceKm: calculateDistanceKm(point, coordinate),
    };
}

function findShortestPath(graph: Map<string, GraphNode>, startKey: string, endKey: string) {
    const distances = new Map<string, number>([[startKey, 0]]);
    const previous = new Map<string, string>();
    const visited = new Set<string>();
    const pending: Array<{ key: string; distance: number }> = [{ key: startKey, distance: 0 }];

    while (pending.length > 0) {
        const current = popNearest(pending)!;

        if (visited.has(current.key)) {
            continue;
        }

        if (current.key === endKey) {
            break;
        }

        visited.add(current.key);
        graph.get(current.key)?.edges.forEach((edgeDistance, neighborKey) => {
            if (visited.has(neighborKey)) {
                return;
            }

            const nextDistance = current.distance + edgeDistance;

            if (nextDistance < (distances.get(neighborKey) ?? Number.POSITIVE_INFINITY)) {
                distances.set(neighborKey, nextDistance);
                previous.set(neighborKey, current.key);
                insertPending(pending, { key: neighborKey, distance: nextDistance });
            }
        });
    }

    const path = [endKey];
    let currentKey = endKey;

    while (currentKey !== startKey) {
        const previousKey = previous.get(currentKey);

        if (!previousKey) {
            return null;
        }

        path.unshift(previousKey);
        currentKey = previousKey;
    }

    return path;
}

function insertPending(
    pending: Array<{ key: string; distance: number }>,
    item: { key: string; distance: number },
) {
    pending.push(item);
    let index = pending.length - 1;

    while (index > 0) {
        const parentIndex = Math.floor((index - 1) / 2);

        if (pending[parentIndex]!.distance <= pending[index]!.distance) {
            break;
        }

        [pending[parentIndex], pending[index]] = [pending[index]!, pending[parentIndex]!];
        index = parentIndex;
    }
}

function popNearest(pending: Array<{ key: string; distance: number }>) {
    const nearest = pending[0];
    const last = pending.pop();

    if (!nearest || pending.length === 0 || !last) {
        return nearest;
    }

    pending[0] = last;
    let index = 0;

    while (true) {
        const leftIndex = (index * 2) + 1;
        const rightIndex = leftIndex + 1;
        let smallestIndex = index;

        if (pending[leftIndex] && pending[leftIndex]!.distance < pending[smallestIndex]!.distance) {
            smallestIndex = leftIndex;
        }

        if (pending[rightIndex] && pending[rightIndex]!.distance < pending[smallestIndex]!.distance) {
            smallestIndex = rightIndex;
        }

        if (smallestIndex === index) {
            break;
        }

        [pending[index], pending[smallestIndex]] = [pending[smallestIndex]!, pending[index]!];
        index = smallestIndex;
    }

    return nearest;
}

function removeConsecutiveDuplicates(coordinates: RiverCoordinate[]) {
    return coordinates.filter((coordinate, index) => {
        const previous = coordinates[index - 1];
        return !previous || coordinateKey(previous) !== coordinateKey(coordinate);
    });
}

function coordinateKey([longitude, latitude]: RiverCoordinate) {
    return `${longitude.toFixed(GRAPH_PRECISION)},${latitude.toFixed(GRAPH_PRECISION)}`;
}

function calculateDistanceKm(start: RiverCoordinate, end: RiverCoordinate) {
    const earthRadiusKm = 6371;
    const latitudeDelta = toRadians(end[1] - start[1]);
    const longitudeDelta = toRadians(end[0] - start[0]);
    const startLatitude = toRadians(start[1]);
    const endLatitude = toRadians(end[1]);
    const a = Math.sin(latitudeDelta / 2) ** 2
        + Math.cos(startLatitude) * Math.cos(endLatitude) * Math.sin(longitudeDelta / 2) ** 2;

    return 2 * earthRadiusKm * Math.asin(Math.sqrt(Math.min(1, Math.max(0, a))));
}

function toRadians(value: number) {
    return (value * Math.PI) / 180;
}

function isFeatureCollection(
    collection: GeoJsonFeatureCollection | null,
): collection is GeoJsonFeatureCollection {
    return collection !== null;
}

function isRiverCoordinate(coordinate: number[]): coordinate is RiverCoordinate {
    return coordinate.length >= 2
        && Number.isFinite(coordinate[0])
        && Number.isFinite(coordinate[1]);
}
