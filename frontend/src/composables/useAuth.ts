import apiClient from "@/services/apiClient";
import { computed, reactive, ref } from "vue";
import type { AuthState, CurrentUserResponse, LoginPayload, LoginResponse, LogoutResponse, RegisterPayload, RegisterResponse, User } from "@/types/auth";

const authState = reactive<AuthState>({
    status: 'unknown',
    user: null,
});

let authRequest: Promise<boolean> | null = null;

export function useAuth() {
    const loading = ref(false);
    const status = computed(() => authState.status);
    const user = computed(() => authState.user);
    const isAuthenticated = computed(() => status.value === 'authenticated');

    async function initializeCsrf() {
        await apiClient.get('/sanctum/csrf-cookie');
    }

    function setAuthenticated(user: User) {
        authState.status = 'authenticated';
        authState.user = user;
    }

    function setGuest() {
        authState.status = 'guest';
        authState.user = null;
    }

    async function syncAuthState() {
        if (authRequest) {
            return authRequest;
        }

        authRequest = (async () => {
            try {
                const response = await apiClient.get<CurrentUserResponse>('api/user');
                setAuthenticated(response.data.data.user);
                return true;
            } catch {
                setGuest();
                return false;
            } finally {
                authRequest = null;
            }
        })();

        return authRequest;
    }

    async function login(payload: LoginPayload): Promise<LoginResponse> {
        loading.value = true

        try {
            await initializeCsrf();

            const response = await apiClient.post<LoginResponse>('api/login', {
                "email": payload.email,
                "password": payload.password
            });

            setAuthenticated(response.data.data.user);
            return response.data;
        } catch {
            throw new Error("Nao foi possivel realizar o login")
        } finally {
            loading.value = false

        }

    }

    async function initializeAuth() {
        if (authState.status !== 'unknown') {
            return authState.status === 'authenticated';
        }

        return syncAuthState();
    }

    async function logout(): Promise<LogoutResponse> {
        loading.value = true

        try {
            const response = await apiClient.post<LogoutResponse>('api/logout');
            setGuest();
            return response.data

        } catch {
            throw new Error("Nao foi possivel realizar o logout")
        } finally {
            loading.value = false
        }
    }

    async function register(payload: RegisterPayload): Promise<RegisterResponse> {
        loading.value = true


        try {
            await initializeCsrf();

            const response = await apiClient.post<RegisterResponse>('api/register', {
                "name": payload.name,
                "email": payload.email,
                "password": payload.password,
                "password_confirmation": payload.passwordConfirmation
            });

            return response.data;
        } catch {
            throw new Error("Nao foi possivel realizar o cadastro")
        } finally {
            loading.value = false

        }

    }
    return {
        status,
        user,
        isAuthenticated,
        initializeAuth,
        login,
        logout,
        register,
        loading
    }
}
