import apiClient from '@/services/apiClient';

export interface LocationSearchResult {
    id: number | string;
    label: string;
    latitude: number;
    longitude: number;
    bounds: {
        west: number;
        south: number;
        east: number;
        north: number;
    } | null;
}

interface LocationSearchResponse {
    data: {
        places: LocationSearchResult[];
    };
}

export async function searchLocations(query: string): Promise<LocationSearchResult[]> {
    const response = await apiClient.get<LocationSearchResponse>('api/locations/search', {
        params: { q: query },
    });

    return response.data.data.places;
}
