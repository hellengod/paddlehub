<template>
  <div class="container">
    <label for="input"> {{ props.label }}</label>
    <div class="input-wrapper">
      <img :src="props.icon" class="icon" />
      <input :type="props.type" :placeholder="props.placeholder" :value="props.modelValue" @input="handleInput"
        class="input">
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
}

const props = defineProps<BaseInputProps>();
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
  height: 54px;
  background-color: transparent;
  border-radius: 10px;
  border: 2px solid rgba(255, 255, 255, 0.123);
  font-size: 17px;
  width: 100%;
  padding-left: 48px;
  color: aliceblue;
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

label {
  color: rgba(240, 248, 255, 0.801);
}
</style>
