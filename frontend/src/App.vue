<template>
  <RouterView />
  <FeedbackHost />
  <ConfirmDialogHost />
</template>
<script setup lang="ts">
import ConfirmDialogHost from '@/components/organisms/ConfirmDialogHost.vue';
import FeedbackHost from '@/components/organisms/FeedbackHost.vue';
</script>
<style scoped></style>
