<template>
    <section class="profile-view">
        <div class="profile-panel">
            <header class="profile-header">
                <h1>Meu perfil</h1>
                <p>Acompanhe seu progresso na comunidade PaddleHub.</p>
            </header>

            <section class="profile-hero">
                <img class="cover-image" :class="{ 'cover-image--fallback': !hasCustomCover }" :src="coverSrc"
                    alt="Capa do perfil">
                <div class="cover-overlay"></div>

                <div class="hero-content">
                    <div class="avatar-shell">
                        <div class="avatar-media">
                            <img v-if="currentAvatarSrc" :src="currentAvatarSrc" :alt="avatarAlt">
                            <span v-else class="avatar-fallback" aria-hidden="true">
                                <ProfileUser></ProfileUser>
                            </span>
                        </div>
                        <button type="button" class="avatar-edit-button" @click="openAvatarPicker"
                            aria-label="Trocar foto de perfil">
                            <span class="avatar-edit-icon" aria-hidden="true">
                                <PencilIcon></PencilIcon>
                            </span>
                        </button>
                        <input ref="avatarInputRef" class="avatar-file-input" type="file" accept="image/*"
                            @change="handleAvatarSelection">
                    </div>

                    <div class="hero-text">
                        <h2>{{ displayName }}</h2>
                        <div class="location">
                            <img src="/map-pin-alt-svgrepo-com.svg" alt="" aria-hidden="true">
                            <span>{{ homeRiverText }}</span>
                        </div>
                        <p>{{ bioText }}</p>
                    </div>
                </div>

                <button type="button" class="profile-edit-trigger" @click="openProfileEditor">
                    <span class="profile-edit-trigger-icon" aria-hidden="true">
                        <PencilIcon></PencilIcon>
                    </span>
                    <span>Editar perfil</span>
                </button>
            </section>
        </div>

        <ProfileEditModal :model-value="isProfileEditorOpen" :cover-src="coverSrc" :has-custom-cover="hasCustomCover"
            :home-river="draftHomeRiver" :bio="draftBio" :cover-selection-text="coverSelectionText"
            @update:modelValue="handleProfileEditorToggle" @update:homeRiver="draftHomeRiver = $event"
            @update:bio="draftBio = $event" @select-cover="handleCoverSelection" @cancel="closeProfileEditor"
            @save="applyProfileChanges">
        </ProfileEditModal>

        <ProfileAvatarCropModal :model-value="isAvatarCropModalOpen" :image-src="avatarCropSource"
            @update:modelValue="handleAvatarCropModalToggle" @cancel="closeAvatarCropModal"
            @confirm="applyAvatarCrop">
        </ProfileAvatarCropModal>
    </section>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue';
import PencilIcon from '@/components/atoms/icons/PencilIcon.vue';
import ProfileUser from '@/components/atoms/icons/ProfileUser.vue';
import ProfileAvatarCropModal from '@/components/organisms/ProfileAvatarCropModal.vue';
import ProfileEditModal from '@/components/organisms/ProfileEditModal.vue';
import { useAuth } from '@/composables/useAuth';

const defaultCoverSrc = '/profile-cover-default.png';

const { user } = useAuth();
const avatarInputRef = ref<HTMLInputElement | null>(null);
const isAvatarCropModalOpen = ref(false);
const isProfileEditorOpen = ref(false);
const avatarCropSource = ref('');
const profileAvatarOverride = ref<string | null>(null);
const pendingAvatarSelectionUrl = ref<string | null>(null);
const appliedAvatarObjectUrl = ref<string | null>(null);
const profileBioOverride = ref<string | null>(null);
const profileHomeRiverOverride = ref<string | null>(null);
const profileCoverOverride = ref<string | null>(null);
const appliedCoverObjectUrl = ref<string | null>(null);
const draftCoverObjectUrl = ref<string | null>(null);
const draftBio = ref('');
const draftHomeRiver = ref('');
const draftCoverUrl = ref<string | null>(null);

const currentProfileBio = computed(() => profileBioOverride.value ?? user.value?.bio ?? '');
const currentHomeRiver = computed(() => profileHomeRiverOverride.value ?? user.value?.homeRiver ?? '');
const currentCoverSrc = computed(() => profileCoverOverride.value ?? user.value?.coverUrl ?? defaultCoverSrc);
const currentAvatarSrc = computed(() => profileAvatarOverride.value ?? user.value?.avatarUrl ?? '');
const hasCustomCover = computed(() => {
    const visibleCover = isProfileEditorOpen.value ? draftCoverUrl.value : currentCoverSrc.value;
    return Boolean(visibleCover && visibleCover !== defaultCoverSrc);
});
const coverSrc = computed(() =>
    isProfileEditorOpen.value && draftCoverUrl.value ? draftCoverUrl.value : currentCoverSrc.value
);
const avatarAlt = computed(() =>
    user.value?.name ? `Foto de perfil de ${user.value.name}` : 'Foto de perfil'
);
const displayName = computed(() => user.value?.name ?? 'Seu perfil');
const homeRiverText = computed(() => currentHomeRiver.value || 'Rio base nao informado');
const bioText = computed(() => currentProfileBio.value || 'Sem descricao cadastrada no momento.');
const coverSelectionText = computed(() =>
    draftCoverObjectUrl.value
        ? 'Nova capa selecionada. Salve para manter a pre-visualizacao.'
        : 'Escolha uma imagem para testar a capa antes da integracao com o backend.'
);

function openAvatarPicker() {
    avatarInputRef.value?.click();
}

function handleAvatarSelection(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        input.value = '';
        return;
    }

    resetPendingAvatarSelection();

    const objectUrl = URL.createObjectURL(file);
    pendingAvatarSelectionUrl.value = objectUrl;
    avatarCropSource.value = objectUrl;
    isAvatarCropModalOpen.value = true;
    input.value = '';
}

function openProfileEditor() {
    isProfileEditorOpen.value = true;
    draftBio.value = currentProfileBio.value;
    draftHomeRiver.value = currentHomeRiver.value;
    draftCoverUrl.value = currentCoverSrc.value;
}

function handleProfileEditorToggle(isOpen: boolean) {
    if (isOpen) {
        openProfileEditor();
        return;
    }

    closeProfileEditor();
}

function closeProfileEditor() {
    resetDraftCover();
    draftBio.value = '';
    draftHomeRiver.value = '';
    isProfileEditorOpen.value = false;
}

function handleCoverSelection(file: File) {
    if (draftCoverObjectUrl.value) {
        URL.revokeObjectURL(draftCoverObjectUrl.value);
    }

    const objectUrl = URL.createObjectURL(file);
    draftCoverObjectUrl.value = objectUrl;
    draftCoverUrl.value = objectUrl;
}

function applyProfileChanges() {
    profileBioOverride.value = draftBio.value.trim();
    profileHomeRiverOverride.value = draftHomeRiver.value.trim();

    if (draftCoverObjectUrl.value) {
        if (appliedCoverObjectUrl.value && appliedCoverObjectUrl.value !== draftCoverObjectUrl.value) {
            URL.revokeObjectURL(appliedCoverObjectUrl.value);
        }

        appliedCoverObjectUrl.value = draftCoverObjectUrl.value;
        profileCoverOverride.value = draftCoverObjectUrl.value;
        draftCoverObjectUrl.value = null;
    }

    draftCoverUrl.value = null;
    draftBio.value = '';
    draftHomeRiver.value = '';
    isProfileEditorOpen.value = false;
}

function handleAvatarCropModalToggle(isOpen: boolean) {
    if (!isOpen) {
        closeAvatarCropModal();
    }
}

function closeAvatarCropModal() {
    isAvatarCropModalOpen.value = false;
    avatarCropSource.value = '';
    resetPendingAvatarSelection();
}

function applyAvatarCrop(blob: Blob) {
    if (appliedAvatarObjectUrl.value) {
        URL.revokeObjectURL(appliedAvatarObjectUrl.value);
    }

    const objectUrl = URL.createObjectURL(blob);
    appliedAvatarObjectUrl.value = objectUrl;
    profileAvatarOverride.value = objectUrl;
    isAvatarCropModalOpen.value = false;
    avatarCropSource.value = '';
    resetPendingAvatarSelection();
}

function resetDraftCover() {
    if (draftCoverObjectUrl.value) {
        URL.revokeObjectURL(draftCoverObjectUrl.value);
        draftCoverObjectUrl.value = null;
    }

    draftCoverUrl.value = null;
}

function resetPendingAvatarSelection() {
    if (pendingAvatarSelectionUrl.value) {
        URL.revokeObjectURL(pendingAvatarSelectionUrl.value);
        pendingAvatarSelectionUrl.value = null;
    }
}

onBeforeUnmount(() => {
    if (pendingAvatarSelectionUrl.value) {
        URL.revokeObjectURL(pendingAvatarSelectionUrl.value);
    }

    if (appliedAvatarObjectUrl.value) {
        URL.revokeObjectURL(appliedAvatarObjectUrl.value);
    }

    if (draftCoverObjectUrl.value) {
        URL.revokeObjectURL(draftCoverObjectUrl.value);
    }

    if (appliedCoverObjectUrl.value) {
        URL.revokeObjectURL(appliedCoverObjectUrl.value);
    }
});
</script>

<style scoped>
.profile-view {
    display: flex;
    width: 100%;
    min-height: calc(100vh - 20px);
    padding-left: 6px;
}

.profile-panel {
    flex: 1;
    border: 1px solid var(--color-border-panel);
    border-radius: var(--radius-sm);
    background: var(--color-bg-panel);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.profile-header {
    position: sticky;
    top: 0;
    z-index: 2;
    padding: var(--space-5) 112px var(--space-5) var(--space-5);
    background: var(--color-bg-panel);
}

.profile-header h1 {
    font-size: 24px;
    font-weight: 600;
    color: var(--color-text-primary);
}

.profile-header p {
    margin-top: var(--space-2);
    color: var(--color-text-secondary);
    font-size: 16px;
}

.profile-hero {
    position: relative;
    min-height: 250px;
    border: 1px solid var(--color-border-subtle);
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
    object-fit: cover;
    object-position: center 88%;
}

.cover-overlay {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(90deg, rgba(2, 10, 17, 0.86) 0%, rgba(2, 10, 17, 0.46) 48%, rgba(2, 10, 17, 0.2) 100%),
        linear-gradient(180deg, rgba(3, 20, 30, 0.12) 0%, rgba(3, 20, 30, 0.48) 100%);
}

.hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 24px;
    min-height: 250px;
    padding: 40px 40px;
}

.avatar-shell {
    position: relative;
    width: 148px;
    height: 148px;
    flex-shrink: 0;
    overflow: visible;
}

.avatar-media {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.28);
    background: rgba(1, 10, 18, 0.72);
    color: var(--color-text-primary);
}

.avatar-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.avatar-fallback {
    width: 100%;
    height: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    background: var(--color-text-primary);
    color: var(--color-bg-panel);
}

.avatar-fallback :deep(svg) {
    width: 100%;
    height: 100%;
    display: block;
}

.avatar-edit-button {
    position: absolute;
    right: -4px;
    bottom: -4px;
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--color-border-strong);
    border-radius: 50%;
    background: var(--color-action-primary);
    color: var(--color-action-primary-text);
    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.3);
    z-index: 2;
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease;
}

.avatar-edit-button:hover {
    background: var(--color-accent-primary);
    color: var(--color-action-primary-text);
    transform: translateY(-1px);
}

.avatar-edit-button:focus-visible,
.profile-edit-trigger:focus-visible {
    outline: 2px solid var(--color-accent-strong);
    outline-offset: 2px;
}

.avatar-edit-icon {
    width: 20px;
    height: 20px;
    display: inline-flex;
}

.avatar-edit-icon :deep(svg) {
    width: 100%;
    height: 100%;
    display: block;
}

.avatar-file-input {
    display: none;
}

.profile-edit-trigger {
    position: absolute;
    right: 24px;
    bottom: 24px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    min-height: 48px;
    padding: 0 20px;
    border: 2px solid var(--color-border-strong);
    border-radius: var(--radius-sm);
    background: var(--color-action-primary);
    color: var(--color-action-primary-text);
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        transform 0.2s ease;
}

.profile-edit-trigger:hover {
    background: var(--color-accent-primary);
    transform: translateY(-1px);
}

.profile-edit-trigger-icon {
    width: 18px;
    height: 18px;
    display: inline-flex;
}

.profile-edit-trigger-icon :deep(svg) {
    width: 100%;
    height: 100%;
    display: block;
}

.hero-text {
    max-width: 540px;
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.hero-text h2 {
    font-size: clamp(2rem, 3vw, 3.1rem);
    line-height: 1.05;
    font-weight: 600;
    color: var(--color-text-primary);
}

.location {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: var(--color-text-primary);
    font-size: 1.05rem;
}

.location img {
    width: 20px;
    height: 20px;
    opacity: 0.92;
}

.hero-text p {
    color: rgba(240, 248, 255, 0.92);
    font-size: 1.1rem;
    line-height: 1.55;
}

@media (max-width: 900px) {
    .profile-header {
        padding-right: 96px;
    }

    .hero-content {
        align-items: flex-start;
        flex-direction: column;
        padding: 32px 24px 88px;
    }

    .avatar-shell {
        width: 120px;
        height: 120px;
    }

    .profile-edit-trigger {
        right: 16px;
        bottom: 16px;
    }
}
</style>
