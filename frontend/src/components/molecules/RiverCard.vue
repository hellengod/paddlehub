<template>
    <article class="river-card">
        <img :src="coverSrc" :alt="coverAlt" class="river-card-image" :aria-hidden="!props.coverSrc">

        <div class="river-card-content">
            <div class="river-card-header">
                <div class="river-card-title">
                    <h3>{{ props.river.name }}</h3>
                    <p>{{ props.river.city }}, {{ props.river.state }}</p>
                </div>

                <div class="river-card-actions">
                    <BaseButton v-if="props.river.canManage" type="button" class="manage-button" width="auto"
                        min-height="28px" padding="0 9px" font-size="11px" font-weight="500" border-width="1px"
                        border-radius="7px" background-color="transparent" text-color="var(--color-accent-strong)"
                        border-color="var(--color-border-subtle)" label="Editar"
                        @click="emit('edit', props.river)" />
                    <BaseButton v-if="props.river.canManage" type="button" class="manage-button manage-button--danger"
                        width="auto" min-height="28px" padding="0 9px" font-size="11px" font-weight="500"
                        border-width="1px" border-radius="7px" background-color="transparent" text-color="#ffb5b5"
                        border-color="rgba(255, 115, 115, 0.24)" label="Excluir"
                        @click="emit('delete', props.river)" />
                    <button type="button" class="favorite-button" :class="{ 'favorite-button--active': props.isFavorite }"
                        :aria-label="favoriteButtonLabel" @click="emit('toggle-favorite', props.river.id)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="m12 21-1.45-1.32C5.4 15.03 2 11.94 2 8.15 2 5.06 4.42 3 7.3 3c1.74 0 3.41.81 4.7 2.09C13.29 3.81 14.96 3 16.7 3 19.58 3 22 5.06 22 8.15c0 3.79-3.4 6.88-8.55 11.54z">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="river-card-badges">
                <span class="card-pill">{{ props.river.displayDifficultyClass }}</span>
                <span class="card-pill">{{ formatExtensionKm(props.river.extensionKm) }} km</span>
            </div>

            <div class="river-card-meta">
                <span class="rating-pill">
                    <span class="rating-star">★</span>
                    {{ props.river.rating.toFixed(1) }}
                </span>
                <span class="meta-separator">•</span>
                <span>{{ props.river.reviewCount }} avaliacoes</span>
            </div>
        </div>
    </article>
</template>

<script setup lang="ts">
import BaseButton from '@/components/atoms/BaseButton.vue';
import type { RiverCatalogCard } from '@/types/rivers';
import { computed } from 'vue';

interface RiverCardProps {
    river: RiverCatalogCard;
    isFavorite: boolean;
    coverSrc?: string;
}

const props = defineProps<RiverCardProps>();
const emit = defineEmits<{
    (event: 'toggle-favorite', riverId: number): void;
    (event: 'edit', river: RiverCatalogCard): void;
    (event: 'delete', river: RiverCatalogCard): void;
}>();

const favoriteButtonLabel = computed(() =>
    props.isFavorite ? 'Remover dos favoritos' : 'Adicionar aos favoritos'
);
const coverSrc = computed(() => props.coverSrc || '/imagem-fundo5.png');
const coverAlt = computed(() => props.coverSrc ? `Mapa do trecho ${props.river.name}` : '');

function formatExtensionKm(distance: number) {
    return distance.toFixed(1);
}
</script>

<style scoped>
.river-card {
    display: grid;
    grid-template-columns: 120px minmax(0, 1fr);
    gap: 12px;
    min-height: 86px;
    padding: 8px 10px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    background: rgba(5, 18, 28, 0.94);
}

.river-card-image {
    width: 100%;
    height: 68px;
    object-fit: cover;
    border-radius: 8px;
}

.river-card-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
    min-width: 0;
}

.river-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}

.river-card-title h3 {
    color: var(--color-text-primary);
    font-size: 14px;
    font-weight: 600;
    line-height: 1.1;
}

.river-card-title p {
    margin-top: 4px;
    color: rgba(230, 244, 255, 0.68);
    font-size: 12px;
}

.river-card-actions {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    gap: 6px;
}

.manage-button:hover {
    border-color: var(--color-accent-primary);
}

.manage-button--danger:hover {
    border-color: rgba(255, 115, 115, 0.52);
    background: rgba(113, 24, 24, 0.22);
}

.favorite-button {
    width: 18px;
    height: 18px;
    padding: 0;
    border: none;
    background: transparent;
    color: rgba(240, 248, 255, 0.7);
    cursor: pointer;
    flex-shrink: 0;
}

.favorite-button svg {
    width: 100%;
    height: 100%;
    display: block;
}

.favorite-button--active {
    color: var(--color-accent-primary);
    fill: currentColor;
}

.river-card-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.card-pill {
    display: inline-flex;
    align-items: center;
    min-height: 18px;
    padding: 0 8px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.05);
    color: rgba(230, 244, 255, 0.64);
    font-size: 11px;
}

.river-card-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    color: rgba(230, 244, 255, 0.64);
    font-size: 12px;
}

.rating-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: rgba(255, 234, 171, 0.92);
}

.rating-star {
    color: #f4c55c;
}

.meta-separator {
    color: rgba(230, 244, 255, 0.34);
}

@media (max-width: 720px) {
    .river-card {
        grid-template-columns: 1fr;
    }

    .river-card-image {
        height: 160px;
    }
}
</style>
