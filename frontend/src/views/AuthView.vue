<template>
    <AuthLayout>
        <template #left>
            <LeftAuthSide></LeftAuthSide>
        </template>

        <template #right>
            <RightRegisterSide v-if="isRegister"></RightRegisterSide>
            <RightLoginSide v-else></RightLoginSide>
        </template>
    </AuthLayout>
</template>
<script setup lang="ts">
import { useRoute } from 'vue-router';
import LeftAuthSide from '@/components/organisms/LeftAuthSide.vue';
import RightLoginSide from '@/components/organisms/RightLoginSide.vue';
import RightRegisterSide from '@/components/organisms/RightRegisterSide.vue';
import { computed } from 'vue';
import AuthLayout from '@/components/templates/AuthLayout.vue';

const route = useRoute();

const isRegister = computed(() => {
    return route.meta.authMode === 'register';
}) 
</script>