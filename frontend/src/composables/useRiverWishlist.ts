import apiClient from '@/services/apiClient';
import { useFeedback } from '@/composables/useFeedback';
import type { RiverWishlistResponse } from '@/types/rivers';
import { computed, reactive } from 'vue';

interface RiverWishlistState {
    riverIds: number[];
    pendingRiverIds: number[];
    loading: boolean;
}

const wishlistState = reactive<RiverWishlistState>({
    riverIds: [],
    pendingRiverIds: [],
    loading: false,
});

export function useRiverWishlist() {
    const { handleError, handleSuccess } = useFeedback();
    const favoriteIds = computed(() => new Set(wishlistState.riverIds));
    const pendingRiverIds = computed(() => new Set(wishlistState.pendingRiverIds));
    const loading = computed(() => wishlistState.loading);

    async function initializeCsrf() {
        await apiClient.get('/sanctum/csrf-cookie');
    }

    function setFavorite(riverId: number, isFavorite: boolean) {
        const riverIds = wishlistState.riverIds.filter((id) => id !== riverId);
        wishlistState.riverIds = isFavorite ? [...riverIds, riverId] : riverIds;
    }

    function setPending(riverId: number, isPending: boolean) {
        const pendingIds = wishlistState.pendingRiverIds.filter((id) => id !== riverId);
        wishlistState.pendingRiverIds = isPending ? [...pendingIds, riverId] : pendingIds;
    }

    async function fetchWishlist() {
        wishlistState.loading = true;
        try {
            const response = await apiClient.get<RiverWishlistResponse>('api/wishlist/rivers');
            wishlistState.riverIds = response.data.data.rivers.map((river) => river.id);
        } catch (error) {
            handleError(error, 'carregar a lista de desejos');
        } finally {
            wishlistState.loading = false;
        }
    }

    async function toggleWishlist(riverId: number) {
        if (wishlistState.pendingRiverIds.includes(riverId)) {
            return;
        }

        const wasFavorite = wishlistState.riverIds.includes(riverId);
        setFavorite(riverId, !wasFavorite);
        setPending(riverId, true);

        try {
            await initializeCsrf();

            if (wasFavorite) {
                await apiClient.delete(`api/wishlist/rivers/${riverId}`);
                handleSuccess('Rio removido da lista de desejos.');
            } else {
                await apiClient.post(`api/wishlist/rivers/${riverId}`);
                handleSuccess('Adicionado à sua lista de desejos.');
            }
        } catch (error) {
            setFavorite(riverId, wasFavorite);
            handleError(error, 'atualizar a lista de desejos');
        } finally {
            setPending(riverId, false);
        }
    }

    function forgetRiver(riverId: number) {
        setFavorite(riverId, false);
        setPending(riverId, false);
    }

    return {
        favoriteIds,
        pendingRiverIds,
        loading,
        fetchWishlist,
        toggleWishlist,
        forgetRiver,
    };
}
