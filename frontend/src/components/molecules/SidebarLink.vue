<template>
    <router-link :to="{ name: props.to }" class="sidebar-link">
        <div class="icon">
            <component :is="props.icon" />
        </div>
        <div class="text">
            {{ props.label }}
        </div>
    </router-link>

</template>
<script setup lang="ts">
import type { Component } from 'vue'

interface SidebarLink {
    icon: Component,
    to: string,
    label: string
}

const props = defineProps<SidebarLink>();

</script>
<style scoped>
.sidebar-link {
    display: flex;
    align-items: center;
    gap: var(--space-5);
    width: 100%;
    padding: var(--space-5) var(--space-3);
    border-radius: var(--radius-sm);
    border: 1px solid transparent;
    background: transparent;
    color: var(--color-text-primary);
    text-decoration: none;
    transition:
        color 0.2s ease,
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.sidebar-link:hover,
.sidebar-link.router-link-active {
    color: var(--color-accent-strong);
    background: var(--color-accent-soft);
    border: 1px solid var(--color-border-subtle);
}

.icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.icon :deep(svg) {
    width: 100%;
    height: 100%;
    display: block;
}

.text {
    font-size: 14px;
    line-height: 1;
}
</style>