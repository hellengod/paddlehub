<template>
    <AuthFormShell title="Crie sua conta" subtitle="Vamos comecar a sua jornada conosco" submitLabel="Cadastrar"
        loadingLabel="Cadastrando..." :loading="loading" :errorMessage="errorMessage"
        footerHelperText="Ja tem uma conta?" footerLinkText="Entrar" footerTo="/" @submit="handleRegister">

        <BaseInput label="Nome" type="text" placeholder="Seu nome completo" v-model="name"
            icon="./user-svgrepo-com.svg" />
        <BaseInput label="E-mail" type="email" placeholder="seu@email.com" v-model="email"
            icon=".\letter-svgrepo-com.svg" />
        <BaseInput label="Senha" type="password" placeholder="Digite sua senha" v-model="password"
            icon=".\padlock-outlined-svgrepo-com.svg" />
        <BaseInput label="Confirmar Senha" type="password" placeholder="Confirme sua senha"
            v-model="passwordConfirmation" icon=".\padlock-outlined-svgrepo-com.svg" />

        <template #actions>
            <label class="remember-me">
                <input type="checkbox" />
                <span>Aceito os termos e condicoes</span>
            </label>
        </template>

    </AuthFormShell>
</template>
<script setup lang="ts">
import BaseInput from '@/components/atoms/BaseInput.vue';
import { useAuth } from '@/composables/useAuth';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import AuthFormShell from '@/components/organisms/AuthFormShell.vue';

const { register, loading } = useAuth();
const router = useRouter();
const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const errorMessage = ref('');


async function handleRegister() {
    errorMessage.value = '';
    try {
        await register({
            name: name.value,
            email: email.value,
            password: password.value,
            passwordConfirmation: passwordConfirmation.value
        })
        void router.push({ name: 'login' });

    }
    catch (error) {

        if (error instanceof Error) {
            errorMessage.value = error.message
        } else {
            errorMessage.value = 'Nao foi possivel fazer cadastro'
        }


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
    accent-color: var(--color-action-primary);
}
</style>
