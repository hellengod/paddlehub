<template>
    <div class="divider"></div>
    <div class="footer-content">
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
    margin-top: var(--space-6);
    background-color: var(--color-border-subtle);
}

.footer-content {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: var(--space-5);
    margin-top: var(--space-5);
    font-size: 15px;
}

.footer-content span {
    color: var(--color-text-secondary);
}

.footer-content a {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: var(--color-accent-primary);
}

.link-icon {
    width: 30px;
}

@media (max-width: 480px) {
    .footer-content {
        flex-wrap: wrap;
        gap: 8px 14px;
        font-size: 16px;
    }

    .link-icon {
        width: 24px;
    }
}
</style>
