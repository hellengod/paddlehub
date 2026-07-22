<template>
    <div class="text-container">
        <h1>
            {{ $props.title }}
        </h1>
        <p> {{ $props.subtitle }}
        </p>
    </div>
</template>
<script lang="ts" setup>
interface AuthFormHeader {
    title: string
    subtitle: string
}
const props = defineProps<AuthFormHeader>();
</script>

<style scoped>
.text-container {
    text-align: center;
    margin-bottom: 24px;
}
</style>