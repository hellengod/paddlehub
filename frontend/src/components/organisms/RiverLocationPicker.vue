<template>
  <section class="location-picker">
    <div class="location-search">
      <div class="location-search__controls">
        <BaseInput
          id="river-location-search"
          v-model="locationQuery"
          class="location-search__field"
          label="Pesquisar local"
          type="search"
          placeholder="Cidade, endereco ou ponto de referencia"
          variant="compact"
          autocomplete="off"
          @keydown.enter.prevent="searchForLocation"
        />
        <BaseButton
          type="button"
          class="location-search__button"
          width="auto"
          min-height="40px"
          padding="0 16px"
          font-size="13px"
          border-width="1px"
          border-radius="8px"
          :disabled="locationSearchLoading"
          @click="searchForLocation"
        >
          {{ locationSearchLoading ? 'Buscando...' : 'Buscar' }}
        </BaseButton>
      </div>

      <p v-if="locationSearchError" class="location-search__message location-search__message--error" role="alert">
        {{ locationSearchError }}
      </p>

      <ul v-if="locationResults.length" class="location-search__results" aria-label="Resultados da pesquisa">
        <li v-for="place in locationResults" :key="place.id">
          <BaseButton
            type="button"
            width="100%"
            min-height="auto"
            padding="10px 12px"
            font-size="13px"
            font-weight="400"
            border-width="0"
            border-radius="0"
            @click="focusLocation(place)"
          >
            {{ place.label }}
          </BaseButton>
        </li>
      </ul>

      <a class="location-search__attribution" href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">
        Busca &copy; colaboradores do OpenStreetMap
      </a>
    </div>

    <div class="selection-mode">
      <BaseButton
        type="button"
        class="mode-chip"
        width="auto"
        min-height="36px"
        padding="0 12px"
        font-size="13px"
        border-width="1px"
        :class="{ 'mode-chip--active': activePoint === 'start' }"
        :aria-pressed="activePoint === 'start'"
        @click="setActivePoint('start')"
      >
        <span class="mode-chip__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"></path>
          </svg>
        </span>
        Marcando entrada
      </BaseButton>

      <BaseButton
        type="button"
        class="mode-chip mode-chip--end"
        width="auto"
        min-height="36px"
        padding="0 12px"
        font-size="13px"
        border-width="1px"
        :class="{ 'mode-chip--active': activePoint === 'end' }"
        :aria-pressed="activePoint === 'end'"
        @click="setActivePoint('end')"
      >
        <span class="mode-chip__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"></path>
          </svg>
        </span>
        Marcando saida
      </BaseButton>

      <BaseButton
        v-if="hasStartSelection && hasEndSelection"
        type="button"
        class="mode-chip"
        width="auto"
        min-height="36px"
        padding="0 12px"
        font-size="13px"
        border-width="1px"
        :disabled="routeLoading"
        @click="generateSuggestedRoute"
      >
        {{ routeLoading ? 'Calculando...' : 'Refazer automatico' }}
      </BaseButton>

      <BaseButton
        v-if="hasStartSelection && hasEndSelection"
        type="button"
        class="mode-chip mode-chip--manual"
        width="auto"
        min-height="36px"
        padding="0 12px"
        font-size="13px"
        border-width="1px"
        :class="{ 'mode-chip--active': routeKind === 'manual' }"
        @click="startManualRoute"
      >
        {{ routeKind === 'manual' && activePoint === 'route' ? 'Editando manualmente' : 'Editar manualmente' }}
      </BaseButton>

      <BaseButton
        v-if="hasAnySelection"
        type="button"
        class="clear-button"
        width="auto"
        min-height="36px"
        padding="0 12px"
        font-size="13px"
        border-width="1px"
        @click="clearAllSelections"
      >
        Limpar pontos
      </BaseButton>
    </div>

    <p v-if="routeMessage" class="route-message" :class="{ 'route-message--warning': routeKind === 'manual' }">
      {{ routeMessage }}
    </p>

    <div class="map-shell">
      <div v-if="isLoading" class="map-overlay">
        Carregando mapa...
      </div>

      <div v-else-if="loadError" class="map-overlay map-overlay--error">
        {{ loadError }}
      </div>

      <div ref="mapContainerRef" class="map-surface" :class="{ 'map-surface--hidden': isLoading || !!loadError }"></div>

      <div v-if="!isLoading && !loadError" class="base-map-switch" aria-label="Mapa base">
        <BaseButton
          type="button"
          class="base-map-switch__button"
          width="auto"
          min-height="34px"
          padding="0 12px"
          font-size="12px"
          border-width="0"
          :class="{ 'base-map-switch__button--active': baseMapMode === 'map' }"
          :aria-pressed="baseMapMode === 'map'"
          @click="setBaseMapMode('map')"
        >
          Mapa
        </BaseButton>
        <BaseButton
          type="button"
          class="base-map-switch__button"
          width="auto"
          min-height="34px"
          padding="0 12px"
          font-size="12px"
          border-width="0"
          :class="{ 'base-map-switch__button--active': baseMapMode === 'satellite' }"
          :aria-pressed="baseMapMode === 'satellite'"
          @click="setBaseMapMode('satellite')"
        >
          Satelite
        </BaseButton>
      </div>

      <div v-if="!isLoading && !loadError" class="hydrography-drawer">
        <BaseButton
          type="button"
          class="hydrography-toggle"
          width="auto"
          min-height="34px"
          padding="0 11px"
          font-size="12px"
          border-width="1px"
          :aria-expanded="hydrographyControlsOpen"
          aria-controls="hydrography-controls"
          @click="hydrographyControlsOpen = !hydrographyControlsOpen"
        >
          <span class="hydrography-toggle__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m12 2 9 5-9 5-9-5 9-5Z"></path>
              <path d="m3 12 9 5 9-5"></path>
              <path d="m3 17 9 5 9-5"></path>
            </svg>
          </span>
          {{ hydrographyControlsOpen ? 'Ocultar hidrografia' : 'Comparar hidrografia' }}
        </BaseButton>

        <aside
          v-if="hydrographyControlsOpen"
          id="hydrography-controls"
          class="hydrography-controls"
          aria-label="Camadas hidrograficas de teste"
        >
          <label>
            <input v-model="baseWaterwaysVisible" type="checkbox">
            <span>OpenFreeMap waterways</span>
            <small>{{ baseWaterwayLayerIds.length }} layers</small>
          </label>
          <label>
            <input v-model="overpassVisible" type="checkbox" :disabled="diagnostics.overpass.loading">
            <span>OSM bruto / Overpass</span>
            <small :class="{ 'diagnostic-error': diagnostics.overpass.error }">{{ diagnosticLabel(diagnostics.overpass) }}</small>
          </label>
          <label>
            <input v-model="anaVisible" type="checkbox" :disabled="diagnostics.ana.loading">
            <span>ANA / BHO Hidrografia</span>
            <small :class="{ 'diagnostic-error': diagnostics.ana.error }">{{ diagnosticLabel(diagnostics.ana) }}</small>
          </label>
        </aside>
      </div>
    </div>

    <div class="selection-summary-grid">
      <article class="selection-card" :class="{ 'selection-card--active': activePoint === 'start' }">
        <div class="selection-card__header">
          <div class="selection-card__title">
            <span class="selection-card__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                <circle cx="12" cy="10" r="2.5"></circle>
              </svg>
            </span>
            <div class="selection-label">Entrada</div>
          </div>

          <BaseButton
            v-if="hasStartSelection"
            type="button"
            class="summary-action"
            width="auto"
            min-height="32px"
            padding="0 10px"
            font-size="12px"
            border-width="1px"
            @click="clearSelection('start')"
          >
            Limpar
          </BaseButton>
        </div>

        <div class="coordinate-entry">
          <BaseInput v-model="coordinateDraft.startLatitude" label="Latitude" type="text" variant="coordinate"
            inputmode="decimal" autocomplete="off" placeholder="-22.614025" aria-label="Latitude da entrada"
            @keydown.enter.prevent="applyTypedCoordinates('start')" />
          <BaseInput v-model="coordinateDraft.startLongitude" label="Longitude" type="text" variant="coordinate"
            inputmode="decimal" autocomplete="off" placeholder="-46.458126" aria-label="Longitude da entrada"
            @keydown.enter.prevent="applyTypedCoordinates('start')" />
          <BaseButton type="button" class="coordinate-entry__apply" width="auto" min-height="36px"
            padding="0 10px" font-size="12px" border-width="1px" border-radius="9px"
            @click="applyTypedCoordinates('start')">
            Marcar
          </BaseButton>
        </div>
        <p v-if="coordinateErrors.start" class="coordinate-entry__error" role="alert">
          {{ coordinateErrors.start }}
        </p>
      </article>

      <article class="selection-card" :class="{ 'selection-card--active': activePoint === 'end' }">
        <div class="selection-card__header">
          <div class="selection-card__title">
            <span class="selection-card__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                <circle cx="12" cy="10" r="2.5"></circle>
              </svg>
            </span>
            <div class="selection-label">Saida</div>
          </div>

          <BaseButton
            v-if="hasEndSelection"
            type="button"
            class="summary-action"
            width="auto"
            min-height="32px"
            padding="0 10px"
            font-size="12px"
            border-width="1px"
            @click="clearSelection('end')"
          >
            Limpar
          </BaseButton>
        </div>

        <div class="coordinate-entry">
          <BaseInput v-model="coordinateDraft.endLatitude" label="Latitude" type="text" variant="coordinate"
            inputmode="decimal" autocomplete="off" placeholder="-22.616321" aria-label="Latitude da saida"
            @keydown.enter.prevent="applyTypedCoordinates('end')" />
          <BaseInput v-model="coordinateDraft.endLongitude" label="Longitude" type="text" variant="coordinate"
            inputmode="decimal" autocomplete="off" placeholder="-46.489542" aria-label="Longitude da saida"
            @keydown.enter.prevent="applyTypedCoordinates('end')" />
          <BaseButton type="button" class="coordinate-entry__apply" width="auto" min-height="36px"
            padding="0 10px" font-size="12px" border-width="1px" border-radius="9px"
            @click="applyTypedCoordinates('end')">
            Marcar
          </BaseButton>
        </div>
        <p v-if="coordinateErrors.end" class="coordinate-entry__error" role="alert">
          {{ coordinateErrors.end }}
        </p>
      </article>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import BaseButton from '@/components/atoms/BaseButton.vue';
