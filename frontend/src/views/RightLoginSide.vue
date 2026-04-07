<template>
    <div class="container-right">
        <div class="login-card">
            <div class="text-container">
                <h1>
                    Entrar na sua conta
                </h1>
                <p>Que bom te ver por aqui!</p>
            </div>
            <BaseInput label="E-mail" type="email" placeholder="seu@email.com" v-model="email"
                icon=".\letter-svgrepo-com.svg" />
            <BaseInput label="Senha" type="password" placeholder="Digite sua senha" v-model="password"
                icon=".\padlock-outlined-svgrepo-com.svg" />
            <div class="login-actions">

                <label class="remember-me">
                    <input type="checkbox" />
                    <span>Lembrar de mim</span>
                </label>

                <a href="">Esqueci minha senha</a>
            </div>

            <div class="submit-container">
                <button class="submit">
                    Entrar
                    <img class="submit-icon" src="/arrow-sm-right-svgrepo-com.svg" />

                </button>
                <div class="divider"></div>
                <div class="cadastro"> <span>Nao tem uma conta?</span>
                    <a href="">Criar conta <img src="/arrow-green.svg" class="link-icon"></a>
                </div>


            </div>

        </div>
    </div>
</template>
<script setup lang="ts">
import BaseInput from '@/components/base/BaseInput.vue';
import { ref } from 'vue';

const email = ref('');
const password = ref('');
</script>

<style scoped>
.container-right {
    height: 100%;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #08161c;
}

.login-card {
    width: 100%;
    max-width: 600px;
    padding: 50px;
    border-radius: 15px;
    background: rgba(33, 86, 109, 0.096);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(69, 199, 255, 0.096);

}

.text-container {
    text-align: center;
    margin-bottom: 30px;
}

h1 {
    color: aliceblue;
    font-size: 40px;
    margin-bottom: 10px;
    font-weight: 500;
}

p {
    font-size: 20px;
}

.login-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 15px;
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
    margin-top: 50px;
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
    margin-top: 60px;
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

</style>