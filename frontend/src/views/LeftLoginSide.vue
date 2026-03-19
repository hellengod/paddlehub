<template>
    <div class="container-left">
        <div class="content">
            <img src="/logo-paddlehub-grande.png" alt="" class="logo-grande">
            <h1>Conecte rios, <br>pessoas e <span class="highlight">remadas</span></h1>
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

.logo-grande {
    width: 400px;
    height: auto;
}

.content {
    display: flex;
    flex-direction: column;
    align-items: left;
    gap: 0px;
}

h1,
span {
    font-weight: bold;

}

.highlight {
    color: rgb(22, 99, 73);
}
</style>