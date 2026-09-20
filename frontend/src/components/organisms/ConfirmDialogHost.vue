<template>
  <BaseModal
    :model-value="dialog !== null"
    :title="dialog?.title ?? ''"
    :description="dialog?.message ?? ''"
    max-width="460px"
    :close-on-backdrop="false"
    @update:model-value="handleVisibilityChange"
  >
    <template #footer>
      <BaseButton
        width="auto"
        min-height="40px"
        padding="0 16px"
        font-size="13px"
        border-width="1px"
        background-color="transparent"
        text-color="var(--color-text-primary)"
        border-color="var(--color-border-subtle)"
        :label="dialog?.cancelLabel ?? 'Cancelar'"
        @click="answer(false)"
      />
      <BaseButton
        width="auto"
        min-height="40px"
        padding="0 16px"
        font-size="13px"
        border-width="1px"
        :background-color="dialog?.variant === 'danger' ? 'rgba(150, 35, 35, 0.72)' : 'var(--color-action-primary)'"
        text-color="#fff"
        :border-color="dialog?.variant === 'danger' ? 'rgba(255, 115, 115, 0.42)' : 'var(--color-border-strong)'"
        :label="dialog?.confirmLabel ?? 'Confirmar'"
        @click="answer(true)"
      />
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import BaseButton from '@/components/atoms/BaseButton.vue';
import BaseModal from '@/components/atoms/BaseModal.vue';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

const { dialog, answer } = useConfirmDialog();

function handleVisibilityChange(isOpen: boolean) {
  if (!isOpen) {
    answer(false);
  }
}
</script>
