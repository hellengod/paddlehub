<template>
  <div class="container">
    <label :style="{ color: props.labelColor }">{{ props.label }}</label>
    <textarea :value="props.modelValue" :placeholder="props.placeholder" :rows="props.rows"
      @input="handleInput" class="textarea" :style="{
        backgroundColor: props.backgroundColor,
        borderColor: props.borderColor,
        color: props.textColor,
        minHeight: props.minHeight,
        fontSize: props.fontSize
      }"></textarea>
  </div>
</template>

<script setup lang="ts">
interface BaseTextareaProps {
  label: string;
  modelValue: string;
  placeholder: string;
  rows?: number;
  minHeight?: string;
  fontSize?: string;
  backgroundColor?: string;
  borderColor?: string;
  textColor?: string;
  labelColor?: string;
}

const props = withDefaults(defineProps<BaseTextareaProps>(), {
  rows: 4,
  minHeight: '120px',
  fontSize: '16px',
  backgroundColor: 'transparent',
  borderColor: 'var(--color-border-subtle)',
  textColor: 'var(--color-text-primary)',
  labelColor: 'var(--color-text-muted)'
});

const emit = defineEmits(['update:modelValue']);

function handleInput(event: Event) {
  const value = (event.target as HTMLTextAreaElement).value;
  emit('update:modelValue', value);
}
</script>

<style scoped>
.container {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.textarea {
  width: 100%;
  padding: 14px 16px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border-subtle);
  resize: vertical;
  font: inherit;
}
</style>
