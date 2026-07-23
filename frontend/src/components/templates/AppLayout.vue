<template>
    <div class="layout-all">
        <div class="layout-shell">
            <SideBar></SideBar>
            <div class="main">
                <RouterView></RouterView>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import SideBar from '@/components/organisms/SideBar.vue';
</script>
<style scoped>
.layout-all {
    display: flex;
    min-height: 100vh;
    background: #010a12;
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