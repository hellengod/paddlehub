<template>
    <div class="layout-shell">
        <SideBar></SideBar>
        <div class="main">
            <slot></slot>
        </div>
    </div>

</template>
<script setup lang="ts">
import SideBar from '@/components/layouts/SideBar.vue';

</script>
<style scoped>
.layout-shell {
    display: flex;
    min-height: 100vh;
    background: rgba(33, 86, 109, 0.096);
}

.main {
    /* background-color: blueviolet; */
    flex: 1;
}
</style>