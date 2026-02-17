'use client'

import { useEffect } from 'react'
import { useSession } from 'next-auth/react'
import { useProfileStore } from '@/store/use-profile-store'
import { resolveSessionAvailableProfiles } from '@/lib/profile/session-profiles'

export function ProfileSyncWatcher() {
    const { data: session, status } = useSession()
    const initializeProfiles = useProfileStore((state) => state.initializeProfiles)

    useEffect(() => {
        if (status === 'authenticated' && session?.user) {
            initializeProfiles(resolveSessionAvailableProfiles(session.user as any))
        } else if (status === 'unauthenticated') {
            initializeProfiles(['base'])
        }
    }, [session, status, initializeProfiles])

    return null
}
