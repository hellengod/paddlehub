<template>
    <div class="container-right">
        <form class="login-card" @submit.prevent="emit('submit')">
            <AuthFormHeader :title="props.title" :subtitle="props.subtitle"></AuthFormHeader>
            <slot></slot>
            <span v-if="props.errorMessage" class="error">
                {{ props.errorMessage }}
            </span>
            <div class="login-actions">
                <slot name="actions"></slot>
            </div>
            <div class="submit-container">
                <button class="submit" :disabled="props.loading" type="submit">
                    {{ props.loading ? props.loadingLabel : props.submitLabel }}
                    <img class="submit-icon" src="/arrow-sm-right-svgrepo-com.svg" />

                </button>
                <AuthFormFooter :helperText="props.footerHelperText" :linkText="props.footerLinkText"
                    :to="props.footerTo"></AuthFormFooter>
            </div>
        </form>
    </div>
</template>

<script lang="ts" setup>
import AuthFormFooter from '@/components/molecules/AuthFormFooter.vue';
import AuthFormHeader from '@/components/molecules/AuthFormHeader.vue';

interface AuthFormShellProps {
    title: string,
    subtitle: string,
    submitLabel: string,
    loadingLabel: string,
    loading: boolean,
    errorMessage: string | null,
    footerHelperText: string,
    footerLinkText: string,
    footerTo: string
}

const props = defineProps<AuthFormShellProps>();
const emit = defineEmits(['submit'])
</script>

<style scoped>
.container-right {
    min-height: 100vh;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #08161c;
    padding: 24px 0;
}

.login-card {
    width: min(100%, 500px);
    height: max-content;
    padding: 25px;
    border-radius: 15px;
    background: rgba(33, 86, 109, 0.096);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(69, 199, 255, 0.096);
}

.login-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 12px 0;
    font-size: 18px;
}

.submit {
    background-color: rgb(22, 99, 73);
    color: aliceblue;
    text-align: center;
    height: 65px;
    width: 100%;
    border-radius: 10px;
    font-size: 20px;
    display: flex;
    align-items: center;
    position: relative;
    justify-content: center;
    border: 2px solid rgb(20, 61, 47);
    margin-top: 32px;
}

.submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.submit-icon {
    width: 30px;
    position: absolute;
    right: 15px;
}

.error {
    color: darkred;
}

@media (max-height: 800px) {
    .container-right {
        align-items: flex-start;
        padding: 24px 0;
    }

    .login-card {
        margin: 24px 0;
        padding: 24px;
    }
}
</style>