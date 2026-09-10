export interface HydrographyBounds {
    west: number;
    south: number;
    east: number;
    north: number;
}

export interface GeoJsonFeatureCollection {
    type: 'FeatureCollection';
    features: GeoJsonLineFeature[];
}

export interface GeoJsonLineFeature {
    type: 'Feature';
    properties: Record<string, unknown>;
    geometry: {
        type: 'LineString';
        coordinates: number[][];
    };
}

interface OverpassElement {
    id: number;
    tags?: Record<string, string>;
    geometry?: Array<{ lat: number; lon: number }>;
}

interface OverpassResponse {
    elements: OverpassElement[];
}

const OVERPASS_URL = 'https://overpass-api.de/api/interpreter';

export async function fetchOverpassWaterways(bounds: HydrographyBounds): Promise<GeoJsonFeatureCollection> {
    const query = `[out:json][timeout:25];\nway["waterway"~"^(river|stream|canal|ditch|drain)$"](${bounds.south},${bounds.west},${bounds.north},${bounds.east});\nout geom;`;

    const response = await fetch(OVERPASS_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body: new URLSearchParams({ data: query }),
    });

    if (!response.ok) {
        throw new Error(`Overpass respondeu HTTP ${response.status}.`);
    }

    if (!response.headers.get('content-type')?.includes('application/json')) {
        throw new Error('Overpass respondeu um formato inesperado, sem GeoJSON/JSON.');
    }

    const payload = (await response.json()) as OverpassResponse;

    return {
        type: 'FeatureCollection',
        features: (payload.elements ?? []).flatMap((element) => {
            if (!element.geometry || element.geometry.length < 2) {
                return [];
            }

            return [{
                type: 'Feature' as const,
                properties: { id: element.id, ...element.tags },
                geometry: {
                    type: 'LineString' as const,
                    coordinates: element.geometry.map((point) => [point.lon, point.lat]),
                },
            }];
        }),
    };
}

export async function fetchAnaHydrography(bounds: HydrographyBounds): Promise<GeoJsonFeatureCollection> {
    const response = await apiClient.get<{ data: GeoJsonFeatureCollection }>('api/hydrography/ana', {
        params: bounds,
    });

    return response.data.data;
}
import apiClient from '@/services/apiClient';
