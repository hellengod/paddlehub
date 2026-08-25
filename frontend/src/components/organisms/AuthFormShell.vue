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
    background-color: var(--color-bg-surface);
    padding: 24px 0;
}

.login-card {
    width: min(100%, 500px);
    height: max-content;
    padding: var(--space-6);
    border-radius: var(--radius-lg);
    background: var(--color-surface-glass);
    backdrop-filter: blur(10px);
    border: 1px solid var(--color-border-panel);
}

.login-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: var(--space-3) 0;
    font-size: 18px;
}

.submit {
    background-color: var(--color-action-primary);
    color: var(--color-action-primary-text);
    text-align: center;
    height: 65px;
    width: 100%;
    border-radius: var(--radius-sm);
    font-size: 20px;
    display: flex;
    align-items: center;
    position: relative;
    justify-content: center;
    border: 2px solid var(--color-border-strong);
    margin-top: var(--space-8);
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
    color: var(--color-danger);
}

@media (max-height: 800px) {
    .container-right {
        align-items: flex-start;
        padding: var(--space-6) 0;
    }

    .login-card {
        margin: var(--space-6) 0;
        padding: var(--space-6);
    }
}

@media (max-width: 768px) {
    .container-right {
        min-height: auto;
        align-items: flex-start;
        background: transparent;
        padding: 0 24px 40px;
    }

    .login-card {
        width: min(100%, 540px);
        padding: 34px 24px 28px;
        border-radius: 32px;
        background:
            linear-gradient(180deg, rgba(3, 18, 26, 0.88) 0%, rgba(2, 15, 22, 0.97) 100%);
        backdrop-filter: blur(18px);
        border: 1px solid rgba(54, 201, 193, 0.36);
        box-shadow: 0 28px 60px rgba(0, 0, 0, 0.34);
    }

    .login-actions {
        flex-wrap: wrap;
        gap: 14px;
        margin: 10px 0 0;
    }

    .submit-container {
        margin-top: 10px;
    }

    .submit {
        border-radius: 18px;
        font-size: clamp(20px, 3vw, 24px);
        background: linear-gradient(90deg, #1dc0c3 0%, #14918b 100%);
        border: 1px solid rgba(88, 240, 230, 0.26);
        box-shadow: 0 18px 34px rgba(20, 145, 139, 0.24);
    }

    .login-card :deep(.container) {
        gap: 12px;
        padding-bottom: 22px;
        font-size: 21px;
    }

    .login-card :deep(label) {
        font-weight: 500;
    }

    .login-card :deep(.input) {
        height: 64px;
        border-radius: 18px;
        border-width: 1px;
        padding: 0 18px;
        background: rgba(7, 24, 34, 0.76);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.03);
        transition: border-color 160ms ease, box-shadow 160ms ease;
    }

    .login-card :deep(.input::placeholder) {
        color: rgba(203, 204, 206, 0.52);
    }

    .login-card :deep(.input:focus) {
        outline: none;
        border-color: rgba(54, 201, 193, 0.55);
        box-shadow:
            0 0 0 4px rgba(54, 201, 193, 0.12),
            inset 0 0 0 1px rgba(255, 255, 255, 0.03);
    }

    .login-card :deep(.input--with-icon) {
        padding-left: 56px;
    }

    .login-card :deep(.icon) {
        width: 24px;
        left: 18px;
        opacity: 0.8;
    }
}

@media (max-width: 480px) {
    .container-right {
        padding: 0 16px 28px;
    }

    .login-card {
        padding: 28px 18px 24px;
        border-radius: 28px;
    }

    .login-actions {
        font-size: 16px;
        align-items: flex-start;
    }

    .submit {
        height: 60px;
        margin-top: 24px;
    }

    .submit-icon {
        width: 26px;
        right: 16px;
    }

    .login-card :deep(.container) {
        font-size: 18px;
        padding-bottom: 20px;
    }

    .login-card :deep(.input) {
        height: 58px;
    }
}
</style>
