export type FeedbackIntent = 'success' | 'warning' | 'error' | 'info';

export interface FeedbackMessage {
  id: number;
  intent: FeedbackIntent;
  title: string;
  message: string;
  duration: number;
}

export interface ConfirmDialogOptions {
  title: string;
  message: string;
  confirmLabel: string;
  cancelLabel?: string;
}
