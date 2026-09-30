<template>
    <div class="layout-all">
        <div class="layout-shell">
            <Sidebar></Sidebar>
            <div class="main-shell">
                <AppTopbar></AppTopbar>
                <div class="main">
                    <RouterView></RouterView>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import AppTopbar from '@/components/organisms/AppTopbar.vue';
import Sidebar from '@/components/organisms/SideBar.vue';
</script>
<style scoped>
.layout-all {
    height: 100vh;
    height: 100dvh;
    overflow: hidden;
    background: var(--color-bg-app);
    padding: 10px;
}

.layout-shell {
    display: flex;
    gap: 5px;
    width: 100%;
    height: 100%;
    min-width: 0;
    min-height: 0;
    overflow: hidden;
}

.main-shell {
    display: flex;
    flex: 1;
    flex-direction: column;
    position: relative;
    min-width: 0;
    min-height: 0;
    overflow: hidden;
    border: 1px solid var(--color-border-panel);
    border-radius: var(--radius-sm);
    background: var(--color-bg-panel);
}

.main {
    flex: 1;
    width: 100%;
    height: auto;
    min-width: 0;
    min-height: 0;
    overflow-x: hidden;
    overflow-y: auto;
    overscroll-behavior: contain;
    background: inherit;
}
</style>
