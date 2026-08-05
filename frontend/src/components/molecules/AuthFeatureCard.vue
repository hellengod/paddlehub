<template>
    <div class="container">
        <div class="icon">
            <img :src=props.icon>
        </div>
        <div class="text-content">
            <p>{{ props.title }}</p>
        </div>
    </div>
</template>
<script setup lang="ts">
interface AuthFeatureCardProps {
    title: string;
    icon: string;
}

const props = defineProps<AuthFeatureCardProps>();
</script>
<style scoped>
.container {
    background: var(--color-accent-soft);
    backdrop-filter: blur(1px);
    border-radius: var(--radius-sm);
    width: 200px;
    height: 70px;
    border: 1px solid var(--color-border-subtle);
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 5px;

}

.icon {
    width: 40px;
    height: 40px;
    margin-left: 10px;
    border-radius: var(--radius-sm);
}

img {
    width: 40px;
    height: 40px;
}

.text-content {
    color: var(--color-text-primary);
}
</style>