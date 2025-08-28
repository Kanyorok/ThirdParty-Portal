"use client"

import { useSession, signOut } from "next-auth/react"
import { useEffect } from "react"

type Props = {
    children?: React.ReactNode
}

type SessionWithError = {
    error?: string;
    user?: { name?: string; email?: string; image?: string };
    expires?: string;
}

const AuthErrorHandler = ({ children }: Props) => {
    const { data: session } = useSession() as { data: SessionWithError | null }

    useEffect(() => {
        if (session?.error === "RefreshAccessTokenError") {
            signOut({ callbackUrl: "/signin", redirect: true })
        }
    }, [session])

    return <>{children}</>
}

export default AuthErrorHandler