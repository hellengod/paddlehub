<template>
    <div class="layout-all">
        <div class="layout-shell">
            <Sidebar></Sidebar>
            <div class="main">
                <RouterView></RouterView>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import Sidebar from '@/components/organisms/Sidebar.vue';
</script>
<style scoped>
.layout-all {
    display: flex;
    min-height: 100vh;
    background: var(--color-bg-app);
    padding: 10px;

    .layout-shell {
        display: flex;
        gap: 5px;
    }
}


.main {
    /* background-color: blueviolet; */
    flex: 1;
}
</style>