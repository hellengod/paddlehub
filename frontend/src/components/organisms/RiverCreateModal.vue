<template>
    <BaseModal :model-value="props.modelValue" title="Cadastrar rio"
        description="Escolha o ponto inicial no mapa e complete os dados principais do rio." max-width="860px"
        @update:modelValue="handleModalVisibilityChange">
        <form class="create-river-form" @submit.prevent="handleSubmit">
            <div class="create-river-grid">
                <BaseInput v-model="createForm.name" label="Nome do rio" type="text" placeholder="Ex.: Rio do Peixe"
                    label-color="var(--color-text-primary)" background-color="rgba(1, 10, 18, 0.78)"
                    border-color="var(--color-border-subtle)" text-color="var(--color-text-primary)" />

                <div class="modal-row">
                    <BaseInput v-model="createForm.city" label="Cidade" type="text" placeholder="Ex.: Socorro"
                        label-color="var(--color-text-primary)" background-color="rgba(1, 10, 18, 0.78)"
                        border-color="var(--color-border-subtle)" text-color="var(--color-text-primary)" />

                    <BaseInput v-model="createForm.state" label="UF" type="text" placeholder="SP"
                        label-color="var(--color-text-primary)" background-color="rgba(1, 10, 18, 0.78)"
                        border-color="var(--color-border-subtle)" text-color="var(--color-text-primary)" />
                </div>

                <div class="modal-field">
                    <label for="create-difficulty-class">Classe de dificuldade</label>
                    <div class="select-shell">
                        <select id="create-difficulty-class" v-model="createForm.difficultyClass">
                            <option value="">Nao informada</option>
                            <option v-for="option in props.difficultyOptions" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>
                    </div>
                </div>

                <BaseTextarea v-model="createForm.description" label="Descricao"
                    placeholder="Contexto do trecho, observacoes iniciais ou tipo de remada."
                    label-color="var(--color-text-primary)" background-color="rgba(1, 10, 18, 0.78)"
                    border-color="var(--color-border-subtle)" text-color="var(--color-text-primary)" :rows="4"
                    min-height="112px" />

                <RiverLocationPicker :latitude="createForm.startLatitude" :longitude="createForm.startLongitude"
                    @update:latitude="handleLatitudeSelection" @update:longitude="handleLongitudeSelection" />
            </div>

            <div v-if="combinedErrorMessage" class="modal-feedback modal-feedback--error">
                {{ combinedErrorMessage }}
            </div>
        </form>

        <template #footer>
            <BaseButton
                class="modal-action modal-action--ghost"
                width="auto"
                min-height="44px"
                padding="0 18px"
                font-size="15px"
                font-weight="600"
                border-width="1px"
                background="transparent"
                text-color="var(--color-text-primary)"
                border-color="var(--color-border-subtle)"
                label="Cancelar"
                @click="emit('update:modelValue', false)"
            />
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
                :label="props.creating ? 'Salvando...' : 'Salvar rio'"
                @click="handleSubmit"
            />
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import BaseButton from '@/components/atoms/BaseButton.vue';
import BaseInput from '@/components/atoms/BaseInput.vue';
import BaseModal from '@/components/atoms/BaseModal.vue';
import BaseTextarea from '@/components/atoms/BaseTextarea.vue';
import RiverLocationPicker from '@/components/organisms/RiverLocationPicker.vue';
import type { RiverCreateFormValues } from '@/types/rivers';
import { computed, reactive, ref, watch } from 'vue';

interface RiverCreateModalProps {
    modelValue: boolean;
    creating: boolean;
    errorMessage: string;
    difficultyOptions: string[];
}

const props = defineProps<RiverCreateModalProps>();
const emit = defineEmits<{
    (event: 'update:modelValue', value: boolean): void;
    (event: 'submit', values: RiverCreateFormValues): void;
}>();

const createForm = reactive(createEmptyForm());
const localErrorMessage = ref('');
const combinedErrorMessage = computed(() => localErrorMessage.value || props.errorMessage);

watch(
    () => props.modelValue,
    (isOpen) => {
        if (!isOpen) {
            resetState();
        }
    }
);

function createEmptyForm(): RiverCreateFormValues {
    return {
        name: '',
        city: '',
        state: '',
        difficultyClass: '',
        description: '',
        startLatitude: null,
        startLongitude: null,
    };
}

function resetState() {
    Object.assign(createForm, createEmptyForm());
    localErrorMessage.value = '';
}

function handleModalVisibilityChange(isOpen: boolean) {
    if (!isOpen) {
        resetState();
    }

    emit('update:modelValue', isOpen);
}

function handleLatitudeSelection(latitude: number | null) {
    createForm.startLatitude = latitude;
    localErrorMessage.value = '';
}

function handleLongitudeSelection(longitude: number | null) {
    createForm.startLongitude = longitude;
    localErrorMessage.value = '';
}

function handleSubmit() {
    localErrorMessage.value = '';

    if (createForm.startLatitude === null || createForm.startLongitude === null) {
        localErrorMessage.value = 'Selecione o ponto inicial do rio no mapa antes de salvar.';
        return;
    }

    emit('submit', {
        name: createForm.name,
        city: createForm.city,
        state: createForm.state,
        difficultyClass: createForm.difficultyClass,
        description: createForm.description,
        startLatitude: createForm.startLatitude,
        startLongitude: createForm.startLongitude,
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

.modal-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 120px;
    gap: 16px;
}

.modal-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.modal-field label {
    color: var(--color-text-primary);
    font-size: 15px;
    font-weight: 600;
}

.select-shell {
    position: relative;
}

.select-shell::after {
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

.select-shell select {
    width: 100%;
    min-height: 48px;
    appearance: none;
    padding: 0 34px 0 12px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    background: rgba(1, 10, 18, 0.78);
    color: var(--color-text-primary);
    font: inherit;
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

.modal-action--ghost:hover {
    border-color: var(--color-accent-primary);
    color: var(--color-accent-strong);
    transform: translateY(-1px);
}

.modal-action--primary:hover {
    background: var(--color-accent-primary);
    transform: translateY(-1px);
}

@media (max-width: 720px) {
    .modal-row {
        grid-template-columns: 1fr;
    }
}
</style>
