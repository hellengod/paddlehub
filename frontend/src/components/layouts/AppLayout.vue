<template>
    <div class="layout-shell">
        <div class="aside">
            <p>ola</p>
        </div>
        <div class="main">
            <slot></slot>
        </div>
    </div>
</template>
<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>
<style scoped>
.layout-shell {
    display: flex;
    min-height: 100vh
}

.aside {
    width: 300px;
    background-color: aqua;
}

.main {
    background-color: blueviolet;
    flex: 1;
}
</style>