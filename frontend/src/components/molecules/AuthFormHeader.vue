<template>
    <div class="text-container">
        <h1>
            {{ props.title }}
        </h1>
        <p> {{ props.subtitle }}
        </p>
    </div>
</template>
<script lang="ts" setup>
interface AuthFormHeaderProps {
    title: string
    subtitle: string
}
const props = defineProps<AuthFormHeaderProps>();
</script>

<style scoped>
.text-container {
    text-align: center;
    margin-bottom: var(--space-6);
}

h1 {
    color: var(--color-text-primary);
    font-size: 34px;
    margin-bottom: var(--space-3);
    font-weight: 500;
}

p {
    font-size: 18px;
    color: var(--color-text-secondary);
}
</style>