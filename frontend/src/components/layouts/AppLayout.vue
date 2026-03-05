<template>
    <div class="header">
        <h1>PaddleHub</h1>
        <nav>
            <RouterLink to="/">Home</RouterLink>
            <RouterLink to="/rivers">Rios</RouterLink>
        </nav>
    </div>
    <div class="body">
        <slot></slot>
    </div>
</template>
<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>
<style></style>