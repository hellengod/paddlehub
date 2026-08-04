<template>
    <div class="sidebar-footer">
        <div class="perfil">
            <SidebarProfileCard name="Hellen Bianchini" location="Rio Juquia, SP" avatar-url="/profile-user.svg"
                route-name="perfil"></SidebarProfileCard>
        </div>
        <button @click="emit('logout')" :disabled="loading" class="logout-button">
            <span class="logout-icon" aria-hidden="true">
                <LogoutIcon></LogoutIcon>
            </span>
            <span class="logout-label">
                {{ props.loading ? 'Saindo...' : 'Sair' }}
            </span>
        </button> <span v-if="errorMessage" class="error">
            {{ props.errorMessage }}
        </span>
    </div>
</template>
<script lang="ts" setup>
import LogoutIcon from '@/components/atoms/icons/LogoutIcon.vue';
import SidebarProfileCard from '@/components/molecules/SidebarProfileCard.vue';

interface SidebarFooterProps {
    loading: boolean,
    errorMessage: string | null,

}

const props = defineProps<SidebarFooterProps>();

const emit = defineEmits(['logout']);
function handleLogout() {
    emit('logout');
}
</script>
<style scoped>
.sidebar-footer {
    margin-top: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 16px 15px;
}

.logout-button {
    display: flex;
    align-items: center;
    gap: 20px;
    width: 100%;
    border: 1px solid transparent;
    border-radius: 10px;
    background: transparent;
    padding: 20px 14px;
    cursor: pointer;
    font: inherit;
    color: aliceblue;
    text-decoration: none;
    transition:
        color 0.2s ease,
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.logout-button:hover {
    color: #3be4db;
    background: rgba(27, 179, 153, 0.096);
    border-color: rgba(255, 255, 255, 0.123);
}

.logout-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.logout-label {
    font-size: 14px;
    line-height: 1;
}

.error {
    color: darkred;
}

.logout-icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.logout-icon :deep(svg) {
    width: 100%;
    height: 100%;
    display: block;
}
</style>