<template>
  <section class="location-picker">
    <div class="picker-header">
      <div>
        <h3>Trecho no mapa</h3>
        <p>Escolha qual ponto esta ativo e clique no mapa para marcar a entrada e a saida do rio.</p>
      </div>

      <BaseButton
        v-if="hasAnySelection"
        type="button"
        class="clear-button"
        width="auto"
        min-height="36px"
        padding="0 12px"
        font-size="0.82rem"
        border-width="1px"
        @click="clearAllSelections"
      >
        Limpar pontos
      </BaseButton>
    </div>

    <div class="selection-mode">
      <BaseButton
        type="button"
        class="mode-chip"
        width="auto"
        min-height="36px"
        padding="0 12px"
        font-size="0.82rem"
        border-width="1px"
        :class="{ 'mode-chip--active': activePoint === 'start' }"
        :aria-pressed="activePoint === 'start'"
        @click="setActivePoint('start')"
      >
        Marcando entrada
      </BaseButton>

      <BaseButton
        type="button"
        class="mode-chip mode-chip--end"
        width="auto"
        min-height="36px"
        padding="0 12px"
        font-size="0.82rem"
        border-width="1px"
        :class="{ 'mode-chip--active': activePoint === 'end' }"
        :aria-pressed="activePoint === 'end'"
        @click="setActivePoint('end')"
      >
        Marcando saida
      </BaseButton>
    </div>

    <div class="map-shell">
      <div v-if="isLoading" class="map-overlay">
        Carregando mapa...
      </div>

      <div v-else-if="loadError" class="map-overlay map-overlay--error">
        {{ loadError }}
      </div>

      <div ref="mapContainerRef" class="map-surface" :class="{ 'map-surface--hidden': isLoading || !!loadError }"></div>
    </div>

    <div class="selection-summary-grid">
      <article class="selection-card" :class="{ 'selection-card--active': activePoint === 'start' }">
        <div class="selection-card__header">
          <div>
            <div class="selection-label">Entrada</div>
            <p>Onde o trecho comeca.</p>
          </div>

          <BaseButton
            v-if="hasStartSelection"
            type="button"
            class="summary-action"
            width="auto"
            min-height="32px"
            padding="0 10px"
            font-size="0.82rem"
            border-width="1px"
            @click="clearSelection('start')"
          >
            Limpar
          </BaseButton>
        </div>

        <div v-if="hasStartSelection" class="selection-values">
          <span>Lat {{ formatCoordinate(props.startLatitude!) }}</span>
          <span>Lng {{ formatCoordinate(props.startLongitude!) }}</span>
        </div>

        <p v-else class="selection-placeholder">
          Nenhum ponto marcado ainda.
        </p>
      </article>

      <article class="selection-card" :class="{ 'selection-card--active': activePoint === 'end' }">
        <div class="selection-card__header">
          <div>
            <div class="selection-label">Saida</div>
            <p>Onde o trecho termina.</p>
          </div>

          <BaseButton
            v-if="hasEndSelection"
            type="button"
            class="summary-action"
            width="auto"
            min-height="32px"
            padding="0 10px"
            font-size="0.82rem"
            border-width="1px"
            @click="clearSelection('end')"
          >
            Limpar
          </BaseButton>
        </div>

        <div v-if="hasEndSelection" class="selection-values">
          <span>Lat {{ formatCoordinate(props.endLatitude!) }}</span>
          <span>Lng {{ formatCoordinate(props.endLongitude!) }}</span>
        </div>

        <p v-else class="selection-placeholder">
          Nenhum ponto marcado ainda.
        </p>
      </article>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import BaseButton from '@/components/atoms/BaseButton.vue';

type SelectionPoint = 'start' | 'end';

interface RiverLocationPickerProps {
  startLatitude: number | null;
  startLongitude: number | null;
  endLatitude: number | null;
  endLongitude: number | null;
}

interface MapLibreMarker {
  addTo(map: MapLibreMap): MapLibreMarker;
  remove(): void;
  setLngLat(lngLat: [number, number]): MapLibreMarker;
}

interface MapLibreMap {
  addControl(control: unknown, position?: string): void;
  fitBounds(
    bounds: [[number, number], [number, number]],
    options?: { padding?: number; maxZoom?: number },
  ): void;
  flyTo(options: { center: [number, number]; zoom?: number }): void;
  on(event: string, listener: (event: { lngLat: { lat: number; lng: number } }) => void): void;
  remove(): void;
  resize(): void;
}

interface MapLibreNamespace {
  Map: new (options: Record<string, unknown>) => MapLibreMap;
  Marker: new (options?: Record<string, unknown>) => MapLibreMarker;
  NavigationControl: new (options?: Record<string, unknown>) => unknown;
}

