<template>
    <AppLayout>
 <button @click="handleLogout" :disabled="loading" class="logout-button">
            {{ loading ? 'Saindo...' : 'Logout' }}
        </button>
        <span v-if="errorMessage" class="error">
            {{ errorMessage }}
        </span>
    </AppLayout>
</template>

<script lang="ts" setup>
import AppLayout from '@/components/layouts/AppLayout.vue';
import useAuth from '@/composable/useAuth';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

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
.error {
    color: darkred;
}

.logout-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>