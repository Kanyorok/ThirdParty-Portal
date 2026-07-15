"use server"

import { getServerSession } from "next-auth/next";
import { authOptions } from "@/lib/auth-options";
import { getBaseUrl } from "@/lib/api-base"
import { ProfileFormValues } from "@/store/profile"

export async function updateProfile(values: ProfileFormValues) {
    const session = await getServerSession(authOptions);

    try {
        const backend = `${getBaseUrl()}/api/v1/portal/profile/update`
        const response = await fetch(backend, {
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
