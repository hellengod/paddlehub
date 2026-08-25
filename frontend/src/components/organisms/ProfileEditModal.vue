<template>
  <BaseModal :model-value="props.modelValue" title="Editar perfil"
    description="Atualize a capa, o rio base e a descricao. Por enquanto a alteracao continua so no front."
    max-width="920px" @update:modelValue="handleModalVisibilityChange">
    <div class="modal-content">
      <section ref="coverCropSurfaceRef" class="cover-preview" :style="coverPreviewStyle"
        :class="{ 'cover-preview--cropping': hasPendingCoverCrop, 'cover-preview--dragging': isCoverDragging }"
        @pointerdown="startCoverDrag" @pointermove="handleCoverDrag" @pointerup="stopCoverDrag"
        @pointercancel="stopCoverDrag">
        <img v-if="hasPendingCoverCrop" ref="coverCropImageRef" class="cover-crop-image" :src="pendingCoverCropSrc!"
          :style="coverCropImageStyle" alt="Pre-visualizacao da nova capa" @load="handlePendingCoverImageLoad"
          draggable="false">
        <img v-else class="cover-image" :class="{ 'cover-image--fallback': !props.hasCustomCover }" :src="props.coverSrc"
          alt="Pre-visualizacao da capa do perfil">
        <div :class="hasPendingCoverCrop ? 'cover-crop-overlay' : 'cover-overlay'"></div>

        <button v-if="!hasPendingCoverCrop" type="button" class="cover-change-button" @click="openCoverPicker">
          <span class="cover-change-icon" aria-hidden="true">
            <PencilIcon></PencilIcon>
          </span>
          <span>Trocar imagem</span>
        </button>

        <input ref="coverInputRef" class="cover-file-input" type="file" accept="image/*" @change="handleCoverChange">
      </section>

      <div v-if="hasPendingCoverCrop" class="cover-crop-controls">
        <p class="cover-helper cover-helper--centered">
          Arraste a imagem e use o zoom para escolher o enquadramento da capa.
        </p>

        <BaseRangeSlider label="Zoom" :model-value="coverZoom" :min="1" :max="3" :step="0.01"
          :display-value="coverZoomLabel" label-color="var(--color-text-primary)"
          @update:modelValue="updateCoverZoom"></BaseRangeSlider>

        <button type="button" class="reset-button" @click="resetCoverCropPosition">
          Centralizar imagem
        </button>

        <div class="cover-crop-actions">
          <button type="button" class="footer-button footer-button--ghost" @click="discardPendingCoverSelection">
            Descartar
          </button>
          <button type="button" class="footer-button footer-button--primary" @click="confirmPendingCoverSelection">
            Usar imagem
          </button>
        </div>
      </div>

      <p v-else class="cover-helper">{{ props.coverSelectionText }}</p>

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
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import BaseInput from '@/components/atoms/BaseInput.vue';
import BaseModal from '@/components/atoms/BaseModal.vue';
import BaseRangeSlider from '@/components/atoms/BaseRangeSlider.vue';
import BaseTextarea from '@/components/atoms/BaseTextarea.vue';
import PencilIcon from '@/components/atoms/icons/PencilIcon.vue';

interface ProfileEditModalProps {
  modelValue: boolean;
  coverSrc: string;
  hasCustomCover: boolean;
  homeRiver: string;
  bio: string;
  coverSelectionText: string;
  coverAspectRatio?: number;
}

interface DragState {
  pointerId: number;
  startX: number;
  startY: number;
  startOffsetX: number;
  startOffsetY: number;
}

const props = defineProps<ProfileEditModalProps>();
const emit = defineEmits(['update:modelValue', 'update:homeRiver', 'update:bio', 'select-cover', 'cancel', 'save']);
const DEFAULT_COVER_CROP_HEIGHT = 250;
const DEFAULT_COVER_ASPECT_RATIO = 4;
const COVER_OUTPUT_WIDTH = 1600;
const coverInputRef = ref<HTMLInputElement | null>(null);
const coverCropSurfaceRef = ref<HTMLElement | null>(null);
const coverCropImageRef = ref<HTMLImageElement | null>(null);
const pendingCoverCropSrc = ref<string | null>(null);
const pendingCoverCropFile = ref<File | null>(null);
const coverNaturalWidth = ref(0);
const coverNaturalHeight = ref(0);
const coverViewportWidth = ref(1);
const coverViewportHeight = ref(DEFAULT_COVER_CROP_HEIGHT);
const coverZoom = ref(1);
const coverOffsetX = ref(0);
const coverOffsetY = ref(0);
const coverDragState = ref<DragState | null>(null);

const coverBaseScale = computed(() => {
  if (!coverNaturalWidth.value || !coverNaturalHeight.value) {
    return 1;
  }

  return Math.max(
    coverViewportWidth.value / coverNaturalWidth.value,
    coverViewportHeight.value / coverNaturalHeight.value
  );
});

