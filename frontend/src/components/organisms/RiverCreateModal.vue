<template>
    <BaseModal :model-value="props.modelValue" :title="modalTitle"
        :description="modalDescription" max-width="860px"
        :close-on-backdrop="false" @update:modelValue="handleModalVisibilityChange">
        <form class="create-river-form" @submit.prevent="handleSubmit">
            <div class="create-river-grid">
                <div class="river-details-grid">
                    <BaseInput id="create-river-name" v-model="createForm.name" class="river-field--name"
                        label="Nome do rio" type="text" placeholder="Ex.: Rio do Peixe" variant="compact" required />

                    <BaseInput id="create-river-city" v-model="createForm.city" class="river-field--city"
                        label="Cidade" type="text" placeholder="Ex.: Socorro" variant="compact" required />

                    <div class="river-field river-field--state">
                        <label for="create-river-state">UF <span aria-hidden="true">*</span></label>
                        <div class="field-control field-control--select">
                            <select id="create-river-state" v-model="createForm.state">
                                <option value="" disabled>UF</option>
                                <option v-for="state in stateOptions" :key="state" :value="state">{{ state }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="river-field river-field--difficulty">
                        <label for="create-difficulty-class">Classe de dificuldade</label>
                        <div class="field-control field-control--select">
                        <select id="create-difficulty-class" v-model="createForm.difficultyClass">
                            <option value="">Nao informada</option>
                            <option v-for="option in props.difficultyOptions" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>
                        </div>
                    </div>

                    <BaseInput id="create-extension-km" v-model="createForm.extensionKm" class="river-field--extension"
                        label="Extensao do percurso (km)" type="number" placeholder="Ex.: 7" variant="compact"
                        suffix="km" min="0.1" max="10000" step="0.1" inputmode="decimal" required />

                    <BaseTextarea id="create-river-description" v-model="createForm.description"
                        class="river-field--description" label="Descricao" variant="compact" :rows="3"
                        placeholder="Contexto do trecho, observacoes iniciais ou tipo de remada." />

                    <BaseFileInput id="create-river-cover" v-model="coverImage" class="river-field--cover"
                        label="Imagem de capa" :fallback-value="mapCoverImage"
                        helper-text="Pre-visualizacao local; a imagem ainda nao sera salva."
                        fallback-helper-text="Captura do mapa usada quando nenhuma imagem for selecionada." />
                </div>

                <RiverLocationPicker
                    :start-latitude="createForm.startLatitude"
                    :start-longitude="createForm.startLongitude"
                    :end-latitude="createForm.endLatitude"
                    :end-longitude="createForm.endLongitude"
                    :route-coordinates="createForm.routeCoordinates"
                    @update:startLatitude="handleStartLatitudeSelection"
                    @update:startLongitude="handleStartLongitudeSelection"
                    @update:endLatitude="handleEndLatitudeSelection"
                    @update:endLongitude="handleEndLongitudeSelection"
                    @update:routeCoordinates="handleRouteCoordinatesSelection"
                    @mapSnapshot="handleMapSnapshot"
                />

            </div>

            <div v-if="combinedErrorMessage" class="modal-feedback modal-feedback--error">
                {{ combinedErrorMessage }}
            </div>
        </form>

        <template #footer>
            <BaseButton
                class="modal-action modal-action--primary"
                width="auto"
                min-height="44px"
                padding="0 18px"
                font-size="15px"
                font-weight="600"
                border-width="1px"
                background="linear-gradient(180deg, #138179 0%, #0f6964 100%)"
                text-color="var(--color-text-primary)"
                border-color="rgba(40, 167, 160, 0.42)"
                :disabled="props.creating"
                :label="submitButtonLabel"
                @click="handleSubmit"
            />
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import BaseButton from '@/components/atoms/BaseButton.vue';
import BaseFileInput from '@/components/atoms/BaseFileInput.vue';
import BaseInput from '@/components/atoms/BaseInput.vue';
import BaseModal from '@/components/atoms/BaseModal.vue';
import BaseTextarea from '@/components/atoms/BaseTextarea.vue';
import RiverLocationPicker from '@/components/organisms/RiverLocationPicker.vue';
import type { River, RiverCoordinate, RiverCreateFormValues } from '@/types/rivers';
import { computed, reactive, ref, watch } from 'vue';

interface RiverCreateModalProps {
    modelValue: boolean;
    creating: boolean;
    errorMessage: string;
    difficultyOptions: string[];
    river?: River | null;
}

type RiverCreateFieldValues = Omit<RiverCreateFormValues, 'coverImage'>;

const props = defineProps<RiverCreateModalProps>();
const stateOptions = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
    'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
    'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
];
const emit = defineEmits<{
    (event: 'update:modelValue', value: boolean): void;
    (event: 'submit', values: RiverCreateFormValues): void;
}>();

