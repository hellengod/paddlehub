<template>
  <div class="container">
    <button
      class="button"
      :style="{
        width: props.width,
        fontSize: props.fontSize,
        backgroundColor: props.backgroundColor,
        color: props.textColor,
        borderColor: props.borderColor
      }"
    >
      {{ props.label }}

      <img
        v-if="props.icon"
        class="button-icon"
        :src="props.icon"
        alt=""
      />
    </button>
  </div>
</template>

<script setup lang="ts">
interface BaseButtonProps {
  label: string
  icon?: string
  width?: string
  fontSize?: string
  backgroundColor?: string
  textColor?: string
  borderColor?: string
}

const props = withDefaults(defineProps<BaseButtonProps>(), {
  width: '100%',
  fontSize: '20px',
  backgroundColor: 'var(--color-action-primary)',
  textColor: 'var(--color-action-primary-text)',
  borderColor: 'var(--color-border-strong)'
})
</script>

<style scoped>
.button {
  height: 65px;
  border-radius: var(--radius-sm);
  border: 2px solid;
  text-align: center;
  display: flex;
  align-items: center;
  position: relative;
  justify-content: center;
  margin-top: var(--space-8);
  cursor: pointer;
}

.button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.button-icon {
  width: 30px;
  position: absolute;
  right: 15px;
}
</style>