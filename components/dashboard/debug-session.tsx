'use client'

import { useSession } from "next-auth/react"

export function DebugSession() {
    const { data: session } = useSession()

    if (!session?.user) return null

    return (
        <div className="p-4 m-4 border border-red-500 bg-red-50 rounded text-xs font-mono break-all">
            <h3 className="font-bold text-red-700 mb-2">Debug Session Data</h3>
            <pre>{JSON.stringify({
                isSupplier: session.user.isSupplier,
                isTenant: session.user.isTenant,
                isCustomer: session.user.isCustomer,
                roles: session.user.types,
                fullUser: session.user
            }, null, 2)}</pre>
        </div>
    )
}