const createForm = reactive(createEmptyForm());
const coverImage = ref<File | null>(null);
const mapCoverImage = ref<File | null>(null);
const localErrorMessage = ref('');
const combinedErrorMessage = computed(() => localErrorMessage.value || props.errorMessage);
const isEditing = computed(() => props.river !== null && props.river !== undefined);
const modalTitle = computed(() => isEditing.value ? 'Editar rio' : 'Cadastrar rio');
const modalDescription = computed(() => isEditing.value
    ? 'Atualize as informacoes e ajuste a entrada, a saida ou o percurso no mapa.'
    : 'Informe a extensao conhecida do trecho e marque a entrada e a saida no mapa.');
const submitButtonLabel = computed(() => {
    if (props.creating) {
        return 'Salvando...';
    }

    return isEditing.value ? 'Salvar alteracoes' : 'Salvar rio';
});

watch(
    [() => props.modelValue, () => props.river] as const,
    ([isOpen, river]) => {
        if (isOpen && river) {
            Object.assign(createForm, createFormFromRiver(river));
            coverImage.value = null;
            mapCoverImage.value = null;
        } else if (!isOpen) {
            resetState();
        }
    },
    { immediate: true },
);

function createEmptyForm(): RiverCreateFieldValues {
    return {
        name: '',
        city: '',
        state: '',
        difficultyClass: '',
        description: '',
        extensionKm: '',
        startLatitude: null,
        startLongitude: null,
        endLatitude: null,
        endLongitude: null,
        routeCoordinates: [],
    };
}

function createFormFromRiver(river: River): RiverCreateFieldValues {
    return {
        name: river.name,
        city: river.city,
        state: river.state,
        difficultyClass: river.difficultyClass ?? '',
        description: river.description ?? '',
        extensionKm: String(river.extensionKm),
        startLatitude: river.startLatitude,
        startLongitude: river.startLongitude,
        endLatitude: river.endLatitude,
        endLongitude: river.endLongitude,
        routeCoordinates: [...river.routeCoordinates],
    };
}

function resetState() {
    Object.assign(createForm, createEmptyForm());
    coverImage.value = null;
    mapCoverImage.value = null;
    localErrorMessage.value = '';
}

function handleModalVisibilityChange(isOpen: boolean) {
    if (!isOpen) {
        resetState();
    }

    emit('update:modelValue', isOpen);
}

function handleStartLatitudeSelection(latitude: number | null) {
    createForm.startLatitude = latitude;
    localErrorMessage.value = '';
}

function handleStartLongitudeSelection(longitude: number | null) {
    createForm.startLongitude = longitude;
    localErrorMessage.value = '';
}

function handleEndLatitudeSelection(latitude: number | null) {
    createForm.endLatitude = latitude;
    localErrorMessage.value = '';
}

function handleEndLongitudeSelection(longitude: number | null) {
    createForm.endLongitude = longitude;
    localErrorMessage.value = '';
}

function handleRouteCoordinatesSelection(coordinates: RiverCoordinate[]) {
    createForm.routeCoordinates = coordinates;
    localErrorMessage.value = '';
}

function handleMapSnapshot(snapshot: File | null) {
    mapCoverImage.value = snapshot;
}

