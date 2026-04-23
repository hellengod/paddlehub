export default function useAuth() {
    async function login(email: string, password: string) {
        const auth = await fetch("http://127.0.0.1:8000/api/login", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        })
        const resposta = await auth.json()
        if (!auth.ok) {
            throw new Error(resposta.message)
        }
        return resposta;

    }
    return {
        login,
    }
}