import type { ConfirmDialogOptions } from '@/types/feedback';
import { computed, reactive } from 'vue';

type ConfirmVariant = 'default' | 'danger';

interface ConfirmRequest extends Required<ConfirmDialogOptions> {
  variant: ConfirmVariant;
  resolve: (confirmed: boolean) => void;
}

const dialogState = reactive<{
  current: ConfirmRequest | null;
  queue: ConfirmRequest[];
}>({
  current: null,
  queue: [],
});

function showNextDialog() {
  if (!dialogState.current) {
    dialogState.current = dialogState.queue.shift() ?? null;
  }
}

function requestConfirmation(options: ConfirmDialogOptions, variant: ConfirmVariant) {
  return new Promise<boolean>((resolve) => {
    dialogState.queue.push({
      ...options,
      cancelLabel: options.cancelLabel ?? 'Cancelar',
      variant,
      resolve,
    });
    showNextDialog();
  });
}

export function useConfirmDialog() {
  const dialog = computed(() => dialogState.current);

  function confirm(options: ConfirmDialogOptions) {
    return requestConfirmation(options, 'default');
  }

  function confirmDanger(options: ConfirmDialogOptions) {
    return requestConfirmation(options, 'danger');
  }

  function answer(confirmed: boolean) {
    const current = dialogState.current;
    if (!current) {
      return;
    }

    dialogState.current = null;
    current.resolve(confirmed);
    showNextDialog();
  }

  return { dialog, confirm, confirmDanger, answer };
}
