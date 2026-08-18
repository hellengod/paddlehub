<template>
  <Teleport to="body">
    <div v-if="props.modelValue" class="modal-overlay" @click="closeModal">
      <div class="modal-panel" :style="{ maxWidth: props.maxWidth }" role="dialog" aria-modal="true"
        :aria-labelledby="titleId" @click.stop>
        <header class="modal-header">
          <div class="modal-heading">
            <h2 :id="titleId">{{ props.title }}</h2>
            <p v-if="props.description">{{ props.description }}</p>
          </div>

          <button type="button" class="close-button" aria-label="Fechar modal" @click="closeModal">
            &times;
          </button>
        </header>

        <div class="modal-body">
          <slot></slot>
        </div>

        <footer v-if="$slots.footer" class="modal-footer">
          <slot name="footer"></slot>
        </footer>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, watch } from 'vue';

interface BaseModalProps {
  modelValue: boolean;
  title: string;
  description?: string;
  maxWidth?: string;
}

const props = withDefaults(defineProps<BaseModalProps>(), {
  description: '',
  maxWidth: '840px'
});

const emit = defineEmits(['update:modelValue']);
const titleId = computed(() => `modal-title-${props.title.toLowerCase().replace(/\s+/g, '-')}`);

watch(
  () => props.modelValue,
  (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : '';
  },
  { immediate: true }
);

onBeforeUnmount(() => {
  document.body.style.overflow = '';
});

function closeModal() {
  emit('update:modelValue', false);
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1200;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: rgba(1, 10, 18, 0.7);
  backdrop-filter: blur(6px);
}

.modal-panel {
  width: min(100%, 840px);
  max-height: calc(100vh - 48px);
  overflow: auto;
  border: 1px solid var(--color-border-panel);
  border-radius: var(--radius-lg);
  background: var(--color-bg-panel);
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  padding: 24px 24px 0;
}

.modal-heading h2 {
  font-size: 24px;
  font-weight: 600;
  color: var(--color-text-primary);
}

.modal-heading p {
  margin-top: 8px;
  color: var(--color-text-secondary);
  font-size: 15px;
}

.close-button {
  width: 40px;
  height: 40px;
  border: 1px solid var(--color-border-subtle);
  border-radius: 50%;
  background: transparent;
  color: var(--color-text-primary);
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
  transition:
    border-color 0.2s ease,
    color 0.2s ease,
    transform 0.2s ease;
}

.close-button:hover {
  border-color: var(--color-accent-primary);
  color: var(--color-accent-strong);
  transform: translateY(-1px);
}

.close-button:focus-visible {
  outline: 2px solid var(--color-accent-strong);
  outline-offset: 2px;
}

.modal-body {
  padding: 24px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 0 24px 24px;
}

@media (max-width: 768px) {
  .modal-overlay {
    padding: 16px;
  }

  .modal-header,
  .modal-body,
  .modal-footer {
    padding-left: 20px;
    padding-right: 20px;
  }
}
</style>
