import type { LoginResponse } from "@/types/auth";
export default function useAuth() {

    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;
    async function login(email: string, password: string): Promise<LoginResponse> {
        const loginUrl = apiBaseUrl + "login"
        const response = await fetch(loginUrl, {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        })
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
    return {
        login,
    }
}
