<template>
    <div class="allsides">
        <div class="left side">
            <LeftLoginSide></LeftLoginSide>
        </div>
        <div class="right side">
            <RightLoginSide></RightLoginSide>
        </div>
    </div>
</template>
<script setup lang="ts">
import LeftLoginSide from './LeftLoginSide.vue';
import RightLoginSide from './RightLoginSide.vue';
</script>
<style scoped>
.allsides {
    height: 100vh;
    width: 100vw;
    display: flex;
    flex-direction: row;
}

.side {
    flex: 1;
}
</style>