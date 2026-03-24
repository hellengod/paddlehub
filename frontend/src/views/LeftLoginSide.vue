<script setup lang="ts">
import Card from './Card.vue';

</script>

<template>
    <div class="container-left">
        <div class="content">
            <img src="/logo-paddlehub-grande.png" alt="" class="logo-grande">
            <h1>Conecte rios, <br>pessoas e <span class="highlight">remadas.</span></h1>
            <p>Descubra novos rios, registre suas experiências<br> e encontre outros remadores pleo caminho.</p>
            <div class="cards">
                <Card title="Explore rios no mapa" icon="/map-pin-alt-svgrepo-com.svg"></Card>
                <Card title="Compartilhe suas aventuras" icon="/camera-svgrepo-com.svg"></Card>
                <Card title="Organize encontros" icon="/people-team-svgrepo-com.svg"></Card>
            </div>

        </div>
    </div>
</template>
<style scoped>
.container-left {
    background-color: rgb(22, 99, 73);
    height: 100%;
    background-image: url(/img-fundo-login.png);
    background-size: cover;
    background-position: center;
    position: relative
}

.container-left::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
}

.logo-grande {
    width: 400px;
    height: auto;
    margin-bottom: -45px;
}

.content {
    display: flex;
    flex-direction: column;
    position: relative;
    z-index: 1;
    left: 60px;
}

h1 {
    color: whitesmoke;
}

h1,
span {
    font-weight: bold;
    font-size: 45px;

}

.highlight {
    color: rgb(34, 153, 113);
}

p {
    font-size: 18px;

}

.cards{
    display: flex;
    gap: 10px;
    margin-top: 70px;
}
</style>