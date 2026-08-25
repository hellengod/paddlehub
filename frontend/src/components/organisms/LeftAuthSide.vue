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

@media (max-width: 768px) {
    .container-left {
        min-height: 402px;
        display: flex;
        align-items: flex-start;
        background-image:
            linear-gradient(180deg, rgba(1, 9, 15, 0.82) 0%, rgba(1, 9, 15, 0.48) 44%, rgba(1, 10, 18, 0.92) 100%),
            url(/img-fundo-login.png);
    }

    .container-left::before {
        background:
            radial-gradient(circle at 78% 62%, rgba(54, 201, 193, 0.2), transparent 28%),
            linear-gradient(180deg, rgba(2, 11, 16, 0.24) 0%, rgba(2, 11, 16, 0.56) 100%);
    }

    .logo-grande {
        width: min(248px, 62vw);
        margin-bottom: -76px;
    }

    .content {
        gap: 14px;
        padding: 0 20px 78px 28px;
        max-width: 372px;
    }

    h1 {
        font-size: clamp(31px, 7.6vw, 42px);
        line-height: 1.08;
        max-width: none;
        margin-left: 8px;
    }

    p {
        font-size: clamp(15px, 3.5vw, 17px);
        line-height: 1.5;
        max-width: none;
        margin-left: 8px;
    }

    p br {
        display: none;
    }

    .cards {
        display: none;
    }
}

@media (max-width: 480px) {
    .container-left {
        min-height: 358px;
    }

    .content {
        gap: 12px;
        padding: 0 16px 62px 22px;
        max-width: 348px;
    }

    .logo-grande {
        width: min(220px, 58vw);
        margin-bottom: -50px;
    }

    h1 {
        font-size: clamp(28px, 7.9vw, 34px);
        margin-left: 6px;
    }

    p {
        font-size: 15px;
        margin-left: 6px;
    }
}
</style>