const coverDisplayScale = computed(() => coverBaseScale.value * coverZoom.value);
const coverRenderedWidth = computed(() => coverNaturalWidth.value * coverDisplayScale.value);
const coverRenderedHeight = computed(() => coverNaturalHeight.value * coverDisplayScale.value);
const maxCoverOffsetX = computed(() => Math.max(0, (coverRenderedWidth.value - coverViewportWidth.value) / 2));
const maxCoverOffsetY = computed(() => Math.max(0, (coverRenderedHeight.value - coverViewportHeight.value) / 2));
const hasPendingCoverCrop = computed(() => Boolean(pendingCoverCropSrc.value));
const isCoverDragging = computed(() => Boolean(coverDragState.value));
const coverZoomLabel = computed(() => `${Math.round(coverZoom.value * 100)}%`);
const activeCoverAspectRatio = computed(() => props.coverAspectRatio || DEFAULT_COVER_ASPECT_RATIO);
const coverPreviewStyle = computed(() => ({
  aspectRatio: String(activeCoverAspectRatio.value)
}));
const coverCropImageStyle = computed(() => ({
  width: `${coverRenderedWidth.value}px`,
  height: `${coverRenderedHeight.value}px`,
  left: `${coverViewportWidth.value / 2 - coverRenderedWidth.value / 2 + coverOffsetX.value}px`,
  top: `${coverViewportHeight.value / 2 - coverRenderedHeight.value / 2 + coverOffsetY.value}px`
}));

function openCoverPicker() {
  coverInputRef.value?.click();
}

function handleCoverChange(event: Event) {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];

  if (!file) {
    return;
  }

  resetPendingCoverCrop();
  pendingCoverCropFile.value = file;
  pendingCoverCropSrc.value = URL.createObjectURL(file);
  void nextTick(() => {
    measureCoverViewport();
  });
  input.value = '';
}

async function confirmPendingCoverSelection() {
  const imageElement = coverCropImageRef.value;

  if (!imageElement || !pendingCoverCropFile.value || !coverNaturalWidth.value || !coverNaturalHeight.value) {
    return;
  }

  const canvas = document.createElement('canvas');
  const outputWidth = COVER_OUTPUT_WIDTH;
  const outputHeight = Math.round(outputWidth / activeCoverAspectRatio.value);
  canvas.width = outputWidth;
  canvas.height = outputHeight;

  const context = canvas.getContext('2d');

  if (!context) {
    return;
  }

  const scale = coverDisplayScale.value;
  const imageLeft = coverViewportWidth.value / 2 - coverRenderedWidth.value / 2 + coverOffsetX.value;
  const imageTop = coverViewportHeight.value / 2 - coverRenderedHeight.value / 2 + coverOffsetY.value;
  const sourceWidth = coverViewportWidth.value / scale;
  const sourceHeight = coverViewportHeight.value / scale;
  const sourceX = clamp((0 - imageLeft) / scale, 0, coverNaturalWidth.value - sourceWidth);
  const sourceY = clamp((0 - imageTop) / scale, 0, coverNaturalHeight.value - sourceHeight);

  context.drawImage(
    imageElement,
    sourceX,
    sourceY,
    sourceWidth,
    sourceHeight,
    0,
    0,
    outputWidth,
    outputHeight
  );

  const blob = await new Promise<Blob | null>((resolve) => canvas.toBlob(resolve, 'image/png'));

  if (!blob) {
    return;
  }

  emit('select-cover', blob);
  resetPendingCoverCrop();
}

function discardPendingCoverSelection() {
  resetPendingCoverCrop();
}

function resetPendingCoverCrop() {
  if (pendingCoverCropSrc.value) {
    URL.revokeObjectURL(pendingCoverCropSrc.value);
  }

  pendingCoverCropSrc.value = null;
  pendingCoverCropFile.value = null;
  coverNaturalWidth.value = 0;
  coverNaturalHeight.value = 0;
  coverZoom.value = 1;
  coverOffsetX.value = 0;
  coverOffsetY.value = 0;
  coverDragState.value = null;
}

function handleModalVisibilityChange(value: boolean) {
  if (!value) {
    resetPendingCoverCrop();
  }

  emit('update:modelValue', value);
}

watch(
  () => props.modelValue,
  (isOpen) => {
    if (!isOpen) {
      resetPendingCoverCrop();
    }
  }
);

watch(
  () => pendingCoverCropSrc.value,
  (imageSrc) => {
    if (!imageSrc) {
      return;
    }

    void nextTick(() => {
      measureCoverViewport();
    });
  }
);

