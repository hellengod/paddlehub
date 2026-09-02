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
                            :is-favorite="favoriteIds.has(river.id)" @toggle-favorite="toggleFavorite" />
                    </div>
                </section>
            </div>
        </div>

        <RiverCreateModal :model-value="isCreateModalOpen" :creating="creating" :error-message="errorMessage"
            :difficulty-options="difficultyOptions" @update:modelValue="handleCreateModalVisibilityChange"
            @submit="handleCreateRiver" />
    </section>
</template>

<script setup lang="ts">
import RiverCard from '@/components/molecules/RiverCard.vue';
import RiverCreateModal from '@/components/organisms/RiverCreateModal.vue';
import RiverFiltersPanel from '@/components/organisms/RiverFiltersPanel.vue';
import { useRivers } from '@/composables/useRivers';
import type { RiverCatalogCard, RiverCatalogFilters, RiverCreateFormValues } from '@/types/rivers';
import { computed, onMounted, reactive, ref } from 'vue';

const difficultyOptions = ['Classe I', 'Classe II', 'Classe III', 'Classe IV', 'Classe V+'];
const ratingOptions = [1, 2, 3, 4, 5];

const { rivers, loading, creating, errorMessage, fetchRivers, createRiver, clearFeedback } = useRivers();
const favoriteIds = ref<Set<number>>(new Set());
const isCreateModalOpen = ref(false);

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

function initializeFavorites(items: RiverCatalogCard[]) {
    if (favoriteIds.value.size > 0 || items.length === 0) {
        return;
    }

    const seedIds = items
        .filter((_, index) => index === 0 || index === 1 || index === items.length - 1)
        .map((river) => river.id);

    favoriteIds.value = new Set(seedIds);
}

function handleApplyFilters(filters: RiverCatalogFilters) {
    activeFilters.search = filters.search;
    activeFilters.region = filters.region;
    activeFilters.difficulty = filters.difficulty;
    activeFilters.maxDistance = filters.maxDistance;
    activeFilters.minRating = filters.minRating;
}

function openCreateModal() {
    clearFeedback();
    isCreateModalOpen.value = true;
}

function handleCreateModalVisibilityChange(isOpen: boolean) {
    isCreateModalOpen.value = isOpen;

    if (!isOpen) {
        clearFeedback();
    }
}

function toggleFavorite(riverId: number) {
    const nextFavorites = new Set(favoriteIds.value);

    if (nextFavorites.has(riverId)) {
        nextFavorites.delete(riverId);
    } else {
        nextFavorites.add(riverId);
    }

    favoriteIds.value = nextFavorites;
}

async function handleCreateRiver(formValues: RiverCreateFormValues) {
    clearFeedback();

    try {
        await createRiver({
            name: formValues.name.trim(),
            city: formValues.city.trim(),
            state: formValues.state.trim(),
            difficulty_class: formValues.difficultyClass.trim() || null,
            description: formValues.description.trim() || null,
            start_latitude: formValues.startLatitude as number,
            start_longitude: formValues.startLongitude as number,
            end_latitude: formValues.endLatitude as number,
            end_longitude: formValues.endLongitude as number,
        });

        initializeFavorites(riverCards.value);
        handleCreateModalVisibilityChange(false);
    } catch {
        // O composable ja define a mensagem de erro.
    }
}

onMounted(() => {
    void fetchRivers().then(() => {
        initializeFavorites(riverCards.value);
    });
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
    border: 1px solid var(--color-border-panel);
    border-radius: var(--radius-sm);
    background: linear-gradient(180deg, rgba(4, 16, 25, 0.98) 0%, rgba(3, 13, 21, 1) 100%);
    padding: 12px;
}

.river-layout {
    display: grid;
    grid-template-columns: 268px minmax(0, 1fr);
    gap: 12px;
    min-height: calc(100vh - 46px);
}

.results-panel {
    background: linear-gradient(180deg, rgba(6, 21, 31, 0.98) 0%, rgba(4, 18, 28, 0.98) 100%);
    padding: 12px 14px 14px;
}

.results-header {
    padding-top: 2px;
    padding-right: 88px;
    margin-bottom: 10px;
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

@media (max-width: 980px) {
    .river-layout {
        grid-template-columns: 1fr;
    }

    .results-header {
        padding-right: 14px;
    }
}

@media (max-width: 720px) {
    .results-panel {
        padding-top: 16px;
    }
}
</style>
