<template>
    <div class="container-right">
        <div class="login-card">
            <div class="text-container">
            <h1>
                Entrar na sua conta
            </h1>
            <p>Que bom te ver por aqui!</p>
            </div>
            <BaseInput label="E-mail"
            type="email"
            placeholder="seu@email.com"
            />
        </div>
    </div>
</template>
<script setup lang="ts">
import BaseInput from '@/components/base/BaseInput.vue';
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

.text-container{
    text-align: center;
    margin-bottom: 30px;
}

h1{
    color: aliceblue;
    font-size: 40px;
    margin-bottom: 10px;
    font-weight: 500;
}

p{
    font-size: 20px;
}
</style>