import BaseInput from '@/components/atoms/BaseInput.vue';
import {
  fetchAnaHydrography,
  fetchOverpassWaterways,
  type GeoJsonFeatureCollection,
} from '@/services/hydrographyDiagnosticService';
import {
  findHydrographyRoute,
  insertRouteCoordinate,
} from '@/services/riverRouteService';
import { searchLocations, type LocationSearchResult } from '@/services/locationSearchService';
import type { RiverCoordinate } from '@/types/rivers';
import * as maplibregl from 'maplibre-gl';
import type { GeoJSONSource, LayerSpecification, Map } from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

type SelectionPoint = 'start' | 'end' | 'route';
type BaseMapMode = 'map' | 'satellite';
type RouteKind = 'idle' | 'automatic' | 'manual';

interface RiverLocationPickerProps {
  startLatitude: number | null;
  startLongitude: number | null;
  endLatitude: number | null;
  endLongitude: number | null;
  routeCoordinates: RiverCoordinate[];
}

const OPEN_FREE_MAP_STYLE = 'https://tiles.openfreemap.org/styles/liberty';
const MAP_MAX_ZOOM = 23;
const ESRI_IMAGERY_NATIVE_MAX_ZOOM = 18;
const ESRI_IMAGERY_SOURCE_ID = 'esri-world-imagery';
const ESRI_IMAGERY_LAYER_ID = 'esri-world-imagery-raster';
const ESRI_IMAGERY_TILE_URL = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
const ESRI_IMAGERY_ATTRIBUTION = 'Source: Esri, Vantor, Earthstar Geographics, and the GIS User Community';
const DEFAULT_CENTER: [number, number] = [-47.0086, -23.9319];
const DEFAULT_ZOOM = 11.2;
const FOCUSED_ZOOM = 14;
const START_MARKER_COLOR = '#36c9c1';
const END_MARKER_COLOR = '#f0b35f';
const TEST_BOUNDS = { west: -47.105, south: -24.035, east: -46.905, north: -23.83 };
const OVERPASS_SOURCE_ID = 'diagnostic-overpass-waterways';
const OVERPASS_LAYER_ID = 'diagnostic-overpass-waterways-line';
const ANA_SOURCE_ID = 'diagnostic-ana-hydrography';
const ANA_LAYER_ID = 'diagnostic-ana-hydrography-line';
const ROUTE_SOURCE_ID = 'river-route';
const ROUTE_LAYER_ID = 'river-route-line';
const MAX_VISIBLE_ROUTE_HANDLES = 96;
const WATERWAY_PATTERN = /(waterway|river|stream|canal|ditch|drain)/i;

type DiagnosticState = { loading: boolean; count: number | null; error: string };

const props = defineProps<RiverLocationPickerProps>();
const emit = defineEmits<{
  (event: 'update:startLatitude', value: number | null): void;
  (event: 'update:startLongitude', value: number | null): void;
  (event: 'update:endLatitude', value: number | null): void;
  (event: 'update:endLongitude', value: number | null): void;
  (event: 'update:routeCoordinates', value: RiverCoordinate[]): void;
  (event: 'mapSnapshot', value: File | null): void;
}>();

