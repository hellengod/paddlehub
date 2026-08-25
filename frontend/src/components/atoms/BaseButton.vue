<template>
  <button
    :type="props.type"
    class="button"
    :disabled="props.disabled"
    :aria-label="props.ariaLabel"
    :style="buttonStyles"
  >
    <span v-if="$slots.default || props.label" class="button-label">
      <slot>{{ props.label }}</slot>
    </span>

    <span
      v-if="$slots.icon || props.icon"
      class="button-icon"
      :class="`button-icon--${props.iconPosition}`"
      aria-hidden="true"
    >
      <slot name="icon">
        <img v-if="props.icon" :src="props.icon" alt="" />
      </slot>
    </span>
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface BaseButtonProps {
  label?: string;
  type?: 'button' | 'submit' | 'reset';
  disabled?: boolean;
  ariaLabel?: string;
  icon?: string;
  iconPosition?: 'left' | 'right';
  width?: string;
  minHeight?: string;
  padding?: string;
  fontSize?: string;
  fontWeight?: string | number;
  background?: string;
  backgroundColor?: string;
  textColor?: string;
  borderColor?: string;
  borderWidth?: string;
  borderRadius?: string;
  gap?: string;
  iconSize?: string;
}

const props = withDefaults(defineProps<BaseButtonProps>(), {
  label: '',
  type: 'button',
  disabled: false,
  ariaLabel: undefined,
  icon: undefined,
  iconPosition: 'right',
  width: '100%',
  minHeight: '65px',
  padding: '0 18px',
  fontSize: '20px',
  fontWeight: 600,
  background: '',
  backgroundColor: 'var(--color-action-primary)',
  textColor: 'var(--color-action-primary-text)',
  borderColor: 'var(--color-border-strong)',
  borderWidth: '2px',
  borderRadius: 'var(--radius-sm)',
  gap: '10px',
  iconSize: '30px'
});

const buttonStyles = computed(() => ({
  '--button-width': props.width,
  '--button-min-height': props.minHeight,
  '--button-padding': props.padding,
  '--button-font-size': props.fontSize,
  '--button-font-weight': String(props.fontWeight),
  '--button-background': props.background || 'none',
  '--button-background-color': props.background ? 'transparent' : props.backgroundColor,
  '--button-text-color': props.textColor,
  '--button-border-color': props.borderColor,
  '--button-border-width': props.borderWidth,
  '--button-border-radius': props.borderRadius,
  '--button-gap': props.gap,
  '--button-icon-size': props.iconSize
}));
</script>

<style scoped>
.button {
  width: var(--button-width);
  min-height: var(--button-min-height);
  padding: var(--button-padding);
  border-style: solid;
  border-width: var(--button-border-width);
  border-color: var(--button-border-color);
  border-radius: var(--button-border-radius);
  background: var(--button-background);
  background-color: var(--button-background-color);
  color: var(--button-text-color);
  text-align: center;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-family: inherit;
  font-size: var(--button-font-size);
  font-weight: var(--button-font-weight);
  line-height: 1.1;
  gap: var(--button-gap);
  cursor: pointer;
  transition:
    filter 0.2s ease,
    transform 0.2s ease,
    border-color 0.2s ease,
    color 0.2s ease,
    background-color 0.2s ease;
}

.button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.button:focus-visible {
  outline: 2px solid var(--color-accent-strong);
  outline-offset: 2px;
}

.button-label {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: inherit;
}

.button-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: var(--button-icon-size);
  height: var(--button-icon-size);
}

.button-icon--left {
  order: -1;
}

.button-icon img {
  width: 100%;
  height: 100%;
  display: block;
}
</style>
