export interface LoginResponse {
    message: string,
    user: User,
}

export type AuthStatus = 'unknown' | 'authenticated' | 'guest'

export interface User {
    id: number,
    email: string,
    name: string
}

export interface LogoutResponse {
    message: string,
}

export interface RegisterResponse {
    message: string,
}
