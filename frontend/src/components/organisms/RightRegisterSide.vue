<template>
    <div class="container-right">
        <form class="login-card" @submit.prevent="handleRegister">
            <div class="text-container">
                <h1>
                    Crie sua conta
                </h1>
                <p>Vamos comecar a sua jornada conosco</p>
            </div>
            <BaseInput label="Nome" type="text" placeholder="Seu nome completo" v-model="name"
                icon="./user-svgrepo-com.svg" />
            <BaseInput label="E-mail" type="email" placeholder="seu@email.com" v-model="email"
                icon=".\letter-svgrepo-com.svg" />
            <BaseInput label="Senha" type="password" placeholder="Digite sua senha" v-model="password"
                icon=".\padlock-outlined-svgrepo-com.svg" />
            <BaseInput label="Confirmar Senha" type="password" placeholder="Confirme sua senha"
                v-model="password_confirmation" icon=".\padlock-outlined-svgrepo-com.svg" />
            <span v-if="errorMessage" class="error">
                {{ errorMessage }}
            </span>
            <div class="login-actions">

                <label class="remember-me">
                    <input type="checkbox" />
                    <span>Aceito os termos e condicoes</span>
                </label>


            </div>

            <div class="submit-container">
                <button class="submit" :disabled="loading" type="submit">
                    {{ loading ? 'Cadastrando...' : 'Cadastrar' }}
                    <img class="submit-icon" src="/arrow-sm-right-svgrepo-com.svg" />

                </button>
                <div class="divider"></div>
                <div class="cadastro"> <span>Ja tem uma conta?</span>
                    <router-link to="/">Entrar <img src="/arrow-green.svg" class="link-icon"></router-link>
                </div>


            </div>

        </form>
    </div>
</template>
<script setup lang="ts">
import BaseInput from '@/components/atoms/BaseInput.vue';
import useAuth from '@/composable/useAuth';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const { register } = useAuth();
const router = useRouter();
const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const loading = ref(false);
const errorMessage = ref('');


async function handleRegister() {
    errorMessage.value = '';
    loading.value = true;

    try {
        await register(name.value, email.value, password.value, password_confirmation.value)
        void router.push({ name: 'login' });

    }
    catch (error) {

        if (error instanceof Error) {
            errorMessage.value = error.message
        } else {
            errorMessage.value = 'Nao foi possivel fazer cadastro'
        }


    } finally {
        loading.value = false;

    }
}
</script>

<style scoped>
.container-right {
    height: 100%;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    background-color: #08161c;
    padding: 24px 0;
}


.login-card {
    width: min(100%, 500px);
    height: max-content;
    padding: 25px;
    border-radius: 15px;
    background: rgba(33, 86, 109, 0.096);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(69, 199, 255, 0.096);
}

.text-container {
    text-align: center;
    margin-bottom: 24px;
}

h1 {
    color: aliceblue;
    font-size: 34px;
    margin-bottom: 10px;
    font-weight: 500;
}

p {
    font-size: 18px;
}

.login-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 12px 0;
    font-size: 18px;
}

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

.submit {
    background-color: rgb(22, 99, 73);
    color: aliceblue;
    text-align: center;
    height: 65px;
    width: 100%;
    border-radius: 10px;
    font-size: 20px;
    display: flex;
    align-items: center;
    position: relative;
    justify-content: center;
    border: 2px solid rgb(20, 61, 47);
    margin-top: 32px;
}

.submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.submit-icon {
    width: 30px;
    position: absolute;
    right: 15px;
}

.link-icon {
    width: 30px;

}

.divider {
    width: 100%;
    height: 1px;
    margin-top: 36px;
    background-color: rgba(255, 255, 255, 0.12);
}

.cadastro {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-top: 20px;
    font-size: 15px;
}

.cadastro a {
    display: flex;
    align-items: center;
    text-decoration: none;
}

.error {
    color: darkred;
}

@media (max-height: 800px) {
    .container-right {
        align-items: flex-start;
        padding: 24px 0;
    }

    .login-card {
        margin: 24px 0;
        padding: 24px;
    }
}
</style>