function handleSubmit() {
    if (props.creating) {
        return;
    }

    localErrorMessage.value = '';

    const extensionKm = Number(createForm.extensionKm);

    if (!Number.isFinite(extensionKm) || extensionKm <= 0 || extensionKm > 10000) {
        localErrorMessage.value = 'Informe uma extensao valida, maior que zero, em quilometros.';
        return;
    }

    if (createForm.startLatitude === null || createForm.startLongitude === null) {
        localErrorMessage.value = 'Selecione o ponto de entrada do rio no mapa antes de salvar.';
        return;
    }

    if (createForm.endLatitude === null || createForm.endLongitude === null) {
        localErrorMessage.value = 'Selecione o ponto de saida do rio no mapa antes de salvar.';
        return;
    }

    if (
        createForm.startLatitude === createForm.endLatitude
        && createForm.startLongitude === createForm.endLongitude
    ) {
        localErrorMessage.value = 'O ponto de saida precisa ser diferente do ponto de entrada.';
        return;
    }

    if (createForm.routeCoordinates.length < 2) {
        localErrorMessage.value = 'Aguarde o percurso ser calculado ou ajuste a linha manualmente.';
        return;
    }

    emit('submit', {
        name: createForm.name,
        city: createForm.city,
        state: createForm.state,
        difficultyClass: createForm.difficultyClass,
        description: createForm.description,
        extensionKm: createForm.extensionKm,
        startLatitude: createForm.startLatitude,
        startLongitude: createForm.startLongitude,
        endLatitude: createForm.endLatitude,
        endLongitude: createForm.endLongitude,
        routeCoordinates: createForm.routeCoordinates,
        coverImage: coverImage.value ?? mapCoverImage.value,
    });
}
</script>

<style scoped>
.create-river-form,
.create-river-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.create-river-form {
    --river-form-label-size: 12px;
    --river-form-control-size: 13px;
    --river-form-helper-size: 11px;
    font-family: var(--font-family-base);
}

.river-details-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(180px, 0.95fr) 88px;
    gap: 12px;
}

.river-field {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 6px;
}

.river-field--name,
.river-field--difficulty {
    grid-column: 1;
}

.river-field--city {
    grid-column: 2;
}

.river-field--state {
    grid-column: 3;
}

.river-field--extension {
    grid-column: 2 / 4;
}

.river-field--description,
.river-field--cover {
    grid-column: 1 / -1;
}

.river-field > label {
    color: rgba(240, 248, 255, 0.86);
    font-size: var(--river-form-label-size);
    font-weight: 600;
    line-height: 1.2;
}

.river-field > label span {
    color: var(--color-accent-primary);
}

.field-control {
    position: relative;
    min-width: 0;
}

.field-control select {
    width: 100%;
    min-width: 0;
    border: 1px solid rgba(127, 185, 215, 0.16);
    border-radius: 8px;
    background: rgba(1, 10, 18, 0.72);
    color: var(--color-text-primary);
    font: inherit;
    font-size: var(--river-form-control-size);
}

.field-control select {
    min-height: 40px;
    padding: 0 12px;
}

.field-control select:focus {
    border-color: rgba(58, 212, 203, 0.56);
    outline: none;
    box-shadow: 0 0 0 2px rgba(58, 212, 203, 0.1);
}

.field-control--select select {
    appearance: none;
    padding-right: 34px;
}

.field-control--select::after {
    content: '';
    position: absolute;
    top: 50%;
    right: 12px;
    width: 8px;
    height: 8px;
    border-right: 1.5px solid rgba(240, 248, 255, 0.6);
    border-bottom: 1.5px solid rgba(240, 248, 255, 0.6);
    transform: translateY(-70%) rotate(45deg);
    pointer-events: none;
}

.modal-feedback {
    margin-top: 4px;
    padding: 12px 14px;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.45;
}

.modal-feedback--error {
    border: 1px solid rgba(255, 115, 115, 0.3);
    background: rgba(113, 24, 24, 0.35);
    color: #ffd4d4;
}

.modal-action--primary:hover {
    background: var(--color-accent-primary);
    transform: translateY(-1px);
}

@media (max-width: 720px) {
    .river-details-grid {
        grid-template-columns: 1fr;
    }

    .river-field--name,
    .river-field--city,
    .river-field--state,
    .river-field--difficulty,
    .river-field--extension,
    .river-field--description,
    .river-field--cover {
        grid-column: 1;
    }
}
</style>