declare global {
  interface Window {
    maplibregl?: MapLibreNamespace;
  }
}

const MAPLIBRE_JS_URL = 'https://unpkg.com/maplibre-gl@5.12.0/dist/maplibre-gl.js';
const MAPLIBRE_CSS_URL = 'https://unpkg.com/maplibre-gl@5.12.0/dist/maplibre-gl.css';
const MAPLIBRE_SCRIPT_ID = 'maplibre-gl-script';
const MAPLIBRE_CSS_ID = 'maplibre-gl-stylesheet';
const DEFAULT_CENTER: [number, number] = [-47.2, -22.7];
const DEFAULT_ZOOM = 5.2;
const FOCUSED_ZOOM = 9.4;
const START_MARKER_COLOR = '#36c9c1';
const END_MARKER_COLOR = '#f0b35f';

let mapLibreLoader: Promise<MapLibreNamespace> | null = null;

const props = defineProps<RiverLocationPickerProps>();
const emit = defineEmits<{
  (event: 'update:startLatitude', value: number | null): void;
  (event: 'update:startLongitude', value: number | null): void;
  (event: 'update:endLatitude', value: number | null): void;
  (event: 'update:endLongitude', value: number | null): void;
}>();

const mapContainerRef = ref<HTMLElement | null>(null);
const isLoading = ref(true);
const loadError = ref('');
const activePoint = ref<SelectionPoint>('start');

let mapInstance: MapLibreMap | null = null;
let startMarkerInstance: MapLibreMarker | null = null;
let endMarkerInstance: MapLibreMarker | null = null;
let mapLibreApi: MapLibreNamespace | null = null;

const hasStartSelection = computed(() => isCoordinatePair(props.startLatitude, props.startLongitude));
const hasEndSelection = computed(() => isCoordinatePair(props.endLatitude, props.endLongitude));
const hasAnySelection = computed(() => hasStartSelection.value || hasEndSelection.value);

watch(
  () => [props.startLatitude, props.startLongitude, props.endLatitude, props.endLongitude] as const,
  ([startLatitude, startLongitude, endLatitude, endLongitude]) => {
    if (!mapInstance || !mapLibreApi) {
      return;
    }

    syncMarkersAndViewport(startLatitude, startLongitude, endLatitude, endLongitude);
  },
);

onMounted(() => {
  void initializeMap();
  window.addEventListener('resize', handleWindowResize);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleWindowResize);
  startMarkerInstance?.remove();
  endMarkerInstance?.remove();
  mapInstance?.remove();
  startMarkerInstance = null;
  endMarkerInstance = null;
  mapInstance = null;
});

function ensureMapLibreCss() {
  if (document.getElementById(MAPLIBRE_CSS_ID)) {
    return;
  }

  const link = document.createElement('link');
  link.id = MAPLIBRE_CSS_ID;
  link.rel = 'stylesheet';
  link.href = MAPLIBRE_CSS_URL;
  document.head.appendChild(link);
}

function loadMapLibre() {
  ensureMapLibreCss();

  if (window.maplibregl) {
    return Promise.resolve(window.maplibregl);
  }

  if (mapLibreLoader) {
    return mapLibreLoader;
  }

  mapLibreLoader = new Promise<MapLibreNamespace>((resolve, reject) => {
    const existingScript = document.getElementById(MAPLIBRE_SCRIPT_ID) as HTMLScriptElement | null;

    if (existingScript) {
      existingScript.addEventListener('load', handleLoad, { once: true });
      existingScript.addEventListener('error', handleError, { once: true });
      return;
    }

    const script = document.createElement('script');
    script.id = MAPLIBRE_SCRIPT_ID;
    script.src = MAPLIBRE_JS_URL;
    script.async = true;
    script.addEventListener('load', handleLoad, { once: true });
    script.addEventListener('error', handleError, { once: true });
    document.head.appendChild(script);

    function handleLoad() {
      if (window.maplibregl) {
        resolve(window.maplibregl);
        return;
      }

      reject(new Error('MapLibre carregou sem expor a API global esperada.'));
    }

    function handleError() {
      reject(new Error('Nao foi possivel carregar a biblioteca do mapa.'));
    }
  }).catch((error) => {
    mapLibreLoader = null;
    throw error;
  });

  return mapLibreLoader;
}

