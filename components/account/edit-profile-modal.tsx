'use client'

import { useState, useEffect, useTransition, useCallback } from 'react'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/common/dialog'
import { Label } from '@/components/common/label'
import { Input } from '@/components/common/input'
import { Button } from '@/components/common/button'
import { Edit, Loader2 } from 'lucide-react'
import PhoneInput from 'react-phone-input-2'
import { toast } from 'sonner'
import { profileService } from '@/lib/api/profile'
import { MutatorOptions } from 'swr'

interface UserProfile {
    firstName: string
    lastName: string
    email: string
    phone?: string
    gender?: string
}

interface Props {
    isOpen: boolean
    onClose: () => void
    profile: UserProfile
    mutateProfile: (data?: any, options?: boolean | MutatorOptions) => Promise<any>
    accessToken: string
}

export default function EditProfileModal({
    isOpen,
    onClose,
    profile,
    mutateProfile,
    accessToken
}: Props) {
    const [formData, setFormData] = useState({
        firstName: profile.firstName,
        lastName: profile.lastName,
        email: profile.email,
        phone: profile.phone || '',
        gender: profile.gender || ''
    })

    const [isPending] = useTransition()
    const [saving, setSaving] = useState(false)

    useEffect(() => {
        if (isOpen) {
            setFormData({
                firstName: profile.firstName,
                lastName: profile.lastName,
                email: profile.email,
                phone: profile.phone || '',
                gender: profile.gender || ''
            })
        }
    }, [isOpen, profile])

    const handleSave = useCallback(async () => {
        setSaving(true)
        try {
            const response = await profileService.updateProfile('me', formData, accessToken)

            await mutateProfile(formData, { revalidate: true })
            toast.success(response.message || 'Profile updated successfully!')
            onClose()
        } catch (error: any) {
            toast.error(error.message || 'Failed to update profile')
        } finally {
            setSaving(false)
        }
    }, [formData, accessToken, mutateProfile, onClose])

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[520px] border-0 shadow-2xl rounded-2xl">
                <DialogHeader className="pb-3">
                    <DialogTitle className="text-2xl font-bold flex items-center gap-3">
                        <div className="p-2 rounded-xl bg-primary text-primary-foreground">
                            <Edit className="h-5 w-5" />
                        </div>
                        Edit Profile
                    </DialogTitle>
                </DialogHeader>

                <div className="space-y-6">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="firstName">First Name</Label>
                            <Input
                                id="firstName"
                                value={formData.firstName}
                                onChange={e => setFormData(prev => ({ ...prev, firstName: e.target.value }))}
                                disabled={saving || isPending}
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="lastName">Last Name</Label>
                            <Input
                                id="lastName"
                                value={formData.lastName}
                                onChange={e => setFormData(prev => ({ ...prev, lastName: e.target.value }))}
                                disabled={saving || isPending}
                            />
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            value={formData.email}
                            onChange={e => setFormData(prev => ({ ...prev, email: e.target.value }))}
                            disabled={saving || isPending}
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="phone">Phone Number</Label>
                        <div className="p-2 bg-muted rounded-xl border border-input focus-within:ring-2 focus-within:ring-ring">
                            <PhoneInput
                                country="us"
                                preferredCountries={['us', 'gb', 'ke', 'ng']}
                                enableSearch
                                value={formData.phone}
                                onChange={phone => setFormData(prev => ({ ...prev, phone }))}
                                disabled={saving || isPending}
                                inputClass="!w-full !bg-transparent !border-none !text-foreground font-medium"
                                buttonClass="!bg-transparent !border-none"
                                containerClass="!w-full"
                                dropdownClass="!bg-background !text-foreground !z-[9999]"
                            />
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="gender">Gender</Label>
                        <select
                            id="gender"
                            value={formData.gender}
                            onChange={e => setFormData(prev => ({ ...prev, gender: e.target.value }))}
                            disabled={saving || isPending}
                            className="w-full bg-muted p-3 rounded-xl text-foreground font-medium border border-input focus:outline-none"
                        >
                            <option value="">-- Select gender --</option>
                            <option value="m">Male</option>
                            <option value="f">Female</option>
                            <option value="o">Prefer not to say</option>
                        </select>
                    </div>
                </div>

                <div className="flex justify-end gap-3 pt-6">
                    <Button variant="outline" onClick={onClose} disabled={saving || isPending}>
                        Cancel
                    </Button>
                    <Button onClick={handleSave} disabled={saving || isPending} className="flex items-center gap-2">
                        {saving ? (
                            <>
                                <Loader2 className="h-4 w-4 animate-spin" />
                                Saving...
                            </>
                        ) : (
                            'Save Changes'
                        )}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    )
}
