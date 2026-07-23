<template>
    <section class="container">
        <div class="imagem-background">
            <img src="/imagem-fundo.png" alt="Imagem de fundo" />
        </div>

        <div class="text-block">
            <h1>Descubra novos rios.</h1>
            <h1>Encontre <span>remadores.</span></h1>
            <h1>Viva novas <span>aventuras.</span></h1>
            <p>Explore locais, participe de encontros e compartilhe experiencias com a comunidade.</p>
        </div>

        <div class="actions">
            <router-link to="mapa">
                <BaseButton label="Explorar Mapa" width="180px" fontSize="16px" backgroundColor="#207974" />
            </router-link>
            <router-link to="eventos">
                <BaseButton label="Ver Eventos" width="180px" fontSize="16px" backgroundColor="#207974"></BaseButton>
            </router-link>
        </div>
    </section>
</template>

<script setup lang="ts">
import BaseButton from '@/components/atoms/BaseButton.vue';


</script>

<style scoped>
.imagem-background {
    position: absolute;
    right: 0;
    width: calc(100vw - 350px);
    height: 450px;
    overflow: hidden;
    border-radius: 0 0 0 16px;
}

.imagem-background img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: right center;
    filter: brightness(50%);

    --webkit-mask-image: linear-gradient(to right,
            transparent 0%,
            transparent 18%,
            black 70%,
            black 100%);

    mask-image: linear-gradient(to right,
            transparent 0%,
            transparent 18%,
            black 70%,
            black 100%);
}

.text-block {
    padding-top: 50px;
    padding-left: 50px;
    width: 450px;

}

h1 {
    font-weight: 600;
    font-size: clamp(1rem, 2.5vw, 2.25rem);
    color: aliceblue;

}

span {
    color: #36c9c1;
    font-weight: 700;
}

p {
    margin-top: 24px;
    font-size: 18px;
    font-weight: 400;
    color: rgb(203, 204, 206);
}

.actions {
    display: flex;
    padding-left: 50px;
    gap: 30px;
}
</style>