async function initializeMap() {
  if (!mapContainerRef.value) {
    return;
  }

  isLoading.value = true;
  loadError.value = '';

  try {
    mapLibreApi = await loadMapLibre();
    await nextTick();

    if (!mapContainerRef.value || !mapLibreApi) {
      return;
    }

    const initialCenter = getInitialCenter();
    const initialZoom = hasAnySelection.value ? FOCUSED_ZOOM : DEFAULT_ZOOM;

    mapInstance = new mapLibreApi.Map({
      container: mapContainerRef.value,
      style: 'https://demotiles.maplibre.org/style.json',
      center: initialCenter,
      zoom: initialZoom,
      attributionControl: true,
    });

    mapInstance.addControl(new mapLibreApi.NavigationControl({ showCompass: false }), 'top-right');
    mapInstance.on('click', handleMapClick);
    syncMarkersAndViewport(props.startLatitude, props.startLongitude, props.endLatitude, props.endLongitude);

    requestAnimationFrame(() => {
      mapInstance?.resize();
    });
  } catch (error) {
    loadError.value = error instanceof Error
      ? `${error.message} Verifique sua conexao para usar o seletor de mapa.`
      : 'Nao foi possivel abrir o mapa.';
  } finally {
    isLoading.value = false;
  }
}

function handleMapClick(event: { lngLat: { lat: number; lng: number } }) {
  const latitude = roundCoordinate(event.lngLat.lat);
  const longitude = roundCoordinate(event.lngLat.lng);

  if (activePoint.value === 'start') {
    emit('update:startLatitude', latitude);
    emit('update:startLongitude', longitude);
    syncMarkersAndViewport(latitude, longitude, props.endLatitude, props.endLongitude);

    if (!hasEndSelection.value) {
      activePoint.value = 'end';
    }

    return;
  }

  emit('update:endLatitude', latitude);
  emit('update:endLongitude', longitude);
  syncMarkersAndViewport(props.startLatitude, props.startLongitude, latitude, longitude);
}

function syncMarkersAndViewport(
  startLatitude: number | null,
  startLongitude: number | null,
  endLatitude: number | null,
  endLongitude: number | null,
) {
  startMarkerInstance = syncMarker(startMarkerInstance, startLatitude, startLongitude, START_MARKER_COLOR);
  endMarkerInstance = syncMarker(endMarkerInstance, endLatitude, endLongitude, END_MARKER_COLOR);
  syncViewport(startLatitude, startLongitude, endLatitude, endLongitude);
}

function syncMarker(
  markerInstance: MapLibreMarker | null,
  latitude: number | null,
  longitude: number | null,
  color: string,
) {
  if (!mapInstance || !mapLibreApi) {
    return markerInstance;
  }

  if (!isCoordinatePair(latitude, longitude)) {
    markerInstance?.remove();
    return null;
  }

  const safeLatitude = latitude as number;
  const safeLongitude = longitude as number;
  const lngLat: [number, number] = [safeLongitude, safeLatitude];

  if (!markerInstance) {
    return new mapLibreApi.Marker({
      color,
      scale: 1.15,
    }).setLngLat(lngLat).addTo(mapInstance);
  }

  markerInstance.setLngLat(lngLat);
  return markerInstance;
}

function syncViewport(
  startLatitude: number | null,
  startLongitude: number | null,
  endLatitude: number | null,
  endLongitude: number | null,
) {
  if (!mapInstance) {
    return;
  }

  const hasStart = isCoordinatePair(startLatitude, startLongitude);
  const hasEnd = isCoordinatePair(endLatitude, endLongitude);

  if (hasStart && hasEnd) {
    const safeStartLatitude = startLatitude as number;
    const safeStartLongitude = startLongitude as number;
    const safeEndLatitude = endLatitude as number;
    const safeEndLongitude = endLongitude as number;
    const southWest: [number, number] = [
      Math.min(safeStartLongitude, safeEndLongitude),
      Math.min(safeStartLatitude, safeEndLatitude),
    ];
    const northEast: [number, number] = [
      Math.max(safeStartLongitude, safeEndLongitude),
      Math.max(safeStartLatitude, safeEndLatitude),
    ];

    if (southWest[0] === northEast[0] && southWest[1] === northEast[1]) {
      mapInstance.flyTo({
        center: [safeStartLongitude, safeStartLatitude],
        zoom: FOCUSED_ZOOM,
      });
      return;
    }

    mapInstance.fitBounds([southWest, northEast], {
      padding: 48,
      maxZoom: FOCUSED_ZOOM,
    });
    return;
  }

  if (hasStart) {
    const safeStartLatitude = startLatitude as number;
    const safeStartLongitude = startLongitude as number;
    mapInstance.flyTo({
      center: [safeStartLongitude, safeStartLatitude],
      zoom: FOCUSED_ZOOM,
    });
    return;
  }

  if (hasEnd) {
    const safeEndLatitude = endLatitude as number;
    const safeEndLongitude = endLongitude as number;
    mapInstance.flyTo({
      center: [safeEndLongitude, safeEndLatitude],
      zoom: FOCUSED_ZOOM,
    });
    return;
  }

  mapInstance.flyTo({
    center: DEFAULT_CENTER,
    zoom: DEFAULT_ZOOM,
  });
}

