'use client'

import { useEffect } from 'react'
import { useSession } from 'next-auth/react'
import { useProfileStore, ProfileType } from '@/store/profile-store'

export function ProfileSyncWatcher() {
    const { data: session, status } = useSession()
    const initializeProfiles = useProfileStore((state) => state.initializeProfiles)

    useEffect(() => {
        if (status === 'authenticated' && session?.user) {
            const types: ProfileType[] = ['base']

            if (session.user.is_supplier) types.push('Supplier')
            if (session.user.is_tenant) types.push('Tenant')
            if (session.user.is_customer) types.push('Customer')

            initializeProfiles(types)
        } else if (status === 'unauthenticated') {
            initializeProfiles(['base'])
        }
    }, [session, status, initializeProfiles])

    return null
}