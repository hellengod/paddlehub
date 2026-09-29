import apiClient from '@/services/apiClient';
import { useFeedback } from '@/composables/useFeedback';
import type { RiverPaddlingListResponse } from '@/types/rivers';
import { computed, reactive } from 'vue';

interface RiverPaddlingListState {
    riverIds: number[];
    pendingRiverIds: number[];
    loading: boolean;
}

const paddlingListState = reactive<RiverPaddlingListState>({
    riverIds: [],
    pendingRiverIds: [],
    loading: false,
});

export function useRiverPaddlingList() {
    const { handleError, handleSuccess } = useFeedback();
    const paddlingListRiverIds = computed(() => new Set(paddlingListState.riverIds));
    const pendingRiverIds = computed(() => new Set(paddlingListState.pendingRiverIds));
    const loading = computed(() => paddlingListState.loading);

    async function initializeCsrf() {
        await apiClient.get('/sanctum/csrf-cookie');
    }

    function setInPaddlingList(riverId: number, isInPaddlingList: boolean) {
        const riverIds = paddlingListState.riverIds.filter((id) => id !== riverId);
        paddlingListState.riverIds = isInPaddlingList ? [...riverIds, riverId] : riverIds;
    }

    function setPending(riverId: number, isPending: boolean) {
        const pendingIds = paddlingListState.pendingRiverIds.filter((id) => id !== riverId);
        paddlingListState.pendingRiverIds = isPending ? [...pendingIds, riverId] : pendingIds;
    }

    async function fetchPaddlingList() {
        paddlingListState.loading = true;
        try {
            const response = await apiClient.get<RiverPaddlingListResponse>('api/paddling-list/rivers');
            paddlingListState.riverIds = response.data.data.rivers.map((river) => river.id);
        } catch (error) {
            handleError(error, 'carregar os rios onde quero remar');
        } finally {
            paddlingListState.loading = false;
        }
    }

    async function togglePaddlingList(riverId: number) {
        if (paddlingListState.pendingRiverIds.includes(riverId)) {
            return;
        }

        const wasInPaddlingList = paddlingListState.riverIds.includes(riverId);
        setInPaddlingList(riverId, !wasInPaddlingList);
        setPending(riverId, true);

        try {
            await initializeCsrf();

            if (wasInPaddlingList) {
                await apiClient.delete(`api/paddling-list/rivers/${riverId}`);
                handleSuccess('Rio removido de Rios onde quero remar.');
            } else {
                await apiClient.post(`api/paddling-list/rivers/${riverId}`);
                handleSuccess('Rio adicionado a Rios onde quero remar.');
            }
        } catch (error) {
            setInPaddlingList(riverId, wasInPaddlingList);
            handleError(error, 'atualizar os rios onde quero remar');
        } finally {
            setPending(riverId, false);
        }
    }

    function forgetRiver(riverId: number) {
        setInPaddlingList(riverId, false);
        setPending(riverId, false);
    }

    return {
        paddlingListRiverIds,
        pendingRiverIds,
        loading,
        fetchPaddlingList,
        togglePaddlingList,
        forgetRiver,
    };
}
