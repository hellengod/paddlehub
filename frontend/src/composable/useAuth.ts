import type { LoginResponse, LogoutResponse, RegisterResponse } from "@/types/auth";
export default function useAuth() {

    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;
    async function login(email: string, password: string): Promise<LoginResponse> {
        const loginUrl = apiBaseUrl + "login"
        let response: Response;
        try {
            response = await fetch(loginUrl, {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password })
            })
        } catch {
            throw new Error("Nao foi possivel se conectar ao servidor")
        }

        const responseText = await response.text()
        let responseData: LoginResponse

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

    async function validateToken() {
        const token = localStorage.getItem('auth_token')
        if (!token) {
            return false;
        }
        const userToken = apiBaseUrl + "user"

        let response: Response;

        try {
            response = await fetch(userToken, {
                method: "GET",
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
        } catch {
            return false;
        }

        if (!response.ok) return false

        return true;
    }

    async function logout(): Promise<LogoutResponse> {
        const token = localStorage.getItem('auth_token')
        if (!token) {
            throw new Error("Usuario nao autenticado")
        }
        const logoutUrl = apiBaseUrl + "logout"
        let response: Response;
        try {
            response = await fetch(logoutUrl, {
                method: "POST",
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
        } catch {
            throw new Error("Nao foi possivel se conectar ao servidor")
        }
        const responseText = await response.text()
        let responseData: LogoutResponse;

        try {
            responseData = JSON.parse(responseText)
        } catch {
            throw new Error('Resposta invalida do servidor')
        }

        if (!response.ok) {
            throw new Error(responseData.message)
        }
        localStorage.removeItem('auth_token')

        return responseData;

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
