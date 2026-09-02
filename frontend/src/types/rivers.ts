import type { ApiResponse } from '@/types/auth';

export interface RiverAuthor {
    id: number | null;
    name: string | null;
}

export interface River {
    id: number;
    name: string;
    city: string;
    state: string;
    difficultyClass: string | null;
    description: string | null;
    startLatitude: number;
    startLongitude: number;
    endLatitude: number | null;
    endLongitude: number | null;
    extensionKm: number;
    createdBy: RiverAuthor;
    createdAt: string | null;
}

export interface RiverCatalogCard extends River {
    displayDifficultyClass: string;
    rating: number;
    reviewCount: number;
    regionLabel: string;
}

export interface RiverCatalogFilters {
    search: string;
    region: string;
    difficulty: string;
    maxDistance: number;
    minRating: number;
}

export interface RiverCreateFormValues {
    name: string;
    city: string;
    state: string;
    difficultyClass: string;
    description: string;
    startLatitude: number | null;
    startLongitude: number | null;
    endLatitude: number | null;
    endLongitude: number | null;
}

export interface RiverListData {
    rivers: River[];
}

export interface RiverRecordData {
    river: River;
}

export type RiverListResponse = ApiResponse<RiverListData>;
export type RiverCreateResponse = ApiResponse<RiverRecordData>;

export interface RiverPayload {
    name: string;
    city: string;
    state: string;
    difficulty_class: string | null;
    description: string | null;
    start_latitude: number;
    start_longitude: number;
    end_latitude: number;
    end_longitude: number;
}
