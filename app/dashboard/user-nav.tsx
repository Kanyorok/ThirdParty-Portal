"use client"

import { useState, useTransition, useCallback } from "react"
import { signOut, useSession } from "next-auth/react"
import { UserNavUI } from "./side-nav/user-nav-ui"

export const UserNav = () => {
    const { data: session, status } = useSession()
    const [isPending, startTransition] = useTransition()
    const [isOpen, setIsOpen] = useState(false)

    const handleLogout = useCallback(() => {
        startTransition(async () => {
            await signOut({ callbackUrl: typeof window !== "undefined" ? `${window.location.origin}/signin` : "/signin" })
        })
    }, [])

    const isLoading = status === 'loading'

    return (
        <UserNavUI
            user={session?.user as any}
            isLoading={isLoading}
            isPending={isPending}
            isOpen={isOpen}
            onLogout={handleLogout}
            onOpenChange={setIsOpen}
        />
    )
}