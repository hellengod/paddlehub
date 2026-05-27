import apiClient from "@/services/apiClient";
import type { LoginResponse, LogoutResponse, RegisterResponse } from "@/types/auth";
export default function useAuth() {

    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;
    async function initializeCsrf() {
        await apiClient.get('/sanctum/csrf-cookie');
    }
    async function login(email: string, password: string): Promise<LoginResponse> {
        await initializeCsrf();

        try {
            const response = await apiClient.post('api/login', {
                "email": email,
                "password": password
            });

            return response.data;
        } catch {
            throw new Error("Nao foi possivel realizar o login")
        }

    }

    async function validateToken() {
        try {
            await apiClient.get('api/user');
            return true;

        } catch {
            return false;
        }
    }

    async function logout(): Promise<LogoutResponse> {
        try {
            const response = await apiClient.post('api/logout');
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
        login,
        validateToken,
        logout,
        register
    }
}
