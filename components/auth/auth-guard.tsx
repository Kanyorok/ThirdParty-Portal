import { useEffect, ReactNode } from "react"
import { Loader2 } from "lucide-react"

interface CustomUser {
    isActive: boolean
    isApproved: boolean
    [key: string]: any
}

interface CustomSession {
    user?: CustomUser
    accessToken?: string
    expires: string
}

const useSession = () => ({ data: null, status: "loading" as const })
const signOut = (options?: { callbackUrl?: string }) => { }
const useRouter = () => ({ replace: (url: string) => { } })

interface AuthGuardProps {
    children: ReactNode
    fallback?: ReactNode
}

export function AuthGuard({ children, fallback }: AuthGuardProps) {
    const { data: session, status } = useSession() as { data: CustomSession | null, status: "loading" | "authenticated" | "unauthenticated" }
    const router = useRouter()

    useEffect(() => {
        if (status === "loading") {
            return
        }

        if (status === "unauthenticated" || !session) {
            router.replace("/signin?error=SessionRequired")
            return
        }

        const { user, accessToken } = session

        if (!accessToken) {
            signOut({ callbackUrl: "/signin?error=NoAccessToken" })
            return
        }

        if (user && (!user.isActive || !user.isApproved)) {
            signOut({ callbackUrl: "/signin?error=AccountNotApproved" })
            return
        }
    }, [session, status, router])

    if (status === "loading") {
        return fallback || (
            <div className="flex min-h-screen items-center justify-center bg-gray-50">
                <div className="flex items-center space-x-3 p-6 bg-white shadow-xl rounded-xl border border-gray-200">
                    <Loader2 className="h-6 w-6 animate-spin text-indigo-600" />
                    <p className="text-base font-medium text-gray-700 font-sans">Authenticating Session...</p>
                </div>
            </div>
        )
    }

    if (status === "unauthenticated" || !session || !session.accessToken || !session.user?.isActive || !session.user?.isApproved) {
        return null
    }

    return <>{children}</>
}

export function withAuth<P extends object>(Component: React.ComponentType<P>) {
    return function AuthenticatedComponent(props: P) {
        return (
            <AuthGuard>
                <Component {...props} />
            </AuthGuard>
        )
    }
}