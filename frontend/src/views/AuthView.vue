<template>
    <div class="allsides">
        <div class="left side">
            <LeftAuthSide></LeftAuthSide>
        </div>
        <div class="right side" v-if="isRegister">
            <RightRegisterSide></RightRegisterSide>
        </div>
        <div class="right side" v-else>
            <RightLoginSide></RightLoginSide>
        </div>
    </div>
</template>
<script setup lang="ts">
import { useRoute } from 'vue-router';
import LeftAuthSide from './LeftAuthSide.vue';
import RightLoginSide from './RightLoginSide.vue';
import RightRegisterSide from './RightRegisterSide.vue';
import { computed } from 'vue';

const route = useRoute();

const isRegister = computed(() => {
    return route.meta.authMode === 'register';
}) 
</script>
<style scoped>
.allsides {
    min-height: 100vh;
    width: 100%;

    display: flex;
    flex-direction: row;
}

.side {
    flex: 1;
}
</style>