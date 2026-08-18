<template>
    <header class="topbar">
        <div class="topbar-actions">
            <button type="button" class="icon-button" aria-label="Abrir notificacoes">
                <span class="icon" aria-hidden="true">
                    <BellIcon></BellIcon>
                </span>
            </button>
            <RouterLink :to="{ name: 'perfil' }" class="avatar-link" aria-label="Abrir perfil">
                <img v-if="user?.avatarUrl" :src="user.avatarUrl" :alt="avatarAlt">
                <span v-else class="avatar-fallback" aria-hidden="true">
                    <ProfileUser></ProfileUser>
                </span>
            </RouterLink>
        </div>
    </header>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import BellIcon from '@/components/atoms/icons/BellIcon.vue';
import ProfileUser from '@/components/atoms/icons/ProfileUser.vue';
import { useAuth } from '@/composables/useAuth';

const { user } = useAuth();

const avatarAlt = computed(() =>
    user.value?.name ? `Foto de perfil de ${user.value.name}` : 'Foto de perfil'
);
</script>

<style scoped>
.topbar {
    position: absolute;
    top: var(--space-4);
    right: var(--space-5);
    z-index: 10;
}

.topbar-actions {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    color: var(--color-text-primary);
}

.icon-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    padding: 0;
    border: none;
    background: transparent;
    color: inherit;
    cursor: pointer;
    transition:
        color 0.2s ease,
        transform 0.2s ease;
}

.icon-button:hover {
    color: var(--color-accent-strong);
    transform: translateY(-1px);
}

.icon-button:focus-visible {
    outline: 2px solid var(--color-accent-strong);
    outline-offset: 2px;
}

.icon {
    width: 22px;
    height: 22px;
    display: inline-flex;
}

.icon :deep(svg) {
    width: 100%;
    height: 100%;
    display: block;
}

.avatar-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s ease;
}

.avatar-link:hover {
    transform: translateY(-1px);
}

.avatar-link:focus-visible {
    outline: 2px solid var(--color-accent-strong);
    outline-offset: 2px;
}

.avatar-link img {
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
</style>
