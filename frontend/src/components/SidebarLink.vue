<template>
    <router-link :to="{ name: props.to }" class="sidebar-link">
        <div class="icon">
            <component :is="props.icon" />
        </div>
        <div class="text">
            {{ props.name }}
        </div>
    </router-link>

</template>
<script setup lang="ts">
import type { Component } from 'vue'

interface SidebarLink {
    icon: Component,
    to: string,
    name: string
}

const props = defineProps<SidebarLink>();

</script>
<style scoped>
.sidebar-link {
    display: flex;
    align-items: center;
    gap: 20px;
    width: 100%;
    padding: 20px 14px;
    border-radius: 10px;
    border: 1px solid transparent;
    background: transparent;
    color: aliceblue;
    text-decoration: none;
    transition:
        color 0.2s ease,
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.sidebar-link:hover,
.sidebar-link.router-link-active {
    color: #3be4db;
    background: rgba(27, 179, 153, 0.096);
    border: 1px solid rgba(255, 255, 255, 0.123);
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