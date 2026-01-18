'use client'

import { SessionProvider } from 'next-auth/react'
import React from 'react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { Session } from 'next-auth'
import type { ThemeProviderProps } from 'next-themes'
import { ThemeProvider } from '@/components/common/theme-provider'

const queryClient = new QueryClient()

interface Props extends ThemeProviderProps {
    children: React.ReactNode
    session?: Session | null
}

export function NextAuthProvider({ children, session, ...props }: Props) {
    return (
        <SessionProvider session={session}>
            <QueryClientProvider client={queryClient}>
                <ThemeProvider {...props}>
                    {children}
                </ThemeProvider>
            </QueryClientProvider>
        </SessionProvider>
    )
}
