<template>
    <section class="imagem-background">
        <img src="/imagem-fundo.png" alt="Imagem de fundo" />
    </section>
</template>

<style scoped>
.imagem-background {
    position: absolute;
    right: 0;
    width: calc(100vw - 350px);
    height: 400px;
    overflow: hidden;
    border-radius: 0 0 0 16px;
}

.imagem-background img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: right center;
    filter: brightness(40%);
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
</style>