import apiClient from '@/services/apiClient';
import { normalizeErrorMessage, useFeedback } from '@/composables/useFeedback';
import type {
    River,
    RiverCreateResponse,
    RiverListResponse,
    RiverPayload,
} from '@/types/rivers';
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
    const { handleError, handleSuccess } = useFeedback();
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

    async function fetchRivers() {
        riversState.loading = true;
        riversState.errorMessage = '';

        try {
            const response = await apiClient.get<RiverListResponse>('api/rivers');
            riversState.items = response.data.data.rivers;
        } catch (error) {
            riversState.errorMessage = normalizeErrorMessage(error, 'carregar os rios');
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
            handleSuccess('Rio cadastrado.');

            return response.data.data.river;
        } catch (error) {
            handleError(error, 'cadastrar o rio');
            throw error;
        } finally {
            riversState.creating = false;
        }
    }

    async function updateRiver(riverId: number, payload: RiverPayload) {
        riversState.creating = true;
        riversState.errorMessage = '';

        try {
            await initializeCsrf();

            const response = await apiClient.put<RiverCreateResponse>(`api/rivers/${riverId}`, payload);
            riversState.items = riversState.items.map((river) =>
                river.id === riverId ? response.data.data.river : river
            );
            handleSuccess('Rio atualizado.');

            return response.data.data.river;
        } catch (error) {
            handleError(error, 'atualizar o rio');
            throw error;
        } finally {
            riversState.creating = false;
        }
    }

    async function deleteRiver(riverId: number) {
        riversState.creating = true;
        riversState.errorMessage = '';

        try {
            await initializeCsrf();
            await apiClient.delete(`api/rivers/${riverId}`);
            riversState.items = riversState.items.filter((river) => river.id !== riverId);
            handleSuccess('Rio excluído.');
        } catch (error) {
            handleError(error, 'excluir o rio');
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
        updateRiver,
        deleteRiver,
        clearFeedback,
    };
}
