<template>
    <div class="container-root">
        <div class="left-panel">
            <slot name="left"></slot>
        </div>
        <div class="right-panel">
            <slot name="right"></slot>
        </div>
    </div>
</template>

<style scoped>
.container-root {
    min-height: 100vh;
    width: 100%;
    display: flex;
    flex-direction: row;
}

.left-panel,
.right-panel {
    flex: 1
}

@media (max-width: 768px) {
    .container-root {
        display: block;
        background:
            linear-gradient(180deg, rgba(2, 13, 19, 0.96) 0%, rgba(1, 10, 18, 1) 100%);
        overflow-x: hidden;
    }

    .right-panel {
        position: relative;
        z-index: 1;
        margin-top: -132px;
    }
}

@media (max-width: 480px) {
    .right-panel {
        margin-top: -112px;
    }
}
</style>
