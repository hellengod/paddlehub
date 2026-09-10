<template>
  <div
    class="base-input"
    :class="rootClasses"
    :style="rootStyles"
  >
    <label :for="inputId">
      {{ props.label }}
      <span v-if="props.required" class="required-marker" aria-hidden="true">*</span>
    </label>

    <div class="input-wrapper">
      <span v-if="$slots.icon || props.icon" class="input-icon" aria-hidden="true">
        <slot name="icon">
          <img v-if="props.icon" :src="props.icon" alt="">
        </slot>
      </span>

      <input
        v-bind="inputAttrs"
        :id="inputId"
        class="input"
        :class="{
          'input--with-icon': $slots.icon || props.icon,
          'input--with-suffix': props.suffix,
        }"
        :type="props.type"
        :placeholder="props.placeholder"
        :value="props.modelValue"
        :required="props.required"
        @input="handleInput"
      >

      <span v-if="props.suffix" class="input-suffix">{{ props.suffix }}</span>
    </div>

    <small v-if="props.helperText" class="helper-text">{{ props.helperText }}</small>
  </div>
</template>

<script setup lang="ts">
import { computed, useAttrs, useId } from 'vue';
import type { HTMLAttributes } from 'vue';

defineOptions({ inheritAttrs: false });

type BaseInputVariant = 'default' | 'compact' | 'coordinate';

interface BaseInputProps {
  label: string;
  modelValue: string;
  id?: string;
  type?: string;
  placeholder?: string;
  variant?: BaseInputVariant;
  required?: boolean;
  helperText?: string;
  suffix?: string;
  icon?: string;
  height?: string;
  fontSize?: string;
  backgroundColor?: string;
  borderColor?: string;
  textColor?: string;
  labelColor?: string;
}

const props = withDefaults(defineProps<BaseInputProps>(), {
  id: undefined,
  type: 'text',
  placeholder: '',
  variant: 'default',
  required: false,
  helperText: '',
  suffix: '',
  icon: undefined,
  height: '54px',
  fontSize: '17px',
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
const inputId = computed(() => props.id || `base-input-${generatedId}`);
const inputAttrs = computed(() => {
  const { class: _class, style: _style, ...nativeInputAttrs } = attrs;
  return nativeInputAttrs;
});
const fieldStyles = computed(() => ({
  '--base-input-height': props.height,
  '--base-input-font-size': props.fontSize,
  '--base-input-background': props.backgroundColor,
  '--base-input-border': props.borderColor,
  '--base-input-color': props.textColor,
  '--base-input-label-color': props.labelColor,
}));
const rootClasses = computed<HTMLAttributes['class']>(() => [
  attrs.class as HTMLAttributes['class'],
  `base-input--${props.variant}`,
]);
const rootStyles = computed<HTMLAttributes['style']>(() => [
  attrs.style as HTMLAttributes['style'],
  fieldStyles.value,
]);

function handleInput(event: Event) {
  emit('update:modelValue', (event.target as HTMLInputElement).value);
}
</script>

<style scoped>
.base-input {
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 0;
  padding-bottom: 18px;
  font-family: var(--font-family-base);
}

.base-input label {
  color: var(--base-input-label-color);
  font-size: 21px;
}

.required-marker {
  color: var(--color-accent-primary);
}

.input-wrapper {
  position: relative;
  width: 100%;
  min-width: 0;
}

.input {
  width: 100%;
  height: var(--base-input-height);
  padding: 0 16px;
  border: 2px solid var(--base-input-border);
  border-radius: var(--radius-sm);
  background: var(--base-input-background);
  color: var(--base-input-color);
  font: inherit;
  font-size: var(--base-input-font-size);
}

.input::placeholder {
  color: rgba(230, 244, 255, 0.4);
}

.input:focus-visible {
  border-color: rgba(58, 212, 203, 0.56);
  outline: none;
  box-shadow: 0 0 0 2px rgba(58, 212, 203, 0.1);
}

.input--with-icon {
  padding-left: 48px;
}

.input--with-suffix {
  padding-right: 44px;
}

.input-icon {
  position: absolute;
  top: 50%;
  left: 14px;
  z-index: 1;
  display: inline-flex;
  width: 28px;
  height: 28px;
  color: var(--color-accent-primary);
  transform: translateY(-50%);
  pointer-events: none;
}

.input-icon :deep(svg),
.input-icon img {
  width: 100%;
  height: 100%;
  display: block;
}

.input-suffix {
  position: absolute;
  top: 50%;
  right: 12px;
  color: rgba(240, 248, 255, 0.72);
  font-size: 11px;
  font-weight: 600;
  transform: translateY(-50%);
  pointer-events: none;
}

.helper-text {
  color: var(--color-text-secondary);
  font-size: 11px;
  line-height: 1.4;
}

.base-input--compact {
  gap: 6px;
  padding-bottom: 0;
}

.base-input--compact label {
  color: rgba(240, 248, 255, 0.86);
  font-size: var(--river-form-label-size, 12px);
  font-weight: 600;
  line-height: 1.2;
}

.base-input--compact .input {
  height: 40px;
  padding: 0 12px;
  border-width: 1px;
  border-color: rgba(127, 185, 215, 0.16);
  border-radius: 8px;
  background: rgba(1, 10, 18, 0.72);
  font-size: var(--river-form-control-size, 13px);
}

.base-input--compact .input--with-icon {
  padding-left: 38px;
}

.base-input--compact .input--with-suffix {
  padding-right: 42px;
}

.base-input--compact .input-icon {
  left: 12px;
  width: 17px;
  height: 17px;
}

.base-input--coordinate {
  gap: 4px;
  padding-bottom: 0;
}

.base-input--coordinate label {
  color: var(--color-text-secondary);
  font-size: var(--river-form-helper-size, 11px);
  line-height: 1.2;
}

.base-input--coordinate .input {
  height: 36px;
  padding: 0 9px;
  border-width: 1px;
  border-color: rgba(58, 212, 203, 0.14);
  border-radius: 9px;
  background: rgba(1, 10, 18, 0.4);
  font-size: var(--river-form-control-size, 13px);
}
</style>
