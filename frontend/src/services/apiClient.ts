import axios, { type AxiosError } from 'axios';

type UnauthorizedHandler = () => void;
type FeedbackAwareAxiosError = AxiosError & { feedbackHandled?: boolean };

let unauthorizedHandler: UnauthorizedHandler | null = null;

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  withCredentials: true,
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
  withXSRFToken: true
});

const authenticationEndpoints = ['api/login', 'api/register', 'api/user', '/sanctum/csrf-cookie'];

apiClient.interceptors.response.use(
  (response) => response,
  (error: FeedbackAwareAxiosError) => {
    const requestUrl = error.config?.url ?? '';
    const isAuthenticationRequest = authenticationEndpoints.some((endpoint) => requestUrl.endsWith(endpoint));

    if (error.response?.status === 401 && !isAuthenticationRequest) {
      error.feedbackHandled = true;
      unauthorizedHandler?.();
    }

    return Promise.reject(error);
  },
);

export function setUnauthorizedHandler(handler: UnauthorizedHandler) {
  unauthorizedHandler = handler;
}

export default apiClient;
