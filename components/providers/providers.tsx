'use client'

import { SessionProvider } from 'next-auth/react'
import React from 'react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { Session } from 'next-auth'
import { SWRConfig } from 'swr'
import { IdleSessionTimeout } from './idle-session-timeout'

interface Props {
    children: React.ReactNode
    session?: Session | null
}

export function NextAuthProvider({ children, session }: Props) {
    const [queryClient] = React.useState(
        () =>
            new QueryClient({
                defaultOptions: {
                    queries: {
                        staleTime: 5 * 60 * 1000,
                        refetchOnWindowFocus: false,
                        refetchOnReconnect: false,
                        refetchOnMount: false,
                        retry: 1,
                    },
                    mutations: {
                        retry: 0,
                    },
                },
            })
    )

    return (
        <SessionProvider session={session} refetchOnWindowFocus={false} refetchInterval={0}>
            <IdleSessionTimeout />
            <QueryClientProvider client={queryClient}>
                <SWRConfig
                    value={{
                        revalidateOnFocus: false,
                        revalidateOnReconnect: false,
                        shouldRetryOnError: false,
                        errorRetryCount: 0,
                        dedupingInterval: 5 * 60 * 1000,
                    }}
                >
                    {children}
                </SWRConfig>
            </QueryClientProvider>
        </SessionProvider>
    )
}
