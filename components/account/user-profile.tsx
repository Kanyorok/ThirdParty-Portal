'use client'

import React, { useState, useCallback } from 'react'
import useSWR from 'swr'
import { AlertTriangle } from 'lucide-react'
import { Button } from '@/components/common/button'
import { UserProfile } from '@/types/next-auth'
import { useSession } from 'next-auth/react'
import { apiService } from '@/lib/api/profile'
import { motion, AnimatePresence } from 'framer-motion'
import ProfileCard from '@/components/account/profile-card'
import ProfileDetailsCard from '@/components/account/profile-detail-card'
import NotificationCard from '@/components/account/notification-card'
// import DangerZoneCard from '@/components/account/danger-card'
import EditProfileModal from '@/components/account/edit-profile-modal'
import { NotificationModal } from '@/components/account/notification-modal'
import { PasswordChangeModal } from '@/components/modals/pwd-change-modal'
import { ProfilePictureModal } from '@/components/account/profile-pic-modal'
import { Spinner } from '@/components/common/spinner'

const fetcher = ([_url, token]: [string, string]) => apiService.getProfile(token)

const UserProfilePage: React.FC = () => {
    const { data: session } = useSession()
    const accessToken = session?.accessToken ?? ''

    const { data: profile, error, isLoading, mutate } = useSWR<UserProfile>(
        accessToken ? ['/api/profile', accessToken] : null,
        fetcher,
        {
            revalidateOnFocus: false,
            revalidateOnReconnect: false,
            revalidateIfStale: true,
        }
    )

    const [isEditProfileModalOpen, setIsEditProfileModalOpen] = useState(false)
    const [isNotificationModalOpen, setIsNotificationModalOpen] = useState(false)
    const [isPasswordChangeModalOpen, setIsPasswordChangeModalOpen] = useState(false)
    const [isProfilePictureModalOpen, setIsProfilePictureModalOpen] = useState(false)

    const toggleModal = useCallback((setter: React.Dispatch<React.SetStateAction<boolean>>) => {
        setter(prev => !prev)
    }, [])

    if (isLoading) {
        return (
            <div className="flex justify-center items-center min-h-[calc(100vh-200px)]">
                <Spinner className="h-10 w-10 animate-spin text-primary" />
            </div>
        )
    }

    if (error || !profile) {
        return (
            <div className="flex flex-col items-center justify-center min-h-[calc(100vh-200px)] text-center p-4 border border-input rounded-lg mx-auto max-w-lg">
                <AlertTriangle className="h-16 w-16 text-destructive mb-4" />
                <h2 className="text-2xl font-bold text-foreground">Failed to load profile</h2>
                <p className="text-muted-foreground mt-2">
                    An error occurred while fetching your profile. Please try refreshing the page.
                </p>
                <Button onClick={() => window.location.reload()} className="mt-6">
                    Retry
                </Button>
            </div>
        )
    }

    const normalizedProfile = {
        ...profile,
        phone: profile.phone || undefined,
        gender: profile.gender || undefined,
    }

    return (
        <AnimatePresence mode="wait">
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: 20 }}
                transition={{ duration: 0.3 }}
                className="grid lg:grid-cols-3 gap-8 p-4 container"
            >
                <div className="lg:col-span-1 space-y-6">
                    <ProfileCard
                        profile={normalizedProfile}
                        onProfilePictureClick={() => toggleModal(setIsProfilePictureModalOpen)}
                        onEditProfile={() => toggleModal(setIsEditProfileModalOpen)}
                    />
                </div>

                <div className="lg:col-span-2 space-y-6">
                    <ProfileDetailsCard
                        profile={normalizedProfile}
                        onEdit={() => toggleModal(setIsEditProfileModalOpen)}
                        onPasswordChange={() => toggleModal(setIsPasswordChangeModalOpen)}
                        onProfilePictureClick={() => toggleModal(setIsProfilePictureModalOpen)}
                    />
                    <NotificationCard
                        profile={normalizedProfile}
                        onEdit={() => toggleModal(setIsNotificationModalOpen)}
                    />
                </div>

                <EditProfileModal
                    isOpen={isEditProfileModalOpen}
                    onClose={() => toggleModal(setIsEditProfileModalOpen)}
                    profile={normalizedProfile}
                    mutateProfile={mutate}
                    accessToken={accessToken}
                />
                <NotificationModal
                    isOpen={isNotificationModalOpen}
                    onClose={() => toggleModal(setIsNotificationModalOpen)}
                    profile={normalizedProfile}
                    mutateProfile={mutate}
                    accessToken={accessToken}
                />
                <PasswordChangeModal
                    isOpen={isPasswordChangeModalOpen}
                    onClose={() => toggleModal(setIsPasswordChangeModalOpen)}
                />
                <ProfilePictureModal
                    isOpen={isProfilePictureModalOpen}
                    onClose={() => toggleModal(setIsProfilePictureModalOpen)}
                    profile={normalizedProfile}
                    mutateProfile={mutate}
                    accessToken={accessToken}
                />
            </motion.div>
        </AnimatePresence>
    )
}

export default UserProfilePage