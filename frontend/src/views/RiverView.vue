<template>
    <section class="river-view">
        <div class="river-shell">
            <div class="river-layout">
                <RiverFiltersPanel :applied-filters="activeFilters" :region-options="regionOptions"
                    :difficulty-options="difficultyOptions" :rating-options="ratingOptions" @apply="handleApplyFilters"
                    @open-create="openCreateModal" />

                <section class="results-panel">
                    <header class="results-header">
                        <div>
                            <h1>Rios</h1>
                            <p>Explorar todos os rios cadastrados na plataforma.</p>
                        </div>
                    </header>

                    <div v-if="loading" class="results-state">
                        Carregando rios...
                    </div>

                    <div v-else-if="errorMessage && rivers.length === 0" class="results-state results-state--error">
                        {{ errorMessage }}
                    </div>

                    <div v-else-if="filteredRivers.length === 0" class="results-state">
                        Nenhum rio corresponde aos filtros aplicados.
                    </div>

                    <div v-else class="river-list">
                        <RiverCard v-for="river in filteredRivers" :key="river.id" :river="river"
                            :is-favorite="favoriteIds.has(river.id)" :cover-src="getRiverCoverSrc(river.id)"
                            :favorite-loading="wishlistLoading || pendingRiverIds.has(river.id)"
                            @toggle-favorite="toggleFavorite" @edit="openEditModal" @delete="openDeleteModal" />
                    </div>
                </section>
            </div>
        </div>

        <RiverCreateModal :model-value="isCreateModalOpen" :creating="creating" :error-message="errorMessage"
            :difficulty-options="difficultyOptions" :river="editingRiver"
            @update:modelValue="handleCreateModalVisibilityChange" @submit="handleSaveRiver" />

    </section>
</template>

<script setup lang="ts">
import RiverCard from '@/components/molecules/RiverCard.vue';
import RiverCreateModal from '@/components/organisms/RiverCreateModal.vue';
import RiverFiltersPanel from '@/components/organisms/RiverFiltersPanel.vue';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { useRiverWishlist } from '@/composables/useRiverWishlist';
import { useRivers } from '@/composables/useRivers';
import type { River, RiverCatalogCard, RiverCatalogFilters, RiverCreateFormValues, RiverPayload } from '@/types/rivers';
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';

const difficultyOptions = ['Classe I', 'Classe II', 'Classe III', 'Classe IV', 'Classe V', 'Classe V+'];
const ratingOptions = [1, 2, 3, 4, 5];

const { rivers, loading, creating, errorMessage, fetchRivers, createRiver, updateRiver, deleteRiver, clearFeedback } = useRivers();
const {
    favoriteIds,
    pendingRiverIds,
    loading: wishlistLoading,
    fetchWishlist,
    toggleWishlist,
    forgetRiver: forgetWishlistedRiver,
} = useRiverWishlist();
const { confirmDanger } = useConfirmDialog();
const isCreateModalOpen = ref(false);
const editingRiver = ref<River | null>(null);
const localRiverCoverUrls = ref<Map<number, string>>(new Map());
let createModalSession = 0;

const activeFilters = reactive<RiverCatalogFilters>({
    search: '',
    region: 'Todas',
    difficulty: 'Todas',
    maxDistance: 100,
    minRating: 0,
});

const riverCards = computed<RiverCatalogCard[]>(() =>
    rivers.value.map((river, index) => {
        const displayDifficultyClass =
            river.difficultyClass ?? difficultyOptions[index % difficultyOptions.length] ?? 'Classe II';
        const rating = 4.4 + (((river.id + index) % 6) / 10);
        const reviewCount = 64 + ((river.id * 29) % 112);

        return {
            ...river,
            displayDifficultyClass,
            rating,
            reviewCount,
            regionLabel: river.state,
        };
    })
);

const regionOptions = computed(() => [...new Set(riverCards.value.map((river) => river.regionLabel))].sort());

const filteredRivers = computed(() =>
    riverCards.value.filter((river) => {
        const matchesSearch =
            activeFilters.search === '' ||
            `${river.name} ${river.city} ${river.state}`.toLowerCase().includes(activeFilters.search.toLowerCase());

        const matchesRegion =
            activeFilters.region === 'Todas' || river.regionLabel === activeFilters.region;

        const matchesDifficulty =
            activeFilters.difficulty === 'Todas' || river.displayDifficultyClass === activeFilters.difficulty;

        const matchesDistance = river.extensionKm <= activeFilters.maxDistance;
        const matchesRating = river.rating >= activeFilters.minRating;

        return matchesSearch && matchesRegion && matchesDifficulty && matchesDistance && matchesRating;
    })
);

function handleApplyFilters(filters: RiverCatalogFilters) {
    activeFilters.search = filters.search;
    activeFilters.region = filters.region;
    activeFilters.difficulty = filters.difficulty;
    activeFilters.maxDistance = filters.maxDistance;
    activeFilters.minRating = filters.minRating;
}

function openCreateModal() {
    clearFeedback();
    editingRiver.value = null;
    createModalSession += 1;
    isCreateModalOpen.value = true;
}

