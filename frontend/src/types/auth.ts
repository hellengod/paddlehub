export interface LoginResponse{
    message: string,
    token: string,
    user: User,
}

export interface User{
    id: number,
    email: string,
    name: string
}