<template>
  <div class="container">
    <div class="label-row">
      <label :style="{ color: props.labelColor }">{{ props.label }}</label>
      <span>{{ props.displayValue }}</span>
    </div>

    <input class="range-input" type="range" :min="props.min" :max="props.max" :step="props.step"
      :value="props.modelValue" @input="handleInput">
  </div>
</template>

<script setup lang="ts">
interface BaseRangeSliderProps {
  label: string;
  modelValue: number;
  min?: number;
  max?: number;
  step?: number;
  displayValue?: string;
  labelColor?: string;
}

const props = withDefaults(defineProps<BaseRangeSliderProps>(), {
  min: 0,
  max: 100,
  step: 1,
  displayValue: '',
  labelColor: 'var(--color-text-muted)'
});

const emit = defineEmits(['update:modelValue']);

function handleInput(event: Event) {
  const value = Number((event.target as HTMLInputElement).value);
  emit('update:modelValue', value);
}
</script>

<style scoped>
.container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

label,
span {
  font-size: 14px;
}

span {
  color: var(--color-text-secondary);
}

.range-input {
  width: 100%;
  accent-color: var(--color-accent-strong);
  cursor: pointer;
}
</style>
