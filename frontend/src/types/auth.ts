export interface ApiResponse<T> {
    message: string,
    data: T,
}

export interface AuthenticatedUserData {
    user: User,
}

export type LoginResponse = ApiResponse<AuthenticatedUserData>;
export type RegisterResponse = ApiResponse<AuthenticatedUserData>;
export type CurrentUserResponse = ApiResponse<AuthenticatedUserData>;
export type LogoutResponse = ApiResponse<null>;

export type AuthStatus = 'unknown' | 'authenticated' | 'guest'
export interface AuthState {
    status: AuthStatus
    user: User | null
}
export interface User {
    id: number,
    email: string,
    name: string,
    avatarUrl: string | null,
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
