<template>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="/logo-paddlehub-grande.png" alt="Logo" class="logo">
        </div>
        <SidebarNavigation :items="menuItems"></SidebarNavigation>

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
                    {{ loading ? 'Saindo...' : 'Sair' }}
                </span>
            </button> <span v-if="errorMessage" class="error">
                {{ errorMessage }}
            </span>
        </div>
    </aside>
</template>
<script setup lang="ts">
import useAuth from '@/composable/useAuth';
import { ref } from 'vue';
import { useRouter } from 'vue-router'
import SidebarLink from '@/components/molecules/SidebarLink.vue';
import HomeIcon from '@/components/atoms/icons/HomeIcon.vue';
import MapIcon from '@/components/atoms/icons/MapIcon.vue';
import EventIcon from '@/components/atoms/icons/EventIcon.vue';
import RiverIcon from '@/components/atoms/icons/RiverIcon.vue';
import LogoutIcon from '@/components/atoms/icons/LogoutIcon.vue';
import CommunityIcon from '@/components/atoms/icons/CommunityIcon.vue';
import SidebarProfileCard from '@/components/molecules/SidebarProfileCard.vue';
import SidebarNavigation from './SidebarNavigation.vue';

const loading = ref(false);
const errorMessage = ref('');
const router = useRouter();
const { logout } = useAuth();


const menuItems = [
    {
        icon: HomeIcon,
        to: 'home',
        label: 'Home',
    },
    {
        icon: MapIcon,
        to: 'mapa',
        label: 'Mapa',
    },
    {
        icon: EventIcon,
        to: 'eventos',
        label: 'Eventos',
    },
    {
        icon: RiverIcon,
        to: 'rios',
        label: 'Rios',
    },
    {
        icon: CommunityIcon,
        to: 'community',
        label: 'Comunidade',
    },
]

async function handleLogout() {
    errorMessage.value = '';
    loading.value = true;
    try {
        await logout();
        void router.push({ name: 'login' });

    }
    catch (error) {

        if (error instanceof Error) {
            errorMessage.value = error.message
        } else {
            errorMessage.value = 'Nao foi possivel fazer logout'
        }


    } finally {
        loading.value = false;

    }
}
</script>
<style scoped>
.sidebar {
    width: 300px;
    border: 1px solid rgba(69, 199, 255, 0.096);
    display: flex;
    flex-direction: column;
    background-color: #03141e;
    border-radius: 10px;
}


.sidebar-header {
    display: flex;
    justify-content: center;
}

.logo {
    max-width: 250px;

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

.logout-label {
    font-size: 14px;
    line-height: 1;
}

.error {
    color: darkred;
}

.logout-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}



.sidebar-footer {
    margin-top: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 16px 15px;
}
</style>
