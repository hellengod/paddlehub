<template>
    <div class="divider"></div>
    <div class="cadastro">
        <span>{{ props.helperText }}</span>

        <router-link :to="props.to">
            {{ props.linkText }}
            <img src="/arrow-green.svg" class="link-icon" alt="" />
        </router-link>
    </div>
</template>

<script lang="ts" setup>
interface AuthFormFooterProps {
    helperText: string
    linkText: string
    to: string
}

const props = defineProps<AuthFormFooterProps>()
</script>

<style scoped>
.divider {
    width: 100%;
    height: 1px;
    margin-top: 36px;
    background-color: rgba(255, 255, 255, 0.12);
}

.cadastro {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-top: 20px;
    font-size: 15px;
}

.cadastro a {
    display: flex;
    align-items: center;
    text-decoration: none;
}

.link-icon {
    width: 30px;

}
</style>