const mapContainerRef = ref<HTMLElement | null>(null);
const isLoading = ref(true);
const loadError = ref('');
const activePoint = ref<SelectionPoint>('start');
const editableRouteCoordinates = ref<RiverCoordinate[]>([...props.routeCoordinates]);
const routeLoading = ref(false);
const routeMessage = ref('');
const routeKind = ref<RouteKind>('idle');
const locationQuery = ref('');
const locationResults = ref<LocationSearchResult[]>([]);
const locationSearchLoading = ref(false);
const locationSearchError = ref('');
const baseMapMode = ref<BaseMapMode>('satellite');
const hydrographyControlsOpen = ref(false);
const baseWaterwaysVisible = ref(true);
const overpassVisible = ref(true);
const anaVisible = ref(true);
const baseWaterwayLayerIds = ref<string[]>([]);
const diagnostics = reactive<{ overpass: DiagnosticState; ana: DiagnosticState }>({
  overpass: { loading: true, count: null, error: '' },
  ana: { loading: true, count: null, error: '' },
});
const coordinateDraft = reactive({
  startLatitude: coordinateInputValue(props.startLatitude),
  startLongitude: coordinateInputValue(props.startLongitude),
  endLatitude: coordinateInputValue(props.endLatitude),
  endLongitude: coordinateInputValue(props.endLongitude),
});
const coordinateErrors = reactive({ start: '', end: '' });

let mapInstance: Map | null = null;
let startMarkerInstance: maplibregl.Marker | null = null;
let endMarkerInstance: maplibregl.Marker | null = null;
let routeEditMarkers: maplibregl.Marker[] = [];
let routeRequestId = 0;
let mapSnapshotTimer: number | null = null;
let mapSnapshotRequestId = 0;

const hasStartSelection = computed(() => isCoordinatePair(props.startLatitude, props.startLongitude));
const hasEndSelection = computed(() => isCoordinatePair(props.endLatitude, props.endLongitude));
const hasAnySelection = computed(() => hasStartSelection.value || hasEndSelection.value);
const hasRoute = computed(() => editableRouteCoordinates.value.length >= 2);

watch(
  () => [props.startLatitude, props.startLongitude] as const,
  ([latitude, longitude]) => {
    coordinateDraft.startLatitude = coordinateInputValue(latitude);
    coordinateDraft.startLongitude = coordinateInputValue(longitude);

    if (isCoordinatePair(latitude, longitude)) {
      coordinateErrors.start = '';
    }
  },
);

watch(
  () => [props.endLatitude, props.endLongitude] as const,
  ([latitude, longitude]) => {
    coordinateDraft.endLatitude = coordinateInputValue(latitude);
    coordinateDraft.endLongitude = coordinateInputValue(longitude);

    if (isCoordinatePair(latitude, longitude)) {
      coordinateErrors.end = '';
    }
  },
);

watch(
  () => [props.startLatitude, props.startLongitude, props.endLatitude, props.endLongitude] as const,
  ([startLatitude, startLongitude, endLatitude, endLongitude]) => {
    if (!mapInstance) {
      return;
    }

    syncMarkersAndViewport(startLatitude, startLongitude, endLatitude, endLongitude);

    if (
      isCoordinatePair(startLatitude, startLongitude)
      && isCoordinatePair(endLatitude, endLongitude)
    ) {
      if (routeMatchesEndpointSelection(
        startLatitude as number,
        startLongitude as number,
        endLatitude as number,
        endLongitude as number,
      )) {
        return;
      }

      void generateSuggestedRoute();
    } else {
      routeRequestId += 1;
      routeLoading.value = false;
      routeMessage.value = '';
      setRouteCoordinates([], 'idle');
    }
  },
);

watch(
  () => props.routeCoordinates,
  (coordinates) => {
    if (JSON.stringify(coordinates) !== JSON.stringify(editableRouteCoordinates.value)) {
      editableRouteCoordinates.value = [...coordinates];
      updateRouteLayer();
      syncRouteEditMarkers();
    }
  },
  { deep: true },
);

watch(baseWaterwaysVisible, (visible) => {
  setLayersVisibility(baseWaterwayLayerIds.value, visible);
  scheduleMapSnapshot();
});
watch(overpassVisible, (visible) => {
  setLayersVisibility([OVERPASS_LAYER_ID], visible);
  scheduleMapSnapshot();
});
watch(anaVisible, (visible) => {
  setLayersVisibility([ANA_LAYER_ID], visible);
  scheduleMapSnapshot();
});

onMounted(() => {
  void initializeMap();
  window.addEventListener('resize', handleWindowResize);
});

onBeforeUnmount(() => {
  routeRequestId += 1;
  mapSnapshotRequestId += 1;
  if (mapSnapshotTimer !== null) {
    window.clearTimeout(mapSnapshotTimer);
  }
  window.removeEventListener('resize', handleWindowResize);
  startMarkerInstance?.remove();
  endMarkerInstance?.remove();
  clearRouteEditMarkers();
  mapInstance?.remove();
  startMarkerInstance = null;
  endMarkerInstance = null;
  mapInstance = null;
});

async function initializeMap() {
  if (!mapContainerRef.value) {
    return;
  }

  isLoading.value = true;
  loadError.value = '';

  try {
    const initialCenter = getInitialCenter();
    const initialZoom = hasAnySelection.value ? FOCUSED_ZOOM : DEFAULT_ZOOM;

    const newMapInstance = new maplibregl.Map({
      container: mapContainerRef.value,
      style: OPEN_FREE_MAP_STYLE,
      center: initialCenter,
      zoom: initialZoom,
      minZoom: 4,
      maxZoom: MAP_MAX_ZOOM,
      canvasContextAttributes: { preserveDrawingBuffer: true },
    });
    mapInstance = newMapInstance;

    newMapInstance.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'top-right');
    newMapInstance.on('click', handleMapClick);
    newMapInstance.on('moveend', handleMapMoveEnd);
    newMapInstance.once('style.load', () => {
      baseWaterwayLayerIds.value = getBaseWaterwayLayerIds(newMapInstance);
      addSatelliteBaseLayer(newMapInstance);
      addRouteLayer(newMapInstance);
      applyBaseMapMode(newMapInstance);
      syncMarkersAndViewport(props.startLatitude, props.startLongitude, props.endLatitude, props.endLongitude);
      isLoading.value = false;
      void loadDiagnosticSources(newMapInstance);
      scheduleMapSnapshot();
    });
    newMapInstance.on('error', (event) => {
      console.error('MapLibre location picker error:', event.error);
      if (isLoading.value) {
        loadError.value = 'Nao foi possivel carregar o mapa. Verifique sua conexao e tente novamente.';
        isLoading.value = false;
      }
    });

    requestAnimationFrame(() => {
      mapInstance?.resize();
    });
  } catch (error) {
    loadError.value = error instanceof Error
      ? `${error.message} Verifique sua conexao para usar o seletor de mapa.`
      : 'Nao foi possivel abrir o mapa.';
  }
}

