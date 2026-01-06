"use server"

import { requestPasswordReset } from "./auth-actions"
import { axiosInstance } from "@/lib/axios"

export async function recoverPassword(email: string) {
    return requestPasswordReset(email)
}

export async function registerUser(data: any) {
    try {
        // Assuming the backend endpoint from hooks/use-register.ts
        const response = await axiosInstance.post('/third-party-auth/register', data);
        
        return {
            success: true,
            data: response.data,
            message: "Registration successful"
        };
    } catch (error: any) {
        console.error("Registration error:", error);
        return {
            success: false,
            error: error.response?.data?.message || "Registration failed",
            message: error.response?.data?.message || "Registration failed"
        };
    }
}