function handlePendingCoverImageLoad() {
  const imageElement = coverCropImageRef.value;

  if (!imageElement) {
    return;
  }

  coverNaturalWidth.value = imageElement.naturalWidth;
  coverNaturalHeight.value = imageElement.naturalHeight;
  measureCoverViewport();
  resetCoverCropPosition();
}

function updateCoverZoom(value: number) {
  coverZoom.value = value;
  clampCoverOffsets();
}

function startCoverDrag(event: PointerEvent) {
  if (!coverNaturalWidth.value || !coverNaturalHeight.value) {
    return;
  }

  coverDragState.value = {
    pointerId: event.pointerId,
    startX: event.clientX,
    startY: event.clientY,
    startOffsetX: coverOffsetX.value,
    startOffsetY: coverOffsetY.value
  };

  coverCropSurfaceRef.value?.setPointerCapture(event.pointerId);
}

function handleCoverDrag(event: PointerEvent) {
  if (!coverDragState.value || coverDragState.value.pointerId !== event.pointerId) {
    return;
  }

  coverOffsetX.value = clamp(
    coverDragState.value.startOffsetX + event.clientX - coverDragState.value.startX,
    -maxCoverOffsetX.value,
    maxCoverOffsetX.value
  );
  coverOffsetY.value = clamp(
    coverDragState.value.startOffsetY + event.clientY - coverDragState.value.startY,
    -maxCoverOffsetY.value,
    maxCoverOffsetY.value
  );
}

function stopCoverDrag(event: PointerEvent) {
  if (!coverDragState.value || coverDragState.value.pointerId !== event.pointerId) {
    return;
  }

  coverCropSurfaceRef.value?.releasePointerCapture(event.pointerId);
  coverDragState.value = null;
}

function resetCoverCropPosition() {
  coverZoom.value = 1;
  coverOffsetX.value = 0;
  coverOffsetY.value = 0;
}

function measureCoverViewport() {
  coverViewportWidth.value = coverCropSurfaceRef.value?.clientWidth || 1;
  coverViewportHeight.value = coverCropSurfaceRef.value?.clientHeight || DEFAULT_COVER_CROP_HEIGHT;
  clampCoverOffsets();
}

function clampCoverOffsets() {
  coverOffsetX.value = clamp(coverOffsetX.value, -maxCoverOffsetX.value, maxCoverOffsetX.value);
  coverOffsetY.value = clamp(coverOffsetY.value, -maxCoverOffsetY.value, maxCoverOffsetY.value);
}

function clamp(value: number, min: number, max: number) {
  return Math.min(Math.max(value, min), max);
}

function handleWindowResize() {
  if (!hasPendingCoverCrop.value) {
    return;
  }

  measureCoverViewport();
}

window.addEventListener('resize', handleWindowResize);

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleWindowResize);
  resetPendingCoverCrop();
});
</script>

<style scoped>
.modal-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cover-preview {
  position: relative;
  width: 100%;
  border: 1px solid var(--color-border-subtle);
  border-radius: var(--radius-md);
  overflow: hidden;
  background: #0b1c24;
}

.cover-preview--cropping {
  cursor: grab;
  touch-action: none;
  user-select: none;
}

.cover-preview--dragging {
  cursor: grabbing;
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

.cover-helper--centered {
  text-align: center;
}

.fields-grid {
  display: grid;
  gap: 20px;
}

.cover-crop-image {
  position: absolute;
  max-width: none;
  pointer-events: none;
}

.cover-crop-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;
  box-shadow: inset 0 0 0 999px rgba(1, 10, 18, 0.1);
}

.cover-crop-overlay::after {
  content: '';
  position: absolute;
  inset: 14px;
  border: 1px solid rgba(240, 248, 255, 0.45);
  border-radius: calc(var(--radius-md) - 4px);
}

.cover-crop-controls {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cover-crop-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.reset-button,
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

.reset-button {
  border: 1px solid var(--color-border-subtle);
  background: transparent;
  color: var(--color-text-primary);
}

.footer-button--ghost {
  border: 1px solid var(--color-border-subtle);
  background: transparent;
  color: var(--color-text-primary);
}

.reset-button:hover,
.footer-button--ghost:hover {
  border-color: var(--color-accent-primary);
  color: var(--color-accent-strong);
  transform: translateY(-1px);
}

.reset-button:focus-visible,
.cover-change-button:focus-visible,
.footer-button:focus-visible {
  outline: 2px solid var(--color-accent-strong);
  outline-offset: 2px;
}

.footer-button--primary {
  border: 2px solid var(--color-border-strong);
  background: var(--color-action-primary);
  color: var(--color-action-primary-text);
}

@media (max-width: 768px) {
  .cover-crop-actions {
    flex-direction: column;
  }

  .cover-change-button {
    right: 16px;
    bottom: 16px;
  }
}
</style>
