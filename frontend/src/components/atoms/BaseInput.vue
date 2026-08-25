<template>
  <div class="container">
    <label :style="{ color: props.labelColor }">{{ props.label }}</label>
    <div class="input-wrapper">
      <img v-if="props.icon" :src="props.icon" class="icon" alt="" aria-hidden="true" />
      <input :type="props.type" :placeholder="props.placeholder" :value="props.modelValue" @input="handleInput"
        class="input" :class="{ 'input--with-icon': props.icon }" :style="{
          height: props.height,
          fontSize: props.fontSize,
          backgroundColor: props.backgroundColor,
          borderColor: props.borderColor,
          color: props.textColor
        }">
    </div>
  </div>
</template>
<script setup lang="ts">
interface BaseInputProps {
  label: string;
  type: string;
  placeholder: string;
  modelValue: string;
  icon?: string
  height?: string
  fontSize?: string
  backgroundColor?: string
  borderColor?: string
  textColor?: string
  labelColor?: string
}

const props = withDefaults(defineProps<BaseInputProps>(), {
  icon: undefined,
  height: '54px',
  fontSize: '17px',
  backgroundColor: 'transparent',
  borderColor: 'var(--color-border-subtle)',
  textColor: 'var(--color-text-primary)',
  labelColor: 'var(--color-text-muted)'
});
const emit = defineEmits(['update:modelValue']);
function handleInput(event: Event) {
  const value = (event.target as HTMLInputElement).value;
  emit('update:modelValue', value);
}
</script>

<style scoped>
.container {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding-bottom: 18px;
  font-size: 21px;

}

.input {
  border-radius: var(--radius-sm);
  border: 2px solid var(--color-border-subtle);
  width: 100%;
  padding: 0 16px;
}

.input--with-icon {
  padding-left: 48px;
}

.input-wrapper {
  width: 100%;
  position: relative;
  width: 100%;
}

.icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 28px;
}

</style>
