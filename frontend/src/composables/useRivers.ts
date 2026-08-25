import apiClient from '@/services/apiClient';
import type {
    River,
    RiverCreateResponse,
    RiverListResponse,
    RiverPayload,
} from '@/types/rivers';
import axios from 'axios';
import { computed, reactive } from 'vue';

interface RiversState {
    items: River[];
    loading: boolean;
    creating: boolean;
    errorMessage: string;
}

const riversState = reactive<RiversState>({
    items: [],
    loading: false,
    creating: false,
    errorMessage: '',
});

export function useRivers() {
    const rivers = computed(() => riversState.items);
    const loading = computed(() => riversState.loading);
    const creating = computed(() => riversState.creating);
    const errorMessage = computed(() => riversState.errorMessage);

    async function initializeCsrf() {
        await apiClient.get('/sanctum/csrf-cookie');
    }

    function clearFeedback() {
        riversState.errorMessage = '';
    }

    function getErrorMessage(error: unknown, fallbackMessage: string) {
        if (axios.isAxiosError(error)) {
            const apiMessage = error.response?.data?.message;
            const validationErrors = error.response?.data?.errors;

            if (validationErrors && typeof validationErrors === 'object') {
                const firstFieldErrors = Object.values(validationErrors)[0];

                if (Array.isArray(firstFieldErrors) && typeof firstFieldErrors[0] === 'string') {
                    return firstFieldErrors[0];
                }
            }

            if (typeof apiMessage === 'string' && apiMessage.trim() !== '') {
                return apiMessage;
            }
        }

        return fallbackMessage;
    }

    async function fetchRivers() {
        riversState.loading = true;
        riversState.errorMessage = '';

        try {
            const response = await apiClient.get<RiverListResponse>('api/rivers');
            riversState.items = response.data.data.rivers;
        } catch (error) {
            riversState.errorMessage = getErrorMessage(error, 'Nao foi possivel carregar os rios.');
        } finally {
            riversState.loading = false;
        }
    }

    async function createRiver(payload: RiverPayload) {
        riversState.creating = true;
        riversState.errorMessage = '';

        try {
            await initializeCsrf();

            const response = await apiClient.post<RiverCreateResponse>('api/rivers', payload);
            riversState.items = [response.data.data.river, ...riversState.items];

            return response.data.data.river;
        } catch (error) {
            riversState.errorMessage = getErrorMessage(error, 'Nao foi possivel cadastrar o rio.');
            throw error;
        } finally {
            riversState.creating = false;
        }
    }

    return {
        rivers,
        loading,
        creating,
        errorMessage,
        fetchRivers,
        createRiver,
        clearFeedback,
    };
}
