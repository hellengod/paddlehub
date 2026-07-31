<template>
    <nav class="sidebar-nav">
        <ul>
            <li v-for="item in props.items" :key="item.to">
                <SidebarLink :icon="item.icon" :to="item.to" :label="item.label"></SidebarLink>
            </li>
        </ul>
    </nav>
</template>

<script lang="ts" setup>
import type { MenuItem } from '@/types/menuItems.ts';
import SidebarLink from '../molecules/SidebarLink.vue';
interface SidebarNavigationProps {
    items: MenuItem[]
}

const props = defineProps<SidebarNavigationProps>();
</script>

<style scoped>
.sidebar-nav {
    padding-inline: 15px;
}

.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
</style>