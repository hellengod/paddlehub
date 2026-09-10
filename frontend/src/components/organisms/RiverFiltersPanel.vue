<template>
    <aside class="filters-panel">
        <h2 class="visually-hidden">Busca e filtros de rios</h2>

        <div class="primary-actions">
            <label class="search-field" for="river-search">
                <span class="search-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5"></path>
                    </svg>
                </span>
                <input id="river-search" v-model.trim="draftFilters.search" type="search" placeholder="Buscar rio"
                    @keyup.enter="applyFilters">
            </label>

            <BaseButton
                class="create-button"
                min-height="38px"
                padding="0 14px"
                font-size="12px"
                font-weight="700"
                border-width="1px"
                gap="10px"
                background="linear-gradient(180deg, rgba(19, 129, 121, 0.98) 0%, rgba(15, 105, 100, 0.98) 100%)"
                text-color="var(--color-text-primary)"
                border-color="rgba(40, 167, 160, 0.42)"
                @click="emit('open-create')"
            >
                <span class="create-button-icon" aria-hidden="true">+</span>
                <span>Cadastrar rio</span>
            </BaseButton>
        </div>

        <div class="filter-group filter-group--region">
            <label for="region-filter">Regiao</label>
            <div class="select-shell">
                <select id="region-filter" v-model="draftFilters.region">
                    <option value="Todas">Todas</option>
                    <option v-for="option in props.regionOptions" :key="option" :value="option">
                        {{ option }}
                    </option>
                </select>
            </div>
        </div>

        <div class="filter-group filter-group--difficulty">
            <label for="difficulty-filter">Dificuldade</label>
            <div class="select-shell">
                <select id="difficulty-filter" v-model="draftFilters.difficulty">
                    <option value="Todas">Todas</option>
                    <option v-for="option in props.difficultyOptions" :key="option" :value="option">
                        {{ option }}
                    </option>
                </select>
            </div>
        </div>

        <div class="filter-group filter-group--distance">
            <div class="filter-label">Extensao (km)</div>
            <input v-model="draftFilters.maxDistance" class="range-input" type="range" min="0" max="100" step="1">
            <div class="range-labels">
                <span>0 km</span>
                <span>{{ draftFilters.maxDistance }} km</span>
            </div>
        </div>

        <div class="filter-group filter-group--rating">
            <div class="filter-label">Avaliacao minima</div>
            <div class="rating-selector">
                <button v-for="star in props.ratingOptions" :key="star" type="button" class="star-button"
                    :class="{ 'star-button--active': star <= draftFilters.minRating }"
                    :aria-label="`Filtrar por nota minima ${star}`" @click="draftFilters.minRating = star">
                    ★
                </button>
            </div>
        </div>

        <div class="filter-actions">
            <BaseButton
                class="apply-button"
                min-height="38px"
                padding="0 14px"
                font-size="12px"
                font-weight="600"
                border-width="1px"
                background="linear-gradient(180deg, #138179 0%, #0f6964 100%)"
                text-color="var(--color-text-primary)"
                border-color="rgba(40, 167, 160, 0.42)"
                label="Aplicar filtros"
                @click="applyFilters"
            />
            <button type="button" class="clear-button" @click="clearFilters">Limpar filtros</button>
        </div>
    </aside>
</template>

<script setup lang="ts">
import BaseButton from '@/components/atoms/BaseButton.vue';
import type { RiverCatalogFilters } from '@/types/rivers';
import { reactive, watch } from 'vue';

interface RiverFiltersPanelProps {
    appliedFilters: RiverCatalogFilters;
    regionOptions: string[];
    difficultyOptions: string[];
    ratingOptions: number[];
}

const props = defineProps<RiverFiltersPanelProps>();
const emit = defineEmits<{
    (event: 'apply', filters: RiverCatalogFilters): void;
    (event: 'open-create'): void;
}>();

const draftFilters = reactive(createFiltersSnapshot(props.appliedFilters));

watch(
    () => props.appliedFilters,
    (filters) => {
        Object.assign(draftFilters, createFiltersSnapshot(filters));
    },
    { deep: true }
);

function createFiltersSnapshot(filters: RiverCatalogFilters): RiverCatalogFilters {
    return {
        search: filters.search,
        region: filters.region,
        difficulty: filters.difficulty,
        maxDistance: filters.maxDistance,
        minRating: filters.minRating,
    };
}

function createDefaultFilters(): RiverCatalogFilters {
    return {
        search: '',
        region: 'Todas',
        difficulty: 'Todas',
        maxDistance: 100,
        minRating: 0,
    };
}

function applyFilters() {
    emit('apply', createFiltersSnapshot(draftFilters));
}

