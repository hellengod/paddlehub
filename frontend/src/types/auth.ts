export interface LoginResponse {
    message: string,
    user: User,
}

export type AuthStatus = 'unknown' | 'authenticated' | 'guest'
export interface AuthState {
    status: AuthStatus
    user: User | null
}
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

export interface RegisterPayload {
    name: string,
    email: string;
    password: string;
    passwordConfirmation: string;
}

export interface LoginPayload {
    email: string;
    password: string;
}