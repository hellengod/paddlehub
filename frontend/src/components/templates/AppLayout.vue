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
import Sidebar from '@/components/organisms/Sidebar.vue';
</script>
<style scoped>
.layout-all {
    min-height: 100vh;
    background: var(--color-bg-app);
    padding: 10px;
}

.layout-shell {
    display: flex;
    gap: 5px;
    width: 100%;
    min-width: 0;
}

.main-shell {
    flex: 1;
    min-width: 0;
    position: relative;
    min-height: calc(100vh - 20px);
}

.main {
    min-height: calc(100vh - 20px);
    min-width: 0;
}
</style>
