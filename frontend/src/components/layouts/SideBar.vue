<template>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="\public\logo-paddlehub-grande.png" alt="Logo" class="logo">
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a>
                        Home
                    </a>
                </li>

                <li>
                    <a>
                        Mapa
                    </a>
                </li>

                <li>
                    <a>
                        Eventos
                    </a>
                </li>

                <li>
                    <a>
                        Configuracao
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a>Perfil</a>
            <button @click="handleLogout" :disabled="loading" class="logout-button">
                {{ loading ? 'Saindo...' : 'Logout' }}
            </button> <span v-if="errorMessage" class="error">
                {{ errorMessage }}
            </span>
        </div>
    </aside>
</template>
<script setup lang="ts">import useAuth from '@/composable/useAuth';
import { ref } from 'vue';
import { useRouter } from 'vue-router'

const loading = ref(false);
const errorMessage = ref('');
const router = useRouter();
const { logout } = useAuth();

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
}


.sidebar-header {
    display: flex;
    justify-content: center;
}

.logo {
    max-width: 250px;

}

.logout-button {
    background: none;
    border: none;
    padding: 0;
    color: #3273dc;
    cursor: pointer;
    font: inherit;
    color: hsla(160, 100%, 37%, 1);
}

.error {
    color: darkred;
}

.logout-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>