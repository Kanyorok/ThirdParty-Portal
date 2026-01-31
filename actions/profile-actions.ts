"use server"

import { getServerSession } from "next-auth/next";
import { authOptions } from "@/lib/auth-options";
import { ProfileFormValues } from "@/store/profile"

export async function updateProfile(values: ProfileFormValues) {
    const session = await getServerSession(authOptions);

    try {
        const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/profile/update`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${session?.accessToken}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(values),
        });

        const data = await response.json();

        if (!response.ok) {
            return { success: false, message: data.message || "Failed to update profile" };
        }

        return { success: true, message: "Profile updated successfully" };
    } catch {
        return { success: false, message: "Connection error" };
    }
}
