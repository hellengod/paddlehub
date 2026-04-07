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
interface CardProps {
    title: string;
    icon: string;
}

const props = defineProps<CardProps>();
</script>
<style scoped>
.container {
    background: rgba(27, 179, 153, 0.096);
    backdrop-filter: blur(1px);
    border-radius: 8px;
    width: 200px;
    height: 70px;
    border: 1px solid rgba(255, 255, 255, 0.123);
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 5px;

}

.icon {
    width: 40px;
    height: 40px;
    margin-left: 10px;
    border-radius: 7px;
}

img {
    width: 40px;
    height: 40px;
}

.text-content {
    color: aliceblue;
}
</style>