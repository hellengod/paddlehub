<template>
    <div class="container">
        <div class="header">
            <div class="avatar">
                <img :src="props.avatarUrl" :alt="props.name">
            </div>
            <div class="text-block">
                <div class="name">{{ props.name }}</div>
                <div class="location">{{ props.location }}</div>
            </div>
        </div>
        <div class="action">
            <router-link :to="{ name: props.routeName }" class="profile-link">Ver perfil</router-link>
        </div>
    </div>
</template>

<script setup lang="ts">
interface SidebarProfileCardProps {
    name: string;
    location: string;
    avatarUrl: string;
    routeName: string;
}

const props = defineProps<SidebarProfileCardProps>();

</script>

<style scoped>
.container {
    display: flex;
    flex-direction: column;
    gap: 15px;
    padding: 16px;
    border: 1px solid rgba(255, 255, 255, 0.123);
    border-radius: 10px;
}

.header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
}

img {
    width: 100%;
    height: 100%;
    object-fit: cover
}

.text-block {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.name {
    font-size: 16px;
    font-weight: 600;
    color: aliceblue;
}

.location {
    font-size: 14px;

}

.action {
    width: 100%;
}

.profile-link {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    padding: 12px 14px;
    border: 1px solid rgba(255, 255, 255, 0.123);
    border-radius: 10px;
    background: transparent;
    color: #3be4db;
    font-size: 14px;
    line-height: 1;
    text-decoration: none;
    transition:
        color 0.2s ease,
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.profile-link:hover {
    color: #3be4db;
    background: rgba(27, 179, 153, 0.096);
    border-color: rgba(255, 255, 255, 0.123);
}
</style>
