"use client"

import { Spinner } from "@/components/common/spinner"

export default function Loading({ message = "Loading..." }: { message?: string }) {
    return (
        <div className="flex flex-col items-center justify-center min-h-screen gap-3 text-gray-700">
            <Spinner className="w-8 h-8 text-blue-600 animate-spin" />
            <span className="text-lg font-medium">{message}</span>
        </div>
    )
}
