<template>
  <Teleport to="body">
    <TransitionGroup class="feedback-stack" name="feedback" tag="section" aria-label="Avisos da aplicação">
      <article
        v-for="feedback in messages"
        :key="feedback.id"
        class="feedback-card"
        :class="`feedback-card--${feedback.intent}`"
        :role="feedback.intent === 'error' || feedback.intent === 'warning' ? 'alert' : 'status'"
      >
        <span class="feedback-icon" aria-hidden="true">{{ intentIcon[feedback.intent] }}</span>
        <div class="feedback-content">
          <strong>{{ feedback.title }}</strong>
          <p>{{ feedback.message }}</p>
        </div>
        <button type="button" class="feedback-close" aria-label="Fechar aviso" @click="dismiss(feedback.id)">
          &times;
        </button>
      </article>
    </TransitionGroup>
  </Teleport>
</template>

<script setup lang="ts">
import { useFeedback } from '@/composables/useFeedback';
import type { FeedbackIntent } from '@/types/feedback';

const { messages, dismiss } = useFeedback();
const intentIcon: Record<FeedbackIntent, string> = {
  success: '✓',
  warning: '!',
  error: '×',
  info: 'i',
};
</script>

<style scoped>
.feedback-stack {
  position: fixed;
  z-index: 2000;
  top: var(--space-5);
  right: var(--space-5);
  display: grid;
  width: min(390px, calc(100vw - 32px));
  gap: 10px;
  pointer-events: none;
}

.feedback-card {
  --feedback-color: #71b7ff;
  display: grid;
  grid-template-columns: 28px minmax(0, 1fr) 28px;
  gap: 12px;
  align-items: start;
  padding: 14px;
  border: 1px solid color-mix(in srgb, var(--feedback-color) 42%, transparent);
  border-radius: var(--radius-md);
  background: rgba(3, 20, 30, 0.97);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.38);
  pointer-events: auto;
}

.feedback-card--success { --feedback-color: #51d6a0; }
.feedback-card--warning { --feedback-color: #f3bd5b; }
.feedback-card--error { --feedback-color: #ff7373; }

.feedback-icon {
  display: grid;
  width: 28px;
  height: 28px;
  place-items: center;
  border-radius: 50%;
  background: color-mix(in srgb, var(--feedback-color) 16%, transparent);
  color: var(--feedback-color);
  font-weight: 800;
}

.feedback-content strong {
  color: var(--color-text-primary);
  font-size: 14px;
}

.feedback-content p {
  margin-top: 2px;
  color: var(--color-text-secondary);
  font-size: 13px;
  line-height: 1.45;
  white-space: pre-line;
}

.feedback-close {
  border: 0;
  background: transparent;
  color: var(--color-text-secondary);
  font-size: 22px;
  line-height: 1;
  cursor: pointer;
}

.feedback-close:focus-visible {
  outline: 2px solid var(--feedback-color);
  outline-offset: 2px;
}

.feedback-enter-active,
.feedback-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.feedback-enter-from,
.feedback-leave-to { opacity: 0; transform: translateX(18px); }

@media (max-width: 640px) {
  .feedback-stack {
    top: 12px;
    right: 16px;
  }
}
</style>
