<template>
  <BaseModal :model-value="props.modelValue" title="Ajustar foto de perfil"
    description="Escolha o enquadramento da sua foto antes de salvar." max-width="760px"
    @update:modelValue="emit('update:modelValue', $event)">
    <div class="modal-content">
      <section ref="cropSurfaceRef" class="crop-surface" :class="{ 'crop-surface--dragging': isDragging }"
        @pointerdown="startDrag" @pointermove="handleDrag" @pointerup="stopDrag" @pointercancel="stopDrag">
        <img ref="imageRef" class="crop-image" :src="props.imageSrc" alt="Ajuste da foto de perfil"
          :style="cropImageStyle" @load="handleImageLoad" draggable="false">
        <div class="crop-overlay" aria-hidden="true"></div>
      </section>

      <div class="controls">
        <p class="helper-text">Arraste a imagem e use o zoom para escolher a parte que vai aparecer no perfil.</p>

        <BaseRangeSlider label="Zoom" :model-value="zoom" :min="1" :max="3" :step="0.01" :display-value="zoomLabel"
          label-color="var(--color-text-primary)" @update:modelValue="updateZoom"></BaseRangeSlider>

        <button type="button" class="reset-button" @click="resetCrop">
          Centralizar imagem
        </button>
      </div>
    </div>

    <template #footer>
      <button type="button" class="footer-button footer-button--ghost" @click="emit('cancel')">
        Cancelar
      </button>
      <button type="button" class="footer-button footer-button--primary" @click="confirmCrop">
        Usar foto
      </button>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import BaseModal from '@/components/atoms/BaseModal.vue';
import BaseRangeSlider from '@/components/atoms/BaseRangeSlider.vue';

interface ProfileAvatarCropModalProps {
  modelValue: boolean;
  imageSrc: string;
}

interface DragState {
  pointerId: number;
  startX: number;
  startY: number;
  startOffsetX: number;
  startOffsetY: number;
}

const emit = defineEmits(['update:modelValue', 'cancel', 'confirm']);
const props = defineProps<ProfileAvatarCropModalProps>();

const OUTPUT_SIZE = 512;
const DEFAULT_VIEWPORT_SIZE = 320;

const cropSurfaceRef = ref<HTMLElement | null>(null);
const imageRef = ref<HTMLImageElement | null>(null);
const naturalWidth = ref(0);
const naturalHeight = ref(0);
const viewportSize = ref(DEFAULT_VIEWPORT_SIZE);
const zoom = ref(1);
const offsetX = ref(0);
const offsetY = ref(0);
const dragState = ref<DragState | null>(null);

//faz a imagem sempre cobrir toda a area visivel do crop
const baseScale = computed(() => {
  if (!naturalWidth.value || !naturalHeight.value) {
    return 1;
  }

  return Math.max(viewportSize.value / naturalWidth.value, viewportSize.value / naturalHeight.value);
});

const displayScale = computed(() => baseScale.value * zoom.value);
const renderedWidth = computed(() => naturalWidth.value * displayScale.value);
const renderedHeight = computed(() => naturalHeight.value * displayScale.value);
const maxOffsetX = computed(() => Math.max(0, (renderedWidth.value - viewportSize.value) / 2));
const maxOffsetY = computed(() => Math.max(0, (renderedHeight.value - viewportSize.value) / 2));
const isDragging = computed(() => Boolean(dragState.value));
const zoomLabel = computed(() => `${Math.round(zoom.value * 100)}%`);

const cropImageStyle = computed(() => ({
  width: `${renderedWidth.value}px`,
  height: `${renderedHeight.value}px`,
  left: `${viewportSize.value / 2 - renderedWidth.value / 2 + offsetX.value}px`,
  top: `${viewportSize.value / 2 - renderedHeight.value / 2 + offsetY.value}px`
}));

watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) {
      void nextTick(() => {
        measureViewport();
        resetCrop();
      });
    }
  }
);

watch(
  () => props.imageSrc,
  () => {
    resetCrop();
  }
);

function handleImageLoad() {
  const imageElement = imageRef.value;

  if (!imageElement) {
    return;
  }

  naturalWidth.value = imageElement.naturalWidth;
  naturalHeight.value = imageElement.naturalHeight;
  measureViewport();
  resetCrop();
}

function updateZoom(value: number) {
  zoom.value = value;
  clampOffsets();
}

function resetCrop() {
  zoom.value = 1;
  offsetX.value = 0;
  offsetY.value = 0;
}

function clampOffsets() {
  offsetX.value = clamp(offsetX.value, -maxOffsetX.value, maxOffsetX.value);
  offsetY.value = clamp(offsetY.value, -maxOffsetY.value, maxOffsetY.value);
}

function startDrag(event: PointerEvent) {
  if (!naturalWidth.value || !naturalHeight.value) {
    return;
  }

  dragState.value = {
    pointerId: event.pointerId,
    startX: event.clientX,
    startY: event.clientY,
    startOffsetX: offsetX.value,
    startOffsetY: offsetY.value
  };

  cropSurfaceRef.value?.setPointerCapture(event.pointerId);
}

function handleDrag(event: PointerEvent) {
  if (!dragState.value || dragState.value.pointerId !== event.pointerId) {
    return;
  }

  offsetX.value = clamp(
    dragState.value.startOffsetX + event.clientX - dragState.value.startX,
    -maxOffsetX.value,
    maxOffsetX.value
  );
  offsetY.value = clamp(
    dragState.value.startOffsetY + event.clientY - dragState.value.startY,
    -maxOffsetY.value,
    maxOffsetY.value
  );
}

function stopDrag(event: PointerEvent) {
  if (!dragState.value || dragState.value.pointerId !== event.pointerId) {
    return;
  }

  cropSurfaceRef.value?.releasePointerCapture(event.pointerId);
  dragState.value = null;
}

async function confirmCrop() {
  const imageElement = imageRef.value;

  if (!imageElement || !naturalWidth.value || !naturalHeight.value) {
    return;
  }

  const canvas = document.createElement('canvas');
  canvas.width = OUTPUT_SIZE;
  canvas.height = OUTPUT_SIZE;

  const context = canvas.getContext('2d');

  if (!context) {
    return;
  }

  const scale = displayScale.value;
  const imageLeft = viewportSize.value / 2 - renderedWidth.value / 2 + offsetX.value;
  const imageTop = viewportSize.value / 2 - renderedHeight.value / 2 + offsetY.value;
  const sourceWidth = viewportSize.value / scale;
  const sourceHeight = viewportSize.value / scale;
  const sourceX = clamp((0 - imageLeft) / scale, 0, naturalWidth.value - sourceWidth);
  const sourceY = clamp((0 - imageTop) / scale, 0, naturalHeight.value - sourceHeight);

  context.drawImage(
    imageElement,
    sourceX,
    sourceY,
    sourceWidth,
    sourceHeight,
    0,
    0,
    OUTPUT_SIZE,
    OUTPUT_SIZE
  );

  const blob = await new Promise<Blob | null>((resolve) => canvas.toBlob(resolve, 'image/png'));

  if (!blob) {
    return;
  }

  emit('confirm', blob);
}

function clamp(value: number, min: number, max: number) {
  return Math.min(Math.max(value, min), max);
}

function measureViewport() {
  viewportSize.value = cropSurfaceRef.value?.clientWidth || DEFAULT_VIEWPORT_SIZE;
  clampOffsets();
}

window.addEventListener('resize', measureViewport);

onBeforeUnmount(() => {
  window.removeEventListener('resize', measureViewport);
});
</script>

<style scoped>
.modal-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.crop-surface {
  position: relative;
  width: min(100%, 320px);
  aspect-ratio: 1;
  margin: 0 auto;
  overflow: hidden;
  border-radius: 24px;
  background: #061019;
  cursor: grab;
  touch-action: none;
  user-select: none;
}

.crop-surface--dragging {
  cursor: grabbing;
}

.crop-image {
  position: absolute;
  max-width: none;
  pointer-events: none;
}

.crop-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;
  box-shadow: inset 0 0 0 999px rgba(1, 10, 18, 0.42);
}

.crop-overlay::after {
  content: '';
  position: absolute;
  inset: 16px;
  border: 2px solid rgba(240, 248, 255, 0.92);
  border-radius: 50%;
  box-shadow: 0 0 0 999px rgba(1, 10, 18, 0.48);
}

.controls {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.helper-text {
  color: var(--color-text-secondary);
  font-size: 14px;
  text-align: center;
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

.reset-button:hover,
.footer-button--ghost:hover {
  border-color: var(--color-accent-primary);
  color: var(--color-accent-strong);
  transform: translateY(-1px);
}

.reset-button:focus-visible,
.footer-button:focus-visible {
  outline: 2px solid var(--color-accent-strong);
  outline-offset: 2px;
}

.footer-button--ghost {
  border: 1px solid var(--color-border-subtle);
  background: transparent;
  color: var(--color-text-primary);
}

.footer-button--primary {
  border: 2px solid var(--color-border-strong);
  background: var(--color-action-primary);
  color: var(--color-action-primary-text);
}

.footer-button--primary:hover {
  background: var(--color-accent-primary);
  transform: translateY(-1px);
}

@media (max-width: 768px) {
  .crop-surface {
    width: min(100%, 280px);
  }
}
</style>
