"use client"

import { useEffect, ReactNode } from "react"
import { useSession, signOut } from "next-auth/react"
import { useRouter } from "next/navigation"
import { Loader2 } from "lucide-react"

interface AuthGuardProps {
    children: ReactNode
    fallback?: ReactNode
}

export function AuthGuard({ children, fallback }: AuthGuardProps) {
    const { data: session, status } = useSession()
    const router = useRouter()

    useEffect(() => {
        // If session is loading, wait
        if (status === "loading") return

        // If no session, redirect to signin
        if (status === "unauthenticated" || !session) {
            console.log('No valid session - redirecting to signin')
            router.replace('/signin?error=SessionRequired')
            return
        }

        // If session exists but user is not active/approved, sign out and redirect
        if (session.user && (!session.user.isActive || !session.user.isApproved)) {
            console.log('User not active or approved - signing out')
            signOut({ callbackUrl: '/signin?error=AccountNotApproved' })
            return
        }

        // If no access token, sign out
        if (!session.accessToken) {
            console.log('No access token - signing out')
            signOut({ callbackUrl: '/signin?error=NoAccessToken' })
            return
        }

    }, [session, status, router])

    // Show loading while session is being fetched
    if (status === "loading") {
        return (
            fallback || (
                <div className="flex min-h-screen items-center justify-center">
                    <div className="flex items-center space-x-2">
                        <Loader2 className="h-4 w-4 animate-spin" />
                        <span className="text-sm text-muted-foreground">Loading...</span>
                    </div>
                </div>
            )
        )
    }

    // If not authenticated, show nothing (redirect happening)
    if (status === "unauthenticated" || !session) {
        return null
    }

    // If user not active/approved, show nothing (sign out happening)
    if (!session.user?.isActive || !session.user?.isApproved) {
        return null
    }

    // If no access token, show nothing (sign out happening)
    if (!session.accessToken) {
        return null
    }

    // All checks passed, render children
    return <>{children}</>
}

// Higher-order component version
export function withAuth<P extends object>(Component: React.ComponentType<P>) {
    return function AuthenticatedComponent(props: P) {
        return (
            <AuthGuard>
                <Component {...props} />
            </AuthGuard>
        )
    }
}