function clearFilters() {
    Object.assign(draftFilters, createDefaultFilters());
    applyFilters();
}
</script>

<style scoped>
.filters-panel {
    display: grid;
    grid-template-columns:
        minmax(250px, 1.45fr)
        minmax(100px, 0.62fr)
        minmax(110px, 0.7fr)
        minmax(150px, 0.9fr)
        minmax(110px, 0.65fr)
        minmax(190px, 1fr);
    align-items: stretch;
    min-width: 0;
    padding: 8px;
    border: 1px solid rgba(127, 185, 215, 0.12);
    border-radius: 10px;
    background: linear-gradient(180deg, rgba(6, 21, 31, 0.98) 0%, rgba(4, 18, 28, 0.98) 100%);
}

.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.primary-actions {
    display: grid;
    grid-template-columns: minmax(120px, 1fr) minmax(126px, 0.78fr);
    align-items: center;
    gap: 10px;
    min-width: 0;
    padding-right: 12px;
    border-right: 1px solid rgba(255, 255, 255, 0.08);
}

.create-button-icon {
    font-size: 16px;
    line-height: 1;
}

.search-field {
    position: relative;
    display: block;
    min-width: 0;
}

.search-icon {
    position: absolute;
    top: 50%;
    left: 12px;
    width: 14px;
    height: 14px;
    color: rgba(230, 244, 255, 0.48);
    transform: translateY(-50%);
}

.search-icon svg {
    width: 100%;
    height: 100%;
    display: block;
}

.search-field input,
.select-shell select {
    width: 100%;
    min-height: 38px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    background: rgba(8, 21, 31, 0.88);
    color: var(--color-text-primary);
    font: inherit;
}

.search-field input {
    padding: 0 14px 0 34px;
}

.search-field input::placeholder {
    color: rgba(230, 244, 255, 0.42);
}

.clear-button {
    border: none;
    background: transparent;
    color: var(--color-accent-primary);
    font-size: 11px;
    white-space: nowrap;
    cursor: pointer;
}

.filter-group {
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 0;
    margin: 0;
    padding: 0 12px;
    border-right: 1px solid rgba(255, 255, 255, 0.08);
}

.filter-group label,
.filter-label {
    display: block;
    margin-bottom: 5px;
    color: rgba(240, 248, 255, 0.8);
    font-size: 10px;
    font-weight: 600;
    line-height: 1.2;
}

.select-shell {
    position: relative;
}

.select-shell::after {
    content: '';
    position: absolute;
    top: 50%;
    right: 12px;
    width: 8px;
    height: 8px;
    border-right: 1.5px solid rgba(240, 248, 255, 0.6);
    border-bottom: 1.5px solid rgba(240, 248, 255, 0.6);
    transform: translateY(-70%) rotate(45deg);
    pointer-events: none;
}

.select-shell select {
    appearance: none;
    padding: 0 34px 0 12px;
}

.range-input {
    width: 100%;
    accent-color: var(--color-accent-primary);
    cursor: pointer;
}

.range-labels {
    display: flex;
    justify-content: space-between;
    margin-top: 4px;
    color: rgba(230, 244, 255, 0.54);
    font-size: 11px;
}

.rating-selector {
    display: flex;
    gap: 4px;
}

.star-button {
    padding: 0;
    border: none;
    background: transparent;
    color: rgba(71, 121, 135, 0.75);
    font-size: 19px;
    line-height: 1;
    cursor: pointer;
}

.star-button--active {
    color: var(--color-accent-primary);
}

.filter-actions {
    display: grid;
    grid-template-columns: minmax(112px, 1fr) auto;
    align-items: center;
    gap: 12px;
    min-width: 0;
    padding-left: 12px;
}

.create-button:hover,
.apply-button:hover {
    filter: brightness(1.04);
}

@container (max-width: 980px) {
    .filters-panel {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px 0;
        padding: 12px;
    }

    .primary-actions {
        grid-column: span 2;
    }

    .filter-group--region,
    .filter-group--rating {
        border-right: none;
    }

    .filter-actions {
        grid-column: 1 / -1;
        grid-template-columns: minmax(112px, 220px) auto;
        justify-content: end;
        padding-right: 8px;
    }
}

@container (max-width: 680px) {
    .filters-panel {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .primary-actions {
        grid-column: auto;
        grid-template-columns: 1fr;
        padding: 0 0 12px;
        border-right: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .filter-group {
        padding: 0 0 12px;
        border-right: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .filter-actions {
        grid-column: auto;
        grid-template-columns: 1fr;
        justify-content: stretch;
        padding: 0;
    }
}
</style>
