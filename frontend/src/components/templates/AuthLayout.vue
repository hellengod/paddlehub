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
</style>