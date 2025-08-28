"use server"

import { cookies } from "next/headers"
import { revalidatePath } from "next/cache"
import { LayoutKeys } from "@/lib/layout-constants"

export async function updateLayoutPreference(key: LayoutKeys, value: string) {
    if (!Object.values(LayoutKeys).includes(key)) {
        throw new Error("Invalid layout preference key.")
    }

    try {
        (await cookies()).set(key, value, {
            path: "/",
            maxAge: 60 * 60 * 24 * 365,
            httpOnly: true,
            sameSite: "lax",
        })

        revalidatePath("/", "layout")

        return { success: true }
    } catch (error) {
        console.error("Failed to set layout preference cookie:", error)
        throw new Error("Could not update layout preference.")
    }
}