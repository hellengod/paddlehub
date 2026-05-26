import apiClient from "@/services/apiClient";
import type { LoginResponse, LogoutResponse, RegisterResponse } from "@/types/auth";
export default function useAuth() {

    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;
    async function initializeCsrf() {
        await apiClient.get('/sanctum/csrf-cookie');
    }
    async function login(email: string, password: string): Promise<LoginResponse> {
        await initializeCsrf();

        const response = await apiClient.post('/login', {
            "email": email,
            "password": password
        });

        return response.data;
    }

    async function validateToken() {
        try {
            await apiClient.get('/user');
            return true;

        } catch {
            return false;
        }
    }

    async function logout(): Promise<LogoutResponse> {
        try {
            const response = await apiClient.post('/logout');
            return response.data

        } catch {
            throw new Error("Nao foi possivel realizar o logout")
        }
    }

    async function register(name: string, email: string, password: string, password_confirmation: string) {
        const registerUrl = apiBaseUrl + "register"
        let response: Response;
        try {
            response = await fetch(registerUrl, {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ name, email, password, password_confirmation })
            })
        } catch {
            throw new Error("Nao foi possivel se conectar ao servidor")
        }
        const responseText = await response.text()
        let responseData: RegisterResponse

        try {
            responseData = JSON.parse(responseText)
        } catch {
            throw new Error('Resposta invalida do servidor')
        }

        if (!response.ok) {
            throw new Error(responseData.message)
        }
        return responseData;

    }
    return {
        login,
        validateToken,
        logout,
        register
    }
}
