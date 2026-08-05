<template>
    <div class="container-left">
        <div class="content">
            <img src="/logo-paddlehub-grande.png" alt="" class="logo-grande">
            <h1>Conecte rios, <br>pessoas e <span class="highlight">remadas.</span></h1>
            <p>Descubra novos rios, registre suas experiências<br> e encontre outros remadores pelo caminho.</p>
            <div class="cards">
                <AuthFeatureCard title="Explore rios no mapa" icon="/map-pin-alt-svgrepo-com.svg"></AuthFeatureCard>
                <AuthFeatureCard title="Compartilhe suas aventuras" icon="/camera-svgrepo-com.svg"></AuthFeatureCard>
                <AuthFeatureCard title="Organize encontros" icon="/people-team-svgrepo-com.svg"></AuthFeatureCard>
            </div>

        </div>
    </div>
</template>
<script setup lang="ts">
import AuthFeatureCard from '@/components/molecules/AuthFeatureCard.vue';

</script>
<style scoped>
.container-left {
    background-color: var(--color-action-primary);
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
    padding-left: 60px;
}

h1 {
    color: var(--color-text-primary);
    font-weight: bold;
    font-size: 55px;
}

.highlight {
    color: var(--color-accent-primary);
}

p {
    font-size: 20px;
    color: var(--color-text-secondary);

}

.cards {
    display: flex;
    gap: var(--space-3);
    margin-top: 70px;
}
</style>
