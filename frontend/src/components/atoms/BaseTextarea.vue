<template>
  <div
    class="base-textarea"
    :class="rootClasses"
    :style="rootStyles"
  >
    <label :for="textareaId">
      {{ props.label }}
      <span v-if="props.required" class="required-marker" aria-hidden="true">*</span>
    </label>

    <div class="textarea-wrapper">
      <span v-if="$slots.icon" class="textarea-icon" aria-hidden="true">
        <slot name="icon"></slot>
      </span>
      <textarea
        v-bind="textareaAttrs"
        :id="textareaId"
        :value="props.modelValue"
        :placeholder="props.placeholder"
        :rows="props.rows"
        :required="props.required"
        class="textarea"
        :class="{ 'textarea--with-icon': $slots.icon }"
        @input="handleInput"
      ></textarea>
    </div>

    <small v-if="props.helperText" class="helper-text">{{ props.helperText }}</small>
  </div>
</template>

<script setup lang="ts">
import { computed, useAttrs, useId } from 'vue';
import type { HTMLAttributes } from 'vue';

defineOptions({ inheritAttrs: false });

type BaseTextareaVariant = 'default' | 'compact';

interface BaseTextareaProps {
  label: string;
  modelValue: string;
  id?: string;
  placeholder?: string;
  variant?: BaseTextareaVariant;
  required?: boolean;
  helperText?: string;
  rows?: number;
  minHeight?: string;
  fontSize?: string;
  backgroundColor?: string;
  borderColor?: string;
  textColor?: string;
  labelColor?: string;
}

const props = withDefaults(defineProps<BaseTextareaProps>(), {
  id: undefined,
  placeholder: '',
  variant: 'default',
  required: false,
  helperText: '',
  rows: 4,
  minHeight: '120px',
  fontSize: '16px',
  backgroundColor: 'transparent',
  borderColor: 'var(--color-border-subtle)',
  textColor: 'var(--color-text-primary)',
  labelColor: 'var(--color-text-muted)',
});

const emit = defineEmits<{
  (event: 'update:modelValue', value: string): void;
}>();

const generatedId = useId();
const attrs = useAttrs();
const textareaId = computed(() => props.id || `base-textarea-${generatedId}`);
const textareaAttrs = computed(() => {
  const { class: _class, style: _style, ...nativeTextareaAttrs } = attrs;
  return nativeTextareaAttrs;
});
const fieldStyles = computed(() => ({
  '--base-textarea-min-height': props.minHeight,
  '--base-textarea-font-size': props.fontSize,
  '--base-textarea-background': props.backgroundColor,
  '--base-textarea-border': props.borderColor,
  '--base-textarea-color': props.textColor,
  '--base-textarea-label-color': props.labelColor,
}));
const rootClasses = computed<HTMLAttributes['class']>(() => [
  attrs.class as HTMLAttributes['class'],
  `base-textarea--${props.variant}`,
]);
const rootStyles = computed<HTMLAttributes['style']>(() => [
  attrs.style as HTMLAttributes['style'],
  fieldStyles.value,
]);

function handleInput(event: Event) {
  emit('update:modelValue', (event.target as HTMLTextAreaElement).value);
}
</script>

<style scoped>
.base-textarea {
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 0;
  font-family: var(--font-family-base);
}

.base-textarea label {
  color: var(--base-textarea-label-color);
}

.required-marker {
  color: var(--color-accent-primary);
}

.textarea-wrapper {
  position: relative;
  min-width: 0;
}

.textarea {
  width: 100%;
  min-height: var(--base-textarea-min-height);
  padding: 14px 16px;
  border: 1px solid var(--base-textarea-border);
  border-radius: var(--radius-sm);
  background: var(--base-textarea-background);
  color: var(--base-textarea-color);
  resize: vertical;
  font: inherit;
  font-size: var(--base-textarea-font-size);
}

.textarea--with-icon {
  padding-left: 38px;
}

.textarea-icon {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 1;
  display: inline-flex;
  width: 17px;
  height: 17px;
  color: var(--color-accent-primary);
  pointer-events: none;
}

.textarea-icon :deep(svg) {
  width: 100%;
  height: 100%;
  display: block;
}

.textarea:focus-visible {
  border-color: rgba(58, 212, 203, 0.56);
  outline: none;
  box-shadow: 0 0 0 2px rgba(58, 212, 203, 0.1);
}

.helper-text {
  color: var(--color-text-secondary);
  font-size: 11px;
  line-height: 1.4;
}

.base-textarea--compact {
  gap: 6px;
}

.base-textarea--compact label {
  color: rgba(240, 248, 255, 0.86);
  font-size: var(--river-form-label-size, 12px);
  font-weight: 600;
  line-height: 1.2;
}

.base-textarea--compact .textarea {
  min-height: 76px;
  padding: 12px 14px;
  border-color: rgba(127, 185, 215, 0.16);
  border-radius: 8px;
  background: rgba(1, 10, 18, 0.72);
  font-size: var(--river-form-control-size, 13px);
}

.base-textarea--compact .textarea--with-icon {
  padding-left: 38px;
}
</style>
