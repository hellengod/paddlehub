<template>
    <div class="profile-card">
        <div class="header">
            <div class="avatar">
                <img v-if="props.avatarUrl" :src="props.avatarUrl" :alt="props.name">
                <span v-else class="avatar-fallback" aria-hidden="true">
                    <ProfileUser></ProfileUser>
                </span>
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
import ProfileUser from '@/components/atoms/icons/ProfileUser.vue';

interface SidebarProfileCardProps {
    name: string;
    location: string;
    avatarUrl?: string | null;
    routeName: string;
}

const props = defineProps<SidebarProfileCardProps>();

</script>

<style scoped>
.profile-card {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    padding: var(--space-4);
    border: 1px solid var(--color-border-subtle);
    border-radius: var(--radius-sm);
}

.header {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.avatar-fallback {
    width: 100%;
    height: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--color-text-primary);
    color: var(--color-bg-panel);
}

.avatar-fallback :deep(svg) {
    width: 100%;
    height: 100%;
    display: block;
}

.text-block {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.name {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-text-primary);
}

.location {
    font-size: 14px;
    color: var(--color-text-secondary);
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
    border: 1px solid var(--color-border-subtle);
    border-radius: var(--radius-sm);
    background: transparent;
    color: var(--color-accent-strong);
    font-size: 14px;
    line-height: 1;
    text-decoration: none;
    transition:
        color 0.2s ease,
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.profile-link:hover {
    color: var(--color-accent-strong);
    background: var(--color-accent-soft);
    border-color: var(--color-border-subtle);
}
</style>
