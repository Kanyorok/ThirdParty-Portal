'use client'

import { SessionProvider } from 'next-auth/react'
import { ThemeProvider as NextThemesProvider, ThemeProviderProps } from 'next-themes'
import React from 'react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { Session } from 'next-auth'
import { ProfileSyncWatcher } from "@/components/profiles/profile-watcher"

const queryClient = new QueryClient()

interface Props extends ThemeProviderProps {
    children: React.ReactNode
    session?: Session | null
}

export function NextAuthProvider({ children, session, ...props }: Props) {
    return (
        <SessionProvider session={session}>
            <QueryClientProvider client={queryClient}>
                <NextThemesProvider {...props}>
                    <ProfileSyncWatcher />
                    {children}
                </NextThemesProvider>
            </QueryClientProvider>
        </SessionProvider>
    )
}