function clearSelection(point: SelectionPoint) {
  activePoint.value = point;

  if (point === 'start') {
    emit('update:startLatitude', null);
    emit('update:startLongitude', null);
    syncMarkersAndViewport(null, null, props.endLatitude, props.endLongitude);
    return;
  }

  emit('update:endLatitude', null);
  emit('update:endLongitude', null);
  syncMarkersAndViewport(props.startLatitude, props.startLongitude, null, null);
}

function clearAllSelections() {
  activePoint.value = 'start';
  emit('update:startLatitude', null);
  emit('update:startLongitude', null);
  emit('update:endLatitude', null);
  emit('update:endLongitude', null);
  syncMarkersAndViewport(null, null, null, null);
}

function setActivePoint(point: SelectionPoint) {
  activePoint.value = point;
}

function getInitialCenter(): [number, number] {
  if (isCoordinatePair(props.startLatitude, props.startLongitude)) {
    return [props.startLongitude as number, props.startLatitude as number];
  }

  if (isCoordinatePair(props.endLatitude, props.endLongitude)) {
    return [props.endLongitude as number, props.endLatitude as number];
  }

  return DEFAULT_CENTER;
}

function handleWindowResize() {
  mapInstance?.resize();
}

function isCoordinatePair(latitude: number | null, longitude: number | null) {
  return latitude !== null && longitude !== null;
}

function roundCoordinate(value: number) {
  return Number(value.toFixed(6));
}

function formatCoordinate(value: number) {
  return value.toFixed(6);
}
</script>

<style scoped>
.location-picker {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.picker-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.picker-header h3 {
  color: var(--color-text-primary);
  font-size: 1rem;
  font-weight: 600;
}

.picker-header p {
  margin-top: 4px;
  color: var(--color-text-secondary);
  font-size: 0.92rem;
  line-height: 1.45;
}

.clear-button,
.summary-action,
.mode-chip {
  min-height: 36px;
  padding: 0 12px;
  border: 1px solid var(--color-border-subtle);
  border-radius: 999px;
  background: transparent;
  color: var(--color-accent-strong);
  font: inherit;
  font-size: 0.85rem;
  cursor: pointer;
}

.selection-mode {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.mode-chip {
  color: rgba(240, 248, 255, 0.78);
}

.mode-chip--active {
  border-color: rgba(54, 201, 193, 0.35);
  background: rgba(22, 87, 89, 0.35);
  color: var(--color-text-primary);
}

.mode-chip--end.mode-chip--active {
  border-color: rgba(240, 179, 95, 0.34);
  background: rgba(117, 76, 24, 0.26);
}

.map-shell {
  position: relative;
  min-height: 320px;
  border: 1px solid var(--color-border-subtle);
  border-radius: 16px;
  overflow: hidden;
  background:
    radial-gradient(circle at top right, rgba(54, 201, 193, 0.18), transparent 32%),
    rgba(1, 10, 18, 0.72);
}

.map-surface {
  width: 100%;
  height: 320px;
}

.map-surface--hidden {
  opacity: 0;
}

.map-overlay {
  position: absolute;
  inset: 0;
  z-index: 2;
  display: grid;
  place-items: center;
  padding: 20px;
  color: var(--color-text-primary);
  text-align: center;
  background: rgba(1, 10, 18, 0.82);
}

.map-overlay--error {
  color: #ffd4d4;
}

.selection-summary-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.selection-card {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 14px 16px;
  border: 1px solid rgba(58, 212, 203, 0.14);
  border-radius: 14px;
  background: rgba(8, 21, 31, 0.74);
}

.selection-card--active {
  border-color: rgba(58, 212, 203, 0.28);
  box-shadow: 0 0 0 1px rgba(58, 212, 203, 0.08) inset;
}

.selection-card__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}

.selection-card__header p {
  margin-top: 4px;
  color: var(--color-text-secondary);
  font-size: 0.85rem;
  line-height: 1.35;
}

.summary-action {
  min-height: 32px;
  padding: 0 10px;
}

.selection-label {
  color: rgba(240, 248, 255, 0.82);
  font-size: 0.82rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.selection-values {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  color: var(--color-text-primary);
  font-size: 0.95rem;
}

.selection-placeholder {
  color: var(--color-text-secondary);
  font-size: 0.92rem;
}

@media (max-width: 720px) {
  .picker-header {
    flex-direction: column;
  }

  .selection-summary-grid {
    grid-template-columns: 1fr;
  }

  .map-shell,
  .map-surface {
    min-height: 280px;
    height: 280px;
  }
}
</style>