function handleMapClick(event: maplibregl.MapMouseEvent) {
  const latitude = roundCoordinate(event.lngLat.lat);
  const longitude = roundCoordinate(event.lngLat.lng);

  if (routeKind.value === 'automatic' && clickedRouteLine(event)) {
    setRouteCoordinates(
      insertRouteCoordinate(editableRouteCoordinates.value, [longitude, latitude]),
      'manual',
    );
    activePoint.value = 'route';
    setBaseMapMode('satellite');
    routeMessage.value = 'Edicao manual ativada. Arraste os pontos ou clique no mapa para adicionar novos ajustes.';
    syncRouteEditMarkers();
    return;
  }

  if (activePoint.value === 'route' && hasRoute.value && routeKind.value === 'manual') {
    const coordinates = insertRouteCoordinate(editableRouteCoordinates.value, [longitude, latitude]);
    setRouteCoordinates(coordinates, 'manual');
    routeMessage.value = 'Percurso ajustado manualmente. Arraste os pontos para acompanhar o leito do rio.';
    return;
  }

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

function clickedRouteLine(event: maplibregl.MapMouseEvent) {
  if (!event.target.getLayer(ROUTE_LAYER_ID)) {
    return false;
  }

  return event.target.queryRenderedFeatures(event.point, {
    layers: [ROUTE_LAYER_ID],
  }).length > 0;
}

async function searchForLocation() {
  const query = locationQuery.value.trim();

  if (query.length < 3) {
    locationResults.value = [];
    locationSearchError.value = 'Digite pelo menos 3 caracteres para pesquisar.';
    return;
  }

  locationSearchLoading.value = true;
  locationSearchError.value = '';
  locationResults.value = [];

  try {
    locationResults.value = await searchLocations(query);

    if (locationResults.value.length === 0) {
      locationSearchError.value = 'Nenhum local encontrado. Tente incluir a cidade ou o estado.';
    }
  } catch {
    locationSearchError.value = 'Nao foi possivel pesquisar locais agora.';
  } finally {
    locationSearchLoading.value = false;
  }
}

function focusLocation(place: LocationSearchResult) {
  if (!mapInstance) {
    return;
  }

  locationQuery.value = place.label;
  locationResults.value = [];
  locationSearchError.value = '';

  if (place.bounds) {
    mapInstance.fitBounds([
      [place.bounds.west, place.bounds.south],
      [place.bounds.east, place.bounds.north],
    ], {
      padding: 48,
      maxZoom: FOCUSED_ZOOM,
    });
    return;
  }

  mapInstance.flyTo({
    center: [place.longitude, place.latitude],
    zoom: FOCUSED_ZOOM,
  });
}

function syncMarkersAndViewport(
  startLatitude: number | null,
  startLongitude: number | null,
  endLatitude: number | null,
  endLongitude: number | null,
) {
  startMarkerInstance = syncMarker(startMarkerInstance, startLatitude, startLongitude, START_MARKER_COLOR, 'start');
  endMarkerInstance = syncMarker(endMarkerInstance, endLatitude, endLongitude, END_MARKER_COLOR, 'end');
  syncViewport(startLatitude, startLongitude, endLatitude, endLongitude);
}

function syncMarker(
  markerInstance: maplibregl.Marker | null,
  latitude: number | null,
  longitude: number | null,
  color: string,
  point: 'start' | 'end',
) {
  if (!mapInstance) {
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
    const marker = new maplibregl.Marker({
      color,
      scale: 1.15,
      draggable: true,
    }).setLngLat(lngLat).addTo(mapInstance);

    const pointLabel = point === 'start' ? 'entrada' : 'saida';
    marker.getElement().title = `Arraste para ajustar a ${pointLabel}`;
    marker.getElement().setAttribute('aria-label', `Arraste para ajustar a ${pointLabel}`);
    marker.on('dragstart', cancelPendingRouteRequest);
    marker.on('dragend', () => updateDraggedEndpoint(point, marker));

    return marker;
  }

  markerInstance.setLngLat(lngLat);
  return markerInstance;
}

function cancelPendingRouteRequest() {
  routeRequestId += 1;
  routeLoading.value = false;
}

function updateDraggedEndpoint(point: 'start' | 'end', marker: maplibregl.Marker) {
  const lngLat = marker.getLngLat();
  const longitude = roundCoordinate(lngLat.lng);
  const latitude = roundCoordinate(lngLat.lat);

  if (editableRouteCoordinates.value.length >= 2 && routeKind.value !== 'idle') {
    const nextCoordinates = [...editableRouteCoordinates.value];
    const endpointIndex = point === 'start' ? 0 : nextCoordinates.length - 1;
    nextCoordinates[endpointIndex] = [longitude, latitude];
    setRouteCoordinates(nextCoordinates, routeKind.value);
  }

  if (point === 'start') {
    emit('update:startLatitude', latitude);
    emit('update:startLongitude', longitude);
  } else {
    emit('update:endLatitude', latitude);
    emit('update:endLongitude', longitude);
  }

  if (routeKind.value !== 'idle') {
    routeMessage.value = `${point === 'start' ? 'Entrada' : 'Saida'} ajustada. O percurso foi atualizado sem alterar o ponto escolhido.`;
  }
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
      maxZoom: Math.max(mapInstance.getZoom(), FOCUSED_ZOOM),
    });
    return;
  }

  if (hasStart) {
    const safeStartLatitude = startLatitude as number;
    const safeStartLongitude = startLongitude as number;
    mapInstance.flyTo({
      center: [safeStartLongitude, safeStartLatitude],
      zoom: Math.max(mapInstance.getZoom(), FOCUSED_ZOOM),
    });
    return;
  }

  if (hasEnd) {
    const safeEndLatitude = endLatitude as number;
    const safeEndLongitude = endLongitude as number;
    mapInstance.flyTo({
      center: [safeEndLongitude, safeEndLatitude],
      zoom: Math.max(mapInstance.getZoom(), FOCUSED_ZOOM),
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
    coordinateDraft.startLatitude = '';
    coordinateDraft.startLongitude = '';
    coordinateErrors.start = '';
    emit('update:startLatitude', null);
    emit('update:startLongitude', null);
    syncMarkersAndViewport(null, null, props.endLatitude, props.endLongitude);
    return;
  }

  coordinateDraft.endLatitude = '';
  coordinateDraft.endLongitude = '';
  coordinateErrors.end = '';
  emit('update:endLatitude', null);
  emit('update:endLongitude', null);
  syncMarkersAndViewport(props.startLatitude, props.startLongitude, null, null);
}

function clearAllSelections() {
  activePoint.value = 'start';
  coordinateDraft.startLatitude = '';
  coordinateDraft.startLongitude = '';
  coordinateDraft.endLatitude = '';
  coordinateDraft.endLongitude = '';
  coordinateErrors.start = '';
  coordinateErrors.end = '';
  emit('update:startLatitude', null);
  emit('update:startLongitude', null);
  emit('update:endLatitude', null);
  emit('update:endLongitude', null);
  syncMarkersAndViewport(null, null, null, null);
}

function setActivePoint(point: SelectionPoint) {
  if (point === 'route' && routeKind.value !== 'manual') {
    return;
  }

  activePoint.value = point;

  if (point === 'route') {
    setBaseMapMode('satellite');
  }

  syncRouteEditMarkers();
}

function applyTypedCoordinates(point: 'start' | 'end') {
  const latitudeValue = point === 'start'
    ? coordinateDraft.startLatitude
    : coordinateDraft.endLatitude;
  const longitudeValue = point === 'start'
    ? coordinateDraft.startLongitude
    : coordinateDraft.endLongitude;
  const latitude = parseCoordinateInput(latitudeValue, -90, 90);
  const longitude = parseCoordinateInput(longitudeValue, -180, 180);

  if (latitude === null || longitude === null) {
    coordinateErrors[point] = 'Informe latitude e longitude validas para marcar o ponto.';
    return;
  }

  coordinateErrors[point] = '';
  cancelPendingRouteRequest();

  if (point === 'start') {
    coordinateDraft.startLatitude = formatCoordinate(latitude);
    coordinateDraft.startLongitude = formatCoordinate(longitude);
    emit('update:startLatitude', latitude);
    emit('update:startLongitude', longitude);
    syncMarkersAndViewport(latitude, longitude, props.endLatitude, props.endLongitude);

    if (!hasEndSelection.value) {
      activePoint.value = 'end';
    }
  } else {
    coordinateDraft.endLatitude = formatCoordinate(latitude);
    coordinateDraft.endLongitude = formatCoordinate(longitude);
    emit('update:endLatitude', latitude);
    emit('update:endLongitude', longitude);
    syncMarkersAndViewport(props.startLatitude, props.startLongitude, latitude, longitude);
    activePoint.value = 'end';
  }
}

function parseCoordinateInput(value: string, minimum: number, maximum: number) {
  const normalizedValue = value.trim().replace(',', '.');

  if (normalizedValue === '') {
    return null;
  }

  const coordinate = Number(normalizedValue);

  if (!Number.isFinite(coordinate) || coordinate < minimum || coordinate > maximum) {
    return null;
  }

  return roundCoordinate(coordinate);
}

function startManualRoute() {
  if (
    !isCoordinatePair(props.startLatitude, props.startLongitude)
    || !isCoordinatePair(props.endLatitude, props.endLongitude)
  ) {
    return;
  }

  routeRequestId += 1;
  routeLoading.value = false;
  const start: RiverCoordinate = [props.startLongitude as number, props.startLatitude as number];
  const end: RiverCoordinate = [props.endLongitude as number, props.endLatitude as number];
  const routeToEdit = editableRouteCoordinates.value.length >= 2
    ? editableRouteCoordinates.value
    : [start, end];

  setRouteCoordinates(routeToEdit, 'manual');
  activePoint.value = 'route';
  setBaseMapMode('satellite');
  routeMessage.value = 'Edicao manual ativada. Arraste os pontos existentes ou clique no mapa para adicionar novos ajustes.';
  syncRouteEditMarkers();
}

function setBaseMapMode(mode: BaseMapMode) {
  baseMapMode.value = mode;

  if (mapInstance) {
    applyBaseMapMode(mapInstance);
    scheduleMapSnapshot();
  }
}

function addSatelliteBaseLayer(map: Map) {
  map.addSource(ESRI_IMAGERY_SOURCE_ID, {
    type: 'raster',
    tiles: [ESRI_IMAGERY_TILE_URL],
    tileSize: 256,
    minzoom: 0,
    maxzoom: ESRI_IMAGERY_NATIVE_MAX_ZOOM,
    attribution: ESRI_IMAGERY_ATTRIBUTION,
  });

  const firstLabelLayerId = map.getStyle().layers?.find((layer) => layer.type === 'symbol')?.id;

  map.addLayer({
    id: ESRI_IMAGERY_LAYER_ID,
    type: 'raster',
    source: ESRI_IMAGERY_SOURCE_ID,
    layout: { visibility: 'none' },
    paint: {
      'raster-fade-duration': 180,
      'raster-resampling': 'linear',
    },
  }, firstLabelLayerId);

  // Keep hydrography visible over imagery while retaining the base map labels.
  baseWaterwayLayerIds.value.forEach((layerId) => map.moveLayer(layerId));
}

function applyBaseMapMode(map: Map) {
  if (!map.getLayer(ESRI_IMAGERY_LAYER_ID)) {
    return;
  }

  map.setLayoutProperty(
    ESRI_IMAGERY_LAYER_ID,
    'visibility',
    baseMapMode.value === 'satellite' ? 'visible' : 'none',
  );
}

function addRouteLayer(map: Map) {
  map.addSource(ROUTE_SOURCE_ID, {
    type: 'geojson',
    data: routeFeatureCollection(editableRouteCoordinates.value),
  });
  map.addLayer({
    id: ROUTE_LAYER_ID,
    type: 'line',
    source: ROUTE_SOURCE_ID,
    layout: {
      'line-cap': 'round',
      'line-join': 'round',
    },
    paint: {
      'line-color': '#fff4bf',
      'line-width': ['interpolate', ['linear'], ['zoom'], 10, 3, 16, 6],
      'line-opacity': 0.95,
    },
  });
}

async function generateSuggestedRoute() {
  if (
    !isCoordinatePair(props.startLatitude, props.startLongitude)
    || !isCoordinatePair(props.endLatitude, props.endLongitude)
  ) {
    return;
  }

  const currentRequestId = ++routeRequestId;
  const start: RiverCoordinate = [props.startLongitude as number, props.startLatitude as number];
  const end: RiverCoordinate = [props.endLongitude as number, props.endLatitude as number];
  const bounds = routeBounds(start, end);
  routeLoading.value = true;
  setRouteCoordinates([], 'idle');
  routeMessage.value = 'Procurando um percurso conectado na hidrografia do OSM...';

  const [overpassResult] = await Promise.allSettled([
    fetchOverpassWaterways(bounds),
  ]);

  if (currentRequestId !== routeRequestId) {
    return;
  }

  const overpassData = overpassResult.status === 'fulfilled' ? overpassResult.value : null;

  if (overpassData) {
    diagnostics.overpass = { loading: false, count: overpassData.features.length, error: '' };
    upsertDiagnosticLayer('overpass', OVERPASS_SOURCE_ID, OVERPASS_LAYER_ID, '#3be4db', overpassData);
  } else {
    diagnostics.overpass.loading = false;
    diagnostics.overpass.error = getErrorMessage(
      overpassResult.status === 'rejected' ? overpassResult.reason : null,
    );
  }

  const suggestedRoute = findHydrographyRoute(start, end, overpassData, null);

  if (suggestedRoute) {
    setRouteCoordinates(withSelectedEndpoints(suggestedRoute.coordinates, start, end), 'automatic');
    activePoint.value = 'end';
    syncRouteEditMarkers();
    const furthestSnapMeters = Math.round(Math.max(
      suggestedRoute.startSnapDistanceKm,
      suggestedRoute.endSnapDistanceKm,
    ) * 1000);
    routeMessage.value = `Percurso calculado pelo leito mapeado em ${suggestedRoute.source}. Entrada e saida preservadas; conexao com a hidrografia em ate ${furthestSnapMeters} m.`;
  } else {
    setRouteCoordinates([start, end], 'manual');
    routeMessage.value = 'Nao encontrei um percurso conectado no OSM. A linha reta mostra apenas a distancia direta; adicione pontos nas curvas do rio para medir o percurso.';
    activePoint.value = 'route';
    setBaseMapMode('satellite');
    syncRouteEditMarkers();
  }

  routeLoading.value = false;
}

function withSelectedEndpoints(
  routeCoordinates: RiverCoordinate[],
  start: RiverCoordinate,
  end: RiverCoordinate,
) {
  const coordinates = [...routeCoordinates];

  if (!coordinates[0] || !coordinatesMatch(coordinates[0], start)) {
    coordinates.unshift(start);
  } else {
    coordinates[0] = start;
  }

  const lastIndex = coordinates.length - 1;
  if (!coordinates[lastIndex] || !coordinatesMatch(coordinates[lastIndex]!, end)) {
    coordinates.push(end);
  } else {
    coordinates[lastIndex] = end;
  }

  return coordinates;
}

function setRouteCoordinates(coordinates: RiverCoordinate[], kind: RouteKind) {
  editableRouteCoordinates.value = coordinates.map(([longitude, latitude]) => [
    roundCoordinate(longitude),
    roundCoordinate(latitude),
  ]);
  routeKind.value = kind;
  emit('update:routeCoordinates', editableRouteCoordinates.value);
  updateRouteLayer();
  syncRouteEditMarkers();
  scheduleMapSnapshot();
}

function handleMapMoveEnd() {
  syncRouteEditMarkers();
  scheduleMapSnapshot();
}

function scheduleMapSnapshot() {
  const requestId = ++mapSnapshotRequestId;

  if (mapSnapshotTimer !== null) {
    window.clearTimeout(mapSnapshotTimer);
    mapSnapshotTimer = null;
  }

  if (
    !mapInstance
    || editableRouteCoordinates.value.length < 2
    || !isCoordinatePair(props.startLatitude, props.startLongitude)
    || !isCoordinatePair(props.endLatitude, props.endLongitude)
  ) {
    emit('mapSnapshot', null);
    return;
  }

  mapSnapshotTimer = window.setTimeout(() => {
    mapSnapshotTimer = null;
    const map = mapInstance;

    if (!map) {
      return;
    }

    const capture = () => {
      if (requestId !== mapSnapshotRequestId) {
        return;
      }

      void captureMapSnapshot(map).then((snapshot) => {
        if (requestId === mapSnapshotRequestId) {
          emit('mapSnapshot', snapshot);
        }
      });
    };

    if (map.loaded() && map.areTilesLoaded()) {
      requestAnimationFrame(() => requestAnimationFrame(capture));
    } else {
      map.once('idle', capture);
    }
  }, 300);
}

function captureMapSnapshot(map: Map) {
  return new Promise<File | null>((resolve) => {
    try {
      const mapCanvas = map.getCanvas();
      const snapshotCanvas = document.createElement('canvas');
      snapshotCanvas.width = mapCanvas.width;
      snapshotCanvas.height = mapCanvas.height;
      const context = snapshotCanvas.getContext('2d');

      if (!context || mapCanvas.clientWidth === 0 || mapCanvas.clientHeight === 0) {
        resolve(null);
        return;
      }

      context.drawImage(mapCanvas, 0, 0);
      const scaleX = snapshotCanvas.width / mapCanvas.clientWidth;
      const scaleY = snapshotCanvas.height / mapCanvas.clientHeight;
      context.save();
      context.scale(scaleX, scaleY);
      drawSnapshotMarker(
        context,
        map,
        props.startLatitude as number,
        props.startLongitude as number,
        START_MARKER_COLOR,
      );
      drawSnapshotMarker(
        context,
        map,
        props.endLatitude as number,
        props.endLongitude as number,
        END_MARKER_COLOR,
      );
      drawSnapshotAttribution(context, mapCanvas.clientWidth, mapCanvas.clientHeight);
      context.restore();

      snapshotCanvas.toBlob((blob) => {
        resolve(blob ? new File([blob], 'mapa-do-rio.jpg', { type: 'image/jpeg' }) : null);
      }, 'image/jpeg', 0.9);
    } catch {
      resolve(null);
    }
  });
}

function drawSnapshotMarker(
  context: CanvasRenderingContext2D,
  map: Map,
  latitude: number,
  longitude: number,
  color: string,
) {
  const point = map.project([longitude, latitude]);

  context.save();
  context.translate(point.x, point.y);
  context.beginPath();
  context.moveTo(0, 0);
  context.bezierCurveTo(-2, -5, -10, -10, -10, -18);
  context.arc(0, -18, 10, Math.PI, 0);
  context.bezierCurveTo(10, -10, 2, -5, 0, 0);
  context.closePath();
  context.fillStyle = color;
  context.strokeStyle = '#ffffff';
  context.lineWidth = 2;
  context.fill();
  context.stroke();
  context.beginPath();
  context.arc(0, -18, 3.5, 0, Math.PI * 2);
  context.fillStyle = '#ffffff';
  context.fill();
  context.restore();
}

function drawSnapshotAttribution(
  context: CanvasRenderingContext2D,
  width: number,
  height: number,
) {
  const attribution = baseMapMode.value === 'satellite'
    ? ESRI_IMAGERY_ATTRIBUTION
    : 'OpenFreeMap | OpenMapTiles | OpenStreetMap contributors';
  context.font = '9px sans-serif';
  const labelWidth = Math.min(width, context.measureText(attribution).width + 12);
  context.fillStyle = 'rgba(3, 20, 30, 0.72)';
  context.fillRect(0, height - 17, labelWidth, 17);
  context.fillStyle = 'rgba(255, 255, 255, 0.82)';
  context.fillText(attribution, 6, height - 5);
}

function updateRouteLayer() {
  const source = mapInstance?.getSource(ROUTE_SOURCE_ID) as GeoJSONSource | undefined;
  source?.setData(routeFeatureCollection(editableRouteCoordinates.value));
}

function routeFeatureCollection(coordinates: RiverCoordinate[]): GeoJsonFeatureCollection {
  return {
    type: 'FeatureCollection',
    features: coordinates.length >= 2
      ? [{
          type: 'Feature',
          properties: {},
          geometry: { type: 'LineString', coordinates },
        }]
      : [],
  };
}

function syncRouteEditMarkers() {
  clearRouteEditMarkers();

  if (
    !mapInstance
    || activePoint.value !== 'route'
    || routeKind.value !== 'manual'
    || editableRouteCoordinates.value.length < 3
  ) {
    return;
  }

  // Limit only the draggable controls; retain every coordinate in the measured line.
  const bounds = mapInstance.getBounds();
  const visibleCoordinates = editableRouteCoordinates.value
    .map((coordinate, coordinateIndex) => ({ coordinate, coordinateIndex }))
    .slice(1, -1)
    .filter(({ coordinate }) => bounds.contains(coordinate));
  const handleStep = Math.max(1, Math.ceil(visibleCoordinates.length / MAX_VISIBLE_ROUTE_HANDLES));

  visibleCoordinates.forEach(({ coordinate, coordinateIndex }, visibleIndex) => {
    if (visibleIndex % handleStep !== 0) {
      return;
    }

    const element = document.createElement('button');
    element.type = 'button';
    element.className = 'route-edit-handle';
    element.title = 'Arraste para ajustar o percurso';
    element.addEventListener('click', (event) => event.stopPropagation());

    const marker = new maplibregl.Marker({ element, draggable: true })
      .setLngLat(coordinate)
      .addTo(mapInstance!);

    marker.on('dragend', () => {
      const lngLat = marker.getLngLat();
      const nextCoordinates = [...editableRouteCoordinates.value];
      nextCoordinates[coordinateIndex] = [lngLat.lng, lngLat.lat];
      setRouteCoordinates(nextCoordinates, 'manual');
      routeMessage.value = 'Percurso ajustado manualmente. A extensao foi recalculada pela linha.';
    });
    routeEditMarkers.push(marker);
  });
}

function clearRouteEditMarkers() {
  routeEditMarkers.forEach((marker) => marker.remove());
  routeEditMarkers = [];
}

function routeBounds(start: RiverCoordinate, end: RiverCoordinate) {
  const longitudeSpan = Math.abs(start[0] - end[0]);
  const latitudeSpan = Math.abs(start[1] - end[1]);
  const longitudePadding = Math.max(0.015, longitudeSpan * 0.25);
  const latitudePadding = Math.max(0.015, latitudeSpan * 0.25);

  return {
    west: Math.max(-180, Math.min(start[0], end[0]) - longitudePadding),
    south: Math.max(-90, Math.min(start[1], end[1]) - latitudePadding),
    east: Math.min(180, Math.max(start[0], end[0]) + longitudePadding),
    north: Math.min(90, Math.max(start[1], end[1]) + latitudePadding),
  };
}

function getErrorMessage(error: unknown) {
  return error instanceof Error ? error.message : 'Falha ao consultar a fonte.';
}

function routeMatchesEndpointSelection(
  startLatitude: number,
  startLongitude: number,
  endLatitude: number,
  endLongitude: number,
) {
  if (routeKind.value === 'idle' || editableRouteCoordinates.value.length < 2) {
    return false;
  }

  const first = editableRouteCoordinates.value[0]!;
  const last = editableRouteCoordinates.value[editableRouteCoordinates.value.length - 1]!;

  return coordinatesMatch(first, [startLongitude, startLatitude])
    && coordinatesMatch(last, [endLongitude, endLatitude]);
}

function coordinatesMatch(left: RiverCoordinate, right: RiverCoordinate) {
  return Math.abs(left[0] - right[0]) < 0.000001
    && Math.abs(left[1] - right[1]) < 0.000001;
}

function getBaseWaterwayLayerIds(map: Map) {
  return (map.getStyle().layers ?? [])
    .filter((layer) => WATERWAY_PATTERN.test(getLayerIdentity(layer)))
    .map((layer) => layer.id);
}

function getLayerIdentity(layer: LayerSpecification) {
  return `${layer.id} ${'source-layer' in layer ? layer['source-layer'] ?? '' : ''}`;
}

async function loadDiagnosticSources(map: Map) {
  await Promise.all([
    loadDiagnosticSource('overpass', OVERPASS_SOURCE_ID, OVERPASS_LAYER_ID, '#3be4db', fetchOverpassWaterways, map),
    loadDiagnosticSource('ana', ANA_SOURCE_ID, ANA_LAYER_ID, '#f0b35f', fetchAnaHydrography, map),
  ]);
}

async function loadDiagnosticSource(
  key: 'overpass' | 'ana',
  sourceId: string,
  layerId: string,
  color: string,
  dataLoader: (bounds: typeof TEST_BOUNDS) => Promise<GeoJsonFeatureCollection>,
  map: Map,
) {
  try {
    const data = await dataLoader(TEST_BOUNDS);
    diagnostics[key].count = data.features.length;
    upsertDiagnosticLayer(key, sourceId, layerId, color, data, map);
  } catch (error) {
    diagnostics[key].error = error instanceof Error ? error.message : 'Falha desconhecida na requisicao.';
    console.error(`${key} hydrography diagnostic error:`, error);
  } finally {
    diagnostics[key].loading = false;
  }
}

function upsertDiagnosticLayer(
  key: 'overpass' | 'ana',
  sourceId: string,
  layerId: string,
  color: string,
  data: GeoJsonFeatureCollection,
  map = mapInstance,
) {
  if (!map) {
    return;
  }

  const existingSource = map.getSource(sourceId) as GeoJSONSource | undefined;

  if (existingSource) {
    existingSource.setData(data);
  } else {
    map.addSource(sourceId, { type: 'geojson', data });
  }

  if (!map.getLayer(layerId)) {
    map.addLayer({
      id: layerId,
      type: 'line',
      source: sourceId,
      paint: {
        'line-color': color,
        'line-width': key === 'ana' ? 2.4 : 2,
        'line-opacity': 0.9,
      },
    });
  }

  setLayersVisibility([layerId], key === 'ana' ? anaVisible.value : overpassVisible.value);

  if (map.getLayer(ROUTE_LAYER_ID)) {
    map.moveLayer(ROUTE_LAYER_ID);
  }
}

function setLayersVisibility(layerIds: string[], visible: boolean) {
  if (!mapInstance) {
    return;
  }

  layerIds.forEach((layerId) => {
    if (mapInstance?.getLayer(layerId)) {
      mapInstance.setLayoutProperty(layerId, 'visibility', visible ? 'visible' : 'none');
    }
  });
}

function diagnosticLabel(state: DiagnosticState) {
  if (state.loading) {
    return 'Consultando...';
  }

  if (state.error) {
    return state.error;
  }

  return `${state.count ?? 0} trechos recebidos`;
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

function coordinateInputValue(value: number | null) {
  return value === null ? '' : formatCoordinate(value);
}
</script>

<style scoped>
.location-picker {
  display: flex;
  flex-direction: column;
  gap: 12px;
  border-radius: 16px;
  background: rgba(3, 20, 30, 0.52);
  font-family: var(--font-family-base);
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
  font-size: var(--river-form-control-size, 13px);
  cursor: pointer;
}

.selection-mode {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.mode-chip {
  min-width: 176px;
}

.mode-chip__icon,
.selection-card__icon,
.hydrography-toggle__icon {
  display: inline-flex;
  flex: 0 0 auto;
}

.mode-chip__icon {
  width: 18px;
  height: 18px;
}

.mode-chip__icon svg,
.selection-card__icon svg,
.hydrography-toggle__icon svg {
  width: 100%;
  height: 100%;
  display: block;
}

.location-search {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.location-search__controls {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 94px;
  align-items: end;
  gap: 10px;
}

.location-search__button {
  border-color: rgba(54, 201, 193, 0.35);
  background: rgba(22, 87, 89, 0.35);
  color: var(--color-text-primary);
}

.location-search__message {
  color: var(--color-text-secondary);
  font-size: var(--river-form-helper-size, 11px);
}

.location-search__message--error {
  color: #ffd4d4;
}

.location-search__results {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid var(--color-border-subtle);
  border-radius: 10px;
  list-style: none;
}

.location-search__results li + li {
  border-top: 1px solid var(--color-border-subtle);
}

.location-search__results button {
  width: 100%;
  padding: 10px 12px;
  border: 0;
  background: rgba(7, 23, 33, 0.82);
  color: var(--color-text-primary);
  font: inherit;
  font-size: var(--river-form-control-size, 13px);
  line-height: 1.4;
  text-align: left;
  cursor: pointer;
}

.location-search__results button:hover,
.location-search__results button:focus-visible {
  background: rgba(22, 87, 89, 0.45);
}

.location-search__attribution {
  align-self: flex-start;
  color: var(--color-text-muted);
  font-size: var(--river-form-helper-size, 11px);
}

.route-message {
  margin: -4px 0 0;
  padding: 9px 11px;
  border-left: 3px solid var(--paddle-water-primary);
  border-radius: 6px;
  background: rgba(54, 201, 193, 0.08);
  color: var(--color-text-secondary);
  font-size: var(--river-form-label-size, 12px);
  line-height: 1.4;
}

.route-message--warning {
  border-left-color: #f0b35f;
  background: rgba(240, 179, 95, 0.08);
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

.map-surface :deep(.route-edit-handle) {
  width: 14px;
  height: 14px;
  padding: 0;
  border: 2px solid #fff4bf;
  border-radius: 50%;
  background: #0b5960;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.55);
  cursor: grab;
}

.map-surface :deep(.route-edit-handle:active) {
  cursor: grabbing;
}

.map-surface :deep(.maplibregl-ctrl-attrib) {
  background: rgba(3, 20, 30, 0.74);
  color: rgba(240, 248, 255, 0.72);
  font-size: 10px;
}

.map-surface :deep(.maplibregl-ctrl-attrib a) {
  color: inherit;
}

.map-surface :deep(.maplibregl-ctrl-attrib-button) {
  filter: invert(1);
  opacity: 0.72;
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

.base-map-switch {
  position: absolute;
  right: 12px;
  bottom: 12px;
  z-index: 2;
  display: flex;
  gap: 2px;
  padding: 3px;
  border: 1px solid rgba(255, 255, 255, 0.16);
  border-radius: 10px;
  background: rgba(3, 20, 30, 0.92);
  box-shadow: 0 8px 22px rgba(0, 0, 0, 0.24);
}

.base-map-switch__button {
  border-radius: 7px;
  background: transparent;
  color: var(--color-text-muted);
}

.base-map-switch__button--active {
  background: rgba(54, 201, 193, 0.22);
  color: var(--color-text-primary);
}

.hydrography-drawer {
  position: absolute;
  top: 12px;
  left: 0;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 6px;
}

.hydrography-toggle {
  border-color: rgba(255, 255, 255, 0.14);
  border-left: 0;
  border-radius: 0 10px 10px 0;
  background: rgba(3, 20, 30, 0.92);
  color: var(--color-text-primary);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.22);
}

.hydrography-toggle__icon {
  width: 18px;
  height: 18px;
}

.hydrography-controls {
  display: flex;
  flex-direction: column;
  gap: 7px;
  width: min(260px, calc(100vw - 48px));
  margin-left: 12px;
  padding: 10px 12px;
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 12px;
  background: rgba(3, 20, 30, 0.92);
  color: var(--color-text-primary);
  font-size: var(--river-form-label-size, 12px);
  box-shadow: 0 10px 26px rgba(0, 0, 0, 0.2);
}

.hydrography-controls label {
  display: grid;
  grid-template-columns: auto 1fr;
  column-gap: 7px;
  cursor: pointer;
}

.hydrography-controls input {
  margin-top: 3px;
  accent-color: var(--color-accent-primary);
}

.hydrography-controls small {
  grid-column: 2;
  margin-top: 1px;
  color: var(--color-text-muted);
  font-size: var(--river-form-helper-size, 11px);
}

.hydrography-controls .diagnostic-error {
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
  padding: 12px 14px;
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
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.selection-card__title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.selection-card__icon {
  width: 20px;
  height: 20px;
  color: var(--color-accent-strong);
}

.summary-action {
  min-height: 32px;
  padding: 0 10px;
}

.selection-label {
  color: rgba(240, 248, 255, 0.82);
  font-size: var(--river-form-label-size, 12px);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.coordinate-entry {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr)) auto;
  align-items: end;
  gap: 8px;
}

.coordinate-entry__apply {
  min-height: 36px;
  padding: 0 10px;
  border: 1px solid rgba(58, 212, 203, 0.34);
  border-radius: 9px;
  background: rgba(22, 87, 89, 0.35);
  color: var(--color-text-primary);
  font: inherit;
  font-size: var(--river-form-label-size, 12px);
  font-weight: 600;
  cursor: pointer;
}

.coordinate-entry__apply:hover {
  background: rgba(22, 112, 109, 0.52);
}

.coordinate-entry__error {
  color: #ffd4d4;
  font-size: var(--river-form-helper-size, 11px);
  line-height: 1.35;
}

@media (max-width: 720px) {
  .selection-summary-grid {
    grid-template-columns: 1fr;
  }

  .coordinate-entry {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .coordinate-entry__apply {
    grid-column: 1 / -1;
  }

  .location-search__controls {
    grid-template-columns: 1fr;
  }

  .map-shell,
  .map-surface {
    min-height: 280px;
    height: 280px;
  }
}
</style>
