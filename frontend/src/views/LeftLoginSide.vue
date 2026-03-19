<template>
    <div class="container-left">
        <div class="content">
            <img src="/logo-paddlehub-grande.png" alt="" class="logo-grande">
            <h1>Conecte rios, <br>pessoas e <span class="highlight">remadas</span></h1>
            <h3>Descubra novos rios, registre suas experiências<br> e encontre outros remadores pleo caminho</h3>
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
</style>