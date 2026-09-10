<template>
  <div class="base-file-input">
    <label :for="inputId">{{ props.label }}</label>

    <input
      :id="inputId"
      ref="nativeInputRef"
      class="native-input"
      type="file"
      :accept="props.accept"
      @change="handleFileSelection"
    >

    <div class="file-control">
      <div class="cover-preview">
        <img v-if="previewUrl" :src="previewUrl" :alt="props.previewAlt">
        <span v-else>Sem imagem</span>
      </div>

      <div class="file-summary">
        <strong>{{ activeFileName }}</strong>
        <small>{{ props.modelValue ? props.helperText : fallbackHelper }}</small>
      </div>

      <div class="file-actions">
        <BaseButton
          type="button"
          width="auto"
          min-height="36px"
          padding="0 12px"
          font-size="12px"
          border-width="1px"
          border-radius="8px"
          background-color="rgba(22, 87, 89, 0.35)"
          text-color="var(--color-text-primary)"
          border-color="rgba(54, 201, 193, 0.35)"
          :label="props.modelValue ? 'Trocar imagem' : 'Selecionar imagem'"
          @click="openFilePicker"
        />

        <BaseButton
          v-if="props.modelValue"
          type="button"
          width="auto"
          min-height="36px"
          padding="0 12px"
          font-size="12px"
          border-width="1px"
          border-radius="8px"
          background-color="transparent"
          text-color="var(--color-accent-strong)"
          border-color="var(--color-border-subtle)"
          label="Remover"
          @click="removeFile"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import BaseButton from '@/components/atoms/BaseButton.vue';
import { computed, onBeforeUnmount, ref, useId, watch } from 'vue';

interface BaseFileInputProps {
  label: string;
  modelValue: File | null;
  id?: string;
  accept?: string;
  helperText?: string;
  fallbackValue?: File | null;
  fallbackHelperText?: string;
  previewAlt?: string;
}

const props = withDefaults(defineProps<BaseFileInputProps>(), {
  id: undefined,
  accept: 'image/*',
  helperText: 'PNG, JPG ou WEBP.',
  fallbackValue: null,
  fallbackHelperText: 'Captura atual do mapa usada automaticamente.',
  previewAlt: 'Pre-visualizacao da imagem selecionada',
});

const emit = defineEmits<{
  (event: 'update:modelValue', value: File | null): void;
}>();

const nativeInputRef = ref<HTMLInputElement | null>(null);
const generatedId = useId();
const inputId = computed(() => props.id || `base-file-input-${generatedId}`);
const previewUrl = ref('');
const activeFile = computed(() => props.modelValue ?? props.fallbackValue);
const activeFileName = computed(() => {
  if (props.modelValue) {
    return props.modelValue.name;
  }

  return props.fallbackValue ? 'Captura do mapa' : 'Imagem de capa';
});
const fallbackHelper = computed(() => (
  props.fallbackValue ? props.fallbackHelperText : props.helperText
));

watch(
  activeFile,
  (file) => {
    releasePreviewUrl();
    previewUrl.value = file ? URL.createObjectURL(file) : '';
  },
  { immediate: true },
);

onBeforeUnmount(releasePreviewUrl);

function openFilePicker() {
  nativeInputRef.value?.click();
}

function handleFileSelection(event: Event) {
  const input = event.target as HTMLInputElement;
  emit('update:modelValue', input.files?.[0] ?? null);
  input.value = '';
}

function removeFile() {
  emit('update:modelValue', null);
}

function releasePreviewUrl() {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value);
  }
}
</script>

<style scoped>
.base-file-input {
  display: flex;
  flex-direction: column;
  min-width: 0;
  gap: 6px;
  font-family: var(--font-family-base);
}

.base-file-input > label {
  color: rgba(240, 248, 255, 0.86);
  font-size: var(--river-form-label-size, 12px);
  font-weight: 600;
  line-height: 1.2;
}

.native-input {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip: rect(0 0 0 0);
  clip-path: inset(50%);
  white-space: nowrap;
}

.file-control {
  display: grid;
  grid-template-columns: 96px minmax(0, 1fr) auto;
  align-items: center;
  gap: 12px;
  min-height: 78px;
  padding: 8px;
  border: 1px solid rgba(127, 185, 215, 0.16);
  border-radius: 8px;
  background: rgba(1, 10, 18, 0.72);
}

.cover-preview {
  display: grid;
  place-items: center;
  width: 96px;
  height: 60px;
  overflow: hidden;
  border-radius: 6px;
  background: rgba(19, 48, 63, 0.58);
  color: var(--color-text-muted);
  font-size: var(--river-form-helper-size, 11px);
}

.cover-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.file-summary {
  display: flex;
  flex-direction: column;
  min-width: 0;
  gap: 4px;
}

.file-summary strong {
  overflow: hidden;
  color: var(--color-text-primary);
  font-size: var(--river-form-control-size, 13px);
  font-weight: 500;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-summary small {
  color: var(--color-text-muted);
  font-size: var(--river-form-helper-size, 11px);
}

.file-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 8px;
}

@media (max-width: 620px) {
  .file-control {
    grid-template-columns: 72px minmax(0, 1fr);
  }

  .cover-preview {
    width: 72px;
  }

  .file-actions {
    grid-column: 1 / -1;
    justify-content: flex-start;
  }
}
</style>
