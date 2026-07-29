<template>
    <AuthFormShell title="Entrar na sua conta" subtitle="Que bom te ver por aqui!" submitLabel="Entrar"
        loadingLabel="Entrando..." :loading="loading" :errorMessage="errorMessage" footerHelperText="Nao tem uma conta?"
        footerLinkText="Criar conta" footerTo="/cadastro" @submit="handleLogin">

        <BaseInput label="E-mail" type="email" placeholder="seu@email.com" v-model="email"
            icon=".\letter-svgrepo-com.svg" />
        <BaseInput label="Senha" type="password" placeholder="Digite sua senha" v-model="password"
            icon=".\padlock-outlined-svgrepo-com.svg" />

        <template #actions>
            <label class="remember-me">
                <input type="checkbox" />
                <span>Lembrar de mim</span>
            </label>

            <a href="">Esqueci minha senha</a>
        </template>
    </AuthFormShell>
</template>
<script setup lang="ts">
import BaseInput from '@/components/atoms/BaseInput.vue';
import { ref } from 'vue';
import useAuth from '@/composable/useAuth';
import { useRouter } from 'vue-router';
import AuthFormShell from './AuthFormShell.vue';

const { login } = useAuth();
const email = ref('');
const password = ref('');
const loading = ref(false);
const errorMessage = ref('');
const router = useRouter();

async function handleLogin() {
    errorMessage.value = '';
    loading.value = true;

    try {
        await login(email.value, password.value)
        void router.push({ name: 'home' });

    }
    catch (error) {

        if (error instanceof Error) {
            errorMessage.value = error.message
        } else {
            errorMessage.value = 'Nao foi possivel fazer login'
        }


    } finally {
        loading.value = false;

    }
}

</script>

<style scoped>
.remember-me {
    display: flex;
    align-items: center;
    gap: 8px;

}

.remember-me input[type="checkbox"] {
    width: 20px;
    height: 20px;
    accent-color: rgb(22, 99, 73);
}
</style>
