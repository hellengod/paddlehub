<template>
    <div class="sidebar-footer">
        <div class="perfil">
            <SidebarProfileCard name="Hellen Bianchini" location="Rio Juquia, SP" avatar-url="/profile-user.svg"
                route-name="perfil"></SidebarProfileCard>
        </div>
        <button @click="handleLogout" :disabled="loading" class="logout-button">
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