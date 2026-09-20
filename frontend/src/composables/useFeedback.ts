import type { FeedbackIntent, FeedbackMessage } from '@/types/feedback';
import axios from 'axios';
import { computed, reactive } from 'vue';

const DEFAULT_DURATION = 5000;
const feedbackState = reactive<{ messages: FeedbackMessage[] }>({ messages: [] });
const timers = new Map<number, ReturnType<typeof setTimeout>>();
let nextFeedbackId = 1;

function validationMessage(errors: unknown): string | null {
  if (!errors || typeof errors !== 'object') {
    return null;
  }

  const messages = Object.values(errors)
    .flatMap((value) => Array.isArray(value) ? value : [value])
    .filter((value): value is string => typeof value === 'string' && value.trim() !== '');

  return messages.length > 0 ? messages.join('\n') : null;
}

export function normalizeErrorMessage(error: unknown, context: string): string {
  if (axios.isAxiosError(error)) {
    if (error.response?.status === 403) {
      return 'Você não possui permissão para realizar esta ação.';
    }

    const responseValidationMessage = validationMessage(error.response?.data?.errors);
    if (responseValidationMessage) {
      return responseValidationMessage;
    }

    const responseMessage = error.response?.data?.message;
    if (typeof responseMessage === 'string' && responseMessage.trim() !== '') {
      return responseMessage;
    }
  }

  if (error instanceof Error && error.message.trim() !== '') {
    return error.message;
  }

  return `Não foi possível ${context}. Tente novamente.`;
}

export function useFeedback() {
  const messages = computed(() => feedbackState.messages);

  function dismiss(id: number) {
    const timer = timers.get(id);
    if (timer) {
      clearTimeout(timer);
      timers.delete(id);
    }

    feedbackState.messages = feedbackState.messages.filter((message) => message.id !== id);
  }

  function notify(intent: FeedbackIntent, title: string, message: string, duration = DEFAULT_DURATION) {
    const id = nextFeedbackId++;
    feedbackState.messages.push({ id, intent, title, message, duration });

    if (duration > 0) {
      timers.set(id, setTimeout(() => dismiss(id), duration));
    }

    return id;
  }

  function handleSuccess(message: string) {
    return notify('success', 'Sucesso', message);
  }

  function handleWarning(message: string, title = 'Atenção') {
    return notify('warning', title, message);
  }

  function handleError(error: unknown, context: string) {
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      return null;
    }

    return notify('error', `Erro ao ${context}`, normalizeErrorMessage(error, context), 7000);
  }

  function handleErrorMessage(message: string, title = 'Erro') {
    return notify('error', title, message, 7000);
  }

  return {
    messages,
    dismiss,
    handleSuccess,
    handleWarning,
    handleError,
    handleErrorMessage,
  };
}
