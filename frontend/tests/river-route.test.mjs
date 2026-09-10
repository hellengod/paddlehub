import assert from 'node:assert/strict';
import test from 'node:test';
import { calculateRouteDistanceKm, findHydrographyRoute } from '../src/services/riverRouteService.ts';

test('distance sums segments before rounding, including short segments', () => {
    const coordinates = Array.from({ length: 1001 }, (_, index) => [index / 1000, 0]);
    assert.equal(calculateRouteDistanceKm(coordinates), 111.2);
    assert.equal(calculateRouteDistanceKm([...coordinates].reverse()), 111.2);
    assert.equal(calculateRouteDistanceKm([]), 0);
});

test('OSM route retains bends: a 7 km winding line must not collapse to its 4.3 km chord', () => {
    // Synthetic geometry near the equator, with 600 short alternating segments.
    // Segment lengths are 7/600 km; their combined eastward span is 4.3 km.
    const kmPerDegree = 111.19492664455873;
    const segments = 600;
    const dx = 4.3 / segments;
    const dy = Math.sqrt((7 / segments) ** 2 - dx ** 2);
    const coordinates = Array.from({ length: segments + 1 }, (_, index) => [
        index * dx / kmPerDegree,
        (index % 2) * dy / kmPerDegree,
    ]);
    const osm = {
        type: 'FeatureCollection',
        features: [{ type: 'Feature', properties: {}, geometry: { type: 'LineString', coordinates } }],
    };

    assert.equal(calculateRouteDistanceKm(coordinates), 7);
    assert.equal(calculateRouteDistanceKm([coordinates[0], coordinates.at(-1)]), 4.3);

    const route = findHydrographyRoute(coordinates[0], coordinates.at(-1), osm, null);
    assert.ok(route);
    assert.equal(route.distanceKm, 7);
    assert.deepEqual(route.coordinates, coordinates);
});
