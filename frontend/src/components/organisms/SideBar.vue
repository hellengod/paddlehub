<template>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="/logo-paddlehub-grande.png" alt="Logo" class="logo">
        </div>
        <SidebarNavigation :items="menuItems"></SidebarNavigation>

        <SidebarFooter :loading="loading" :errorMessage="errorMessage" @logout="handleLogout"></SidebarFooter>

    </aside>
</template>
<script setup lang="ts">
import {useAuth} from '@/composables/useAuth';
import { ref } from 'vue';
import { useRouter } from 'vue-router'
import HomeIcon from '@/components/atoms/icons/HomeIcon.vue';
import MapIcon from '@/components/atoms/icons/MapIcon.vue';
import EventIcon from '@/components/atoms/icons/EventIcon.vue';
import RiverIcon from '@/components/atoms/icons/RiverIcon.vue';
import CommunityIcon from '@/components/atoms/icons/CommunityIcon.vue';
import SidebarNavigation from '@/components/organisms/SidebarNavigation.vue';
import SidebarFooter from '@/components/organisms/SidebarFooter.vue';

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
    border: 1px solid var(--color-border-panel);
    display: flex;
    flex-direction: column;
    background-color: var(--color-bg-panel);
    border-radius: var(--radius-sm);
}


.sidebar-header {
    display: flex;
    justify-content: center;
}

.logo {
    max-width: 250px;

}
</style>
