<template>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="\public\logo-paddlehub-grande.png" alt="Logo" class="logo">
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li v-for="item in menuItems" :key="item.to">
                    <SidebarLink :icon="item.icon" :to="item.to" :label="item.label"></SidebarLink>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
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
<script setup lang="ts">import useAuth from '@/composable/useAuth';
import { ref } from 'vue';
import { useRouter } from 'vue-router'
import SidebarLink from '../SidebarLink.vue';
import HomeIcon from '../icons/HomeIcon.vue';
import MapIcon from '../icons/MapIcon.vue';
import EventIcon from '../icons/EventIcon.vue';
import RiverIcon from '../icons/RiverIcon.vue';
import LogoutIcon from '../icons/LogoutIcon.vue';
import CommunityIcon from '../icons/CommunityIcon.vue';

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

.sidebar-nav {
    padding-inline: 15px;
}

.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sidebar-footer {
    margin-top: auto;
    padding: 10px 15px;
}
</style>