function openEditModal(river: River) {
    clearFeedback();
    editingRiver.value = river;
    createModalSession += 1;
    isCreateModalOpen.value = true;
}

function handleCreateModalVisibilityChange(isOpen: boolean) {
    isCreateModalOpen.value = isOpen;

    if (!isOpen) {
        clearFeedback();
        editingRiver.value = null;
    }
}

async function openDeleteModal(river: River) {
    const confirmed = await confirmDanger({
        title: 'Excluir rio',
        message: `O rio ${river.name} e o trecho cadastrado serão removidos definitivamente.`,
        confirmLabel: 'Excluir',
    });

    if (confirmed) {
        await removeRiver(river);
    }
}

function toggleFavorite(riverId: number) {
    void toggleWishlist(riverId);
}

function getRiverCoverSrc(riverId: number) {
    return localRiverCoverUrls.value.get(riverId);
}

function setLocalRiverCover(riverId: number, coverImage: File | null) {
    if (!coverImage) {
        return;
    }

    const previousUrl = localRiverCoverUrls.value.get(riverId);
    if (previousUrl) {
        URL.revokeObjectURL(previousUrl);
    }

    const nextCoverUrls = new Map(localRiverCoverUrls.value);
    nextCoverUrls.set(riverId, URL.createObjectURL(coverImage));
    localRiverCoverUrls.value = nextCoverUrls;
}

function removeLocalRiverCover(riverId: number) {
    const coverUrl = localRiverCoverUrls.value.get(riverId);
    if (coverUrl) {
        URL.revokeObjectURL(coverUrl);
    }

    const nextCoverUrls = new Map(localRiverCoverUrls.value);
    nextCoverUrls.delete(riverId);
    localRiverCoverUrls.value = nextCoverUrls;
}

function riverPayloadFromForm(formValues: RiverCreateFormValues): RiverPayload {
    return {
        name: formValues.name.trim(),
        city: formValues.city.trim(),
        state: formValues.state.trim(),
        difficulty_class: formValues.difficultyClass.trim() || null,
        description: formValues.description.trim() || null,
        extension_km: Number(formValues.extensionKm),
        start_latitude: formValues.startLatitude as number,
        start_longitude: formValues.startLongitude as number,
        end_latitude: formValues.endLatitude as number,
        end_longitude: formValues.endLongitude as number,
        route_coordinates: formValues.routeCoordinates,
    };
}

async function handleSaveRiver(formValues: RiverCreateFormValues) {
    if (creating.value) {
        return;
    }

    const submissionSession = createModalSession;
    const riverBeingEdited = editingRiver.value;
    clearFeedback();

    try {
        const savedRiver = riverBeingEdited
            ? await updateRiver(riverBeingEdited.id, riverPayloadFromForm(formValues))
            : await createRiver(riverPayloadFromForm(formValues));

        setLocalRiverCover(savedRiver.id, formValues.coverImage);
        if (isCreateModalOpen.value && submissionSession === createModalSession) {
            handleCreateModalVisibilityChange(false);
        }
    } catch {
        // O composable ja define a mensagem de erro.
    }
}

async function removeRiver(river: River) {
    if (creating.value) {
        return;
    }

    clearFeedback();

    try {
        await deleteRiver(river.id);
        removeLocalRiverCover(river.id);
        forgetWishlistedRiver(river.id);
    } catch {
        // O composable de negócio apresenta o erro uma única vez.
    }
}

onMounted(() => {
    void Promise.all([fetchRivers(), fetchWishlist()]);
});

onBeforeUnmount(() => {
    localRiverCoverUrls.value.forEach((coverUrl) => URL.revokeObjectURL(coverUrl));
});
</script>

<style scoped>
.river-view {
    width: 100%;
    min-height: calc(100vh - 20px);
    padding-left: 6px;
}

.river-shell {
    min-height: calc(100vh - 20px);
    padding: 74px 12px 12px;
    border: 1px solid var(--color-border-panel);
    border-radius: var(--radius-sm);
    background: linear-gradient(180deg, rgba(4, 16, 25, 0.98) 0%, rgba(3, 13, 21, 1) 100%);
    container-type: inline-size;
}

.river-layout {
    display: flex;
    flex-direction: column;
    gap: 14px;
    min-height: calc(100vh - 108px);
}

.results-panel {
    min-width: 0;
}

.results-header {
    margin-bottom: 12px;
}

.results-header h1 {
    color: var(--color-text-primary);
    font-size: 15px;
    font-weight: 600;
}

.results-header p {
    margin-top: 2px;
    color: rgba(230, 244, 255, 0.62);
    font-size: 12px;
}

.results-state {
    min-height: 260px;
    display: grid;
    place-items: center;
    color: rgba(230, 244, 255, 0.68);
    text-align: center;
    font-size: 14px;
}

.results-state--error {
    color: #ffd4d4;
}

.river-list {
    display: grid;
    gap: 8px;
}

@media (max-width: 720px) {
    .river-shell {
        padding-top: 80px;
    }

    .results-panel {
        padding: 0;
    }
}
</style>
