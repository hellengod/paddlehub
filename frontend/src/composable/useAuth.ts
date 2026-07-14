import apiClient from "@/services/apiClient";
import { reactive } from "vue";
import type { AuthStatus, LoginResponse, LogoutResponse, RegisterResponse, User } from "@/types/auth";

const authState = reactive<{
    status: AuthStatus;
    user: User | null;
}>({
    status: 'unknown',
    user: null,
});

let authRequest: Promise<boolean> | null = null;

export default function useAuth() {

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
                const response = await apiClient.get<User>('api/user');
                setAuthenticated(response.data);
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

    async function login(email: string, password: string): Promise<LoginResponse> {
        await initializeCsrf();

        try {
            const response = await apiClient.post('api/login', {
                "email": email,
                "password": password
            });

            setAuthenticated(response.data.user);
            return response.data;
        } catch {
            throw new Error("Nao foi possivel realizar o login")
        }

    }

    async function initializeAuth() {
        if (authState.status !== 'unknown') {
            return authState.status === 'authenticated';
        }

        return syncAuthState();
    }

    async function logout(): Promise<LogoutResponse> {
        try {
            const response = await apiClient.post('api/logout');
            setGuest();
            return response.data

        } catch {
            throw new Error("Nao foi possivel realizar o logout")
        }
    }

    async function register(name: string, email: string, password: string, password_confirmation: string) {
        await initializeCsrf();
        try {
            const response = await apiClient.post('api/register', {
                "name": name,
                "email": email,
                "password": password,
                "password_confirmation": password_confirmation
            });

            return response.data;
        } catch {
            throw new Error("Nao foi possivel realizar o cadastro")
        }

    }
    return {
        authState,
        initializeAuth,
        login,
        logout,
        register
    }
}
