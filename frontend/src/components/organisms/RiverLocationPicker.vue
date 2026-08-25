<template>
  <section class="location-picker">
    <div class="picker-header">
      <div>
        <h3>Ponto inicial no mapa</h3>
        <p>Clique no mapa para marcar onde o rio comeca.</p>
      </div>

      <button v-if="hasSelection" type="button" class="clear-button" @click="clearSelection">
        Limpar ponto
      </button>
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

    <div class="selection-summary">
      <div class="selection-label">Ponto selecionado</div>

      <div v-if="hasSelection" class="selection-values">
        <span>Lat {{ formatCoordinate(props.latitude!) }}</span>
        <span>Lng {{ formatCoordinate(props.longitude!) }}</span>
      </div>

      <p v-else class="selection-placeholder">
        Nenhum ponto marcado ainda.
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface RiverLocationPickerProps {
  latitude: number | null;
  longitude: number | null;
}

interface MapLibreMarker {
  addTo(map: MapLibreMap): MapLibreMarker;
  remove(): void;
  setLngLat(lngLat: [number, number]): MapLibreMarker;
}

interface MapLibreMap {
  addControl(control: unknown, position?: string): void;
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

let mapLibreLoader: Promise<MapLibreNamespace> | null = null;

const props = defineProps<RiverLocationPickerProps>();
const emit = defineEmits(['update:latitude', 'update:longitude']);

const mapContainerRef = ref<HTMLElement | null>(null);
const isLoading = ref(true);
const loadError = ref('');

let mapInstance: MapLibreMap | null = null;
let markerInstance: MapLibreMarker | null = null;
let mapLibreApi: MapLibreNamespace | null = null;

const hasSelection = computed(() => props.latitude !== null && props.longitude !== null);

watch(
  () => [props.latitude, props.longitude] as const,
  ([latitude, longitude]) => {
    if (!mapInstance || !mapLibreApi) {
      return;
    }

    syncMarker(latitude, longitude);
  }
);

onMounted(() => {
  void initializeMap();
  window.addEventListener('resize', handleWindowResize);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleWindowResize);
  markerInstance?.remove();
  mapInstance?.remove();
  markerInstance = null;
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

    const initialCenter: [number, number] = hasSelection.value && props.longitude !== null && props.latitude !== null
      ? [props.longitude, props.latitude]
      : DEFAULT_CENTER;

    const initialZoom = hasSelection.value ? FOCUSED_ZOOM : DEFAULT_ZOOM;

    mapInstance = new mapLibreApi.Map({
      container: mapContainerRef.value,
      style: 'https://demotiles.maplibre.org/style.json',
      center: initialCenter,
      zoom: initialZoom,
      attributionControl: true,
    });

    mapInstance.addControl(new mapLibreApi.NavigationControl({ showCompass: false }), 'top-right');
    mapInstance.on('click', handleMapClick);
    syncMarker(props.latitude, props.longitude);

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

  emit('update:latitude', latitude);
  emit('update:longitude', longitude);
  syncMarker(latitude, longitude);
}

function syncMarker(latitude: number | null, longitude: number | null) {
  if (!mapInstance || !mapLibreApi) {
    return;
  }

  if (latitude === null || longitude === null) {
    markerInstance?.remove();
    markerInstance = null;
    return;
  }

  const lngLat: [number, number] = [longitude, latitude];

  if (!markerInstance) {
    markerInstance = new mapLibreApi.Marker({
      color: '#36c9c1',
      scale: 1.15,
    }).setLngLat(lngLat).addTo(mapInstance);
  } else {
    markerInstance.setLngLat(lngLat);
  }

  mapInstance.flyTo({
    center: lngLat,
    zoom: FOCUSED_ZOOM,
  });
}

function clearSelection() {
  emit('update:latitude', null);
  emit('update:longitude', null);
  syncMarker(null, null);
}

function handleWindowResize() {
  mapInstance?.resize();
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

.clear-button {
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

.selection-summary {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 14px 16px;
  border: 1px solid rgba(58, 212, 203, 0.14);
  border-radius: 14px;
  background: rgba(8, 21, 31, 0.74);
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

  .map-shell,
  .map-surface {
    min-height: 280px;
    height: 280px;
  }
}
</style>
