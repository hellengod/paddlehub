<template>
  <BaseModal :model-value="props.modelValue" title="Editar perfil"
    description="Atualize a capa, o rio base e a descricao. Por enquanto a alteracao continua so no front."
    max-width="920px" @update:modelValue="emit('update:modelValue', $event)">
    <div class="modal-content">
      <section class="cover-preview">
        <img class="cover-image" :class="{ 'cover-image--fallback': !props.hasCustomCover }" :src="props.coverSrc"
          alt="Pre-visualizacao da capa do perfil">
        <div class="cover-overlay"></div>

        <button type="button" class="cover-change-button" @click="openCoverPicker">
          <span class="cover-change-icon" aria-hidden="true">
            <PencilIcon></PencilIcon>
          </span>
          <span>Trocar imagem</span>
        </button>

        <input ref="coverInputRef" class="cover-file-input" type="file" accept="image/*" @change="handleCoverChange">
      </section>

      <p class="cover-helper">{{ props.coverSelectionText }}</p>

      <div class="fields-grid">
        <BaseInput label="Rio base" type="text" placeholder="Ex.: Rio Juquia, SP" :model-value="props.homeRiver"
          label-color="var(--color-text-primary)" background-color="rgba(1, 10, 18, 0.78)"
          border-color="var(--color-border-subtle)" text-color="var(--color-text-primary)"
          @update:modelValue="emit('update:homeRiver', $event)"></BaseInput>

        <BaseTextarea label="Descricao" :model-value="props.bio"
          placeholder="Conte um pouco sobre voce, sua modalidade e seu estilo de remada."
          label-color="var(--color-text-primary)" background-color="rgba(1, 10, 18, 0.78)"
          border-color="var(--color-border-subtle)" text-color="var(--color-text-primary)"
          @update:modelValue="emit('update:bio', $event)"></BaseTextarea>
      </div>
    </div>

    <template #footer>
      <button type="button" class="footer-button footer-button--ghost" @click="emit('cancel')">
        Cancelar
      </button>
      <button type="button" class="footer-button footer-button--primary" @click="emit('save')">
        Salvar no front
      </button>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import BaseInput from '@/components/atoms/BaseInput.vue';
import BaseModal from '@/components/atoms/BaseModal.vue';
import BaseTextarea from '@/components/atoms/BaseTextarea.vue';
import PencilIcon from '@/components/atoms/icons/PencilIcon.vue';

interface ProfileEditModalProps {
  modelValue: boolean;
  coverSrc: string;
  hasCustomCover: boolean;
  homeRiver: string;
  bio: string;
  coverSelectionText: string;
}

const props = defineProps<ProfileEditModalProps>();
const emit = defineEmits(['update:modelValue', 'update:homeRiver', 'update:bio', 'select-cover', 'cancel', 'save']);
const coverInputRef = ref<HTMLInputElement | null>(null);

function openCoverPicker() {
  coverInputRef.value?.click();
}

function handleCoverChange(event: Event) {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];

  if (!file) {
    return;
  }

  emit('select-cover', file);
  input.value = '';
}
</script>

<style scoped>
.modal-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cover-preview {
  position: relative;
  min-height: 260px;
  border: 1px solid var(--color-border-subtle);
  border-radius: var(--radius-md);
  overflow: hidden;
  background: #0b1c24;
}

.cover-image {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.cover-image--fallback {
  object-position: center 88%;
}

.cover-overlay {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(180deg, rgba(2, 10, 17, 0.08) 0%, rgba(2, 10, 17, 0.44) 100%);
}

.cover-change-button {
  position: absolute;
  right: 20px;
  bottom: 20px;
  z-index: 1;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  min-height: 48px;
  padding: 0 20px;
  border: 2px solid var(--color-border-strong);
  border-radius: var(--radius-sm);
  background: var(--color-action-primary);
  color: var(--color-action-primary-text);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition:
    background-color 0.2s ease,
    transform 0.2s ease;
}

.cover-change-button:hover,
.footer-button--primary:hover {
  background: var(--color-accent-primary);
  transform: translateY(-1px);
}

.cover-change-button:focus-visible,
.footer-button:focus-visible {
  outline: 2px solid var(--color-accent-strong);
  outline-offset: 2px;
}

.cover-change-icon {
  width: 18px;
  height: 18px;
  display: inline-flex;
}

.cover-change-icon :deep(svg) {
  width: 100%;
  height: 100%;
  display: block;
}

.cover-file-input {
  display: none;
}

.cover-helper {
  color: var(--color-text-secondary);
  font-size: 14px;
}

.fields-grid {
  display: grid;
  gap: 20px;
}

.footer-button {
  min-height: 44px;
  padding: 0 18px;
  border-radius: var(--radius-sm);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition:
    background-color 0.2s ease,
    border-color 0.2s ease,
    color 0.2s ease,
    transform 0.2s ease;
}

.footer-button--ghost {
  border: 1px solid var(--color-border-subtle);
  background: transparent;
  color: var(--color-text-primary);
}

.footer-button--ghost:hover {
  border-color: var(--color-accent-primary);
  color: var(--color-accent-strong);
  transform: translateY(-1px);
}

.footer-button--primary {
  border: 2px solid var(--color-border-strong);
  background: var(--color-action-primary);
  color: var(--color-action-primary-text);
}

@media (max-width: 768px) {
  .cover-preview {
    min-height: 220px;
  }

  .cover-change-button {
    right: 16px;
    bottom: 16px;
  }
}
</style>
