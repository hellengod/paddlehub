<template>
    <aside class="filters-panel">
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
            min-height="42px"
            padding="0 14px"
            font-size="13px"
            font-weight="700"
            border-width="1px"
            gap="12px"
            background="linear-gradient(180deg, rgba(19, 129, 121, 0.98) 0%, rgba(15, 105, 100, 0.98) 100%)"
            text-color="var(--color-text-primary)"
            border-color="rgba(40, 167, 160, 0.42)"
            @click="emit('open-create')"
        >
            <span class="create-button-icon" aria-hidden="true">+</span>
            <span>Cadastrar rio</span>
        </BaseButton>

        <div class="filters-header">
            <h2>Filtros</h2>
            <button type="button" class="clear-button" @click="clearFilters">Limpar filtros</button>
        </div>

        <div class="filter-group">
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

        <div class="filter-group">
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

        <div class="filter-group">
            <div class="filter-label">Extensao (km)</div>
            <input v-model="draftFilters.maxDistance" class="range-input" type="range" min="0" max="100" step="1">
            <div class="range-labels">
                <span>0 km</span>
                <span>{{ draftFilters.maxDistance }} km</span>
            </div>
        </div>

        <div class="filter-group">
            <div class="filter-label">Avaliacao minima</div>
            <div class="rating-selector">
                <button v-for="star in props.ratingOptions" :key="star" type="button" class="star-button"
                    :class="{ 'star-button--active': star <= draftFilters.minRating }"
                    :aria-label="`Filtrar por nota minima ${star}`" @click="draftFilters.minRating = star">
                    ★
                </button>
            </div>
        </div>

        <BaseButton
            class="apply-button"
            min-height="42px"
            padding="0 14px"
            font-size="13px"
            font-weight="600"
            border-width="1px"
            background="linear-gradient(180deg, #138179 0%, #0f6964 100%)"
            text-color="var(--color-text-primary)"
            border-color="rgba(40, 167, 160, 0.42)"
            label="Aplicar filtros"
            @click="applyFilters"
        />
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
    display: flex;
    flex-direction: column;
    padding: 16px;
    border: 1px solid rgba(127, 185, 215, 0.12);
    border-radius: 14px;
    background: linear-gradient(180deg, rgba(6, 21, 31, 0.98) 0%, rgba(4, 18, 28, 0.98) 100%);
}

.create-button {
    margin-top: 16px;
}

.create-button-icon {
    font-size: 16px;
    line-height: 1;
}

.search-field {
    position: relative;
    display: block;
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
    min-height: 40px;
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

.filters-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: 24px;
    margin-bottom: 18px;
}

.filters-header h2 {
    color: var(--color-text-primary);
    font-size: 15px;
    font-weight: 600;
}

.clear-button {
    border: none;
    background: transparent;
    color: var(--color-accent-primary);
    font-size: 11px;
    cursor: pointer;
}

.filter-group {
    margin-bottom: 22px;
}

.filter-group label,
.filter-label {
    display: block;
    margin-bottom: 10px;
    color: rgba(240, 248, 255, 0.8);
    font-size: 11px;
    font-weight: 600;
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
    margin-top: 10px;
    color: rgba(230, 244, 255, 0.54);
    font-size: 11px;
}

.rating-selector {
    display: flex;
    gap: 5px;
    margin-top: 2px;
}

.star-button {
    padding: 0;
    border: none;
    background: transparent;
    color: rgba(71, 121, 135, 0.75);
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
}

.star-button--active {
    color: var(--color-accent-primary);
}

.apply-button {
    margin-top: 10px;
}

.create-button:hover,
.apply-button:hover {
    filter: brightness(1.04);
}
</style>
