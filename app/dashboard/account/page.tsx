'use client';

import React, { useState, useTransition, useCallback, useEffect, useRef } from 'react';
import useSWR, { MutatorOptions } from 'swr';
import { toast } from 'sonner';
import {
    User,
    Edit,
    KeyRound,
    Bell,
    Info,
    Camera,
    Shield,
    AlertTriangle,
    Trash2,
    Loader2,
    Mail,
    Phone,
    BadgeCheckIcon,
    UploadCloud,
    X,
    Building2,
    Users,
} from 'lucide-react';
import { Button } from '@/components/common/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/common/card';
import { Input } from '@/components/common/input';
import { Label } from '@/components/common/label';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/common/avatar';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/common/alert-dialog';
import PhoneInput from 'react-phone-input-2'
import 'react-phone-input-2/lib/style.css'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/common/dialog';
import { Checkbox } from '@/components/common/checkbox';
import { Switch } from '@/components/common/switch';
import { Separator } from '@/components/common/separator';
import { Badge } from '@/components/common/badge';
import { UserProfile } from '@/types/next-auth';
import { useSession } from 'next-auth/react';
import { getInitials } from '@/lib/utils';
import { apiService } from '@/lib/api/profile';
import { motion } from 'framer-motion';

const RolesCard: React.FC<{
    profile: UserProfile;
    accessToken: string;
    mutateProfile: (data?: any, options?: boolean | MutatorOptions) => Promise<any>;
}> = ({ profile, accessToken, mutateProfile }) => {
    const [togglingRoleId, setTogglingRoleId] = useState<number | null>(null);

    const handleToggleRole = async (roleId: number, currentStatus: boolean, roleLabel: string) => {
        setTogglingRoleId(roleId);
        try {
            // Optimistic update? Maybe too risky if backend fails. We'll wait.
            const newStatus = !currentStatus;
            await apiService.toggleRole(roleId, newStatus, accessToken);
            toast.success(`${roleLabel} role ${newStatus ? 'enabled' : 'disabled'} successfully.`);

            // Mutate profile to refresh data
            await mutateProfile(); // This should re-fetch because toggleRole updates backend state
        } catch (error: any) {
            toast.error(error.message || `Failed to toggle ${roleLabel} role.`);
        } finally {
            setTogglingRoleId(null);
        }
    };

    // Extract roles from 'types' (Assuming ThirdPartyResource returns 'types' which are mapped to profile.thirdParty?.types or similar)
    // The previous type definition update added types?: ThirdPartyTypeEntry[] to BaseUser.
    // However, UserProfile interface might need checking. Based on resource, it returns nested under 'user_profile'.
    // The useSWR hook returns UserProfile.
    // We need to access the 'types' from the profile. 
    // Wait, the API returns { user_profile: ... }. The apiService.getProfile unpacks this.
    // The UserProfile interface in next-auth.d.ts doesn't explicitly have 'types' at the top level, 
    // it has 'thirdParty.types' implied? No, let's check the resource again.
    // RESOURCE: 'types' => mapped array.
    // So 'types' is a top-level property of the returned object (which matches UserProfile structure roughly).
    // Let's assume profile.types exists as we saw in the resource.
    // Implementation note: The TS interface UserProfile might strictly not have it, so we cast for now or update interface.
    // We updated BaseUser but not UserProfile in the d.ts file properly? 
    // BaseUser has 'types'. UserProfile might likely be the same shape.

    // @ts-ignore
    const roles = profile.types || [];

    return (
        <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.3 }}
        >
            <Card className="border-0 shadow-medium bg-card">
                <CardHeader className="pb-4">
                    <CardTitle className="flex items-center gap-3 text-2xl font-bold">
                        <div className="p-2 bg-gradient-primary rounded-lg">
                            <Users className="h-5 w-5 text-primary-foreground" />
                        </div>
                        Manage Roles
                    </CardTitle>
                    <CardDescription>
                        Enable or disable your access roles. Identifying as a Supplier, Tenant, or Customer.
                    </CardDescription>
                </CardHeader>
                <Separator />
                <CardContent className="pt-6 space-y-6">
                    {roles.length === 0 ? (
                        <div className="text-center text-muted-foreground p-4">
                            No roles assigned.
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {roles.map((role: any) => (
                                <div key={role.pivotId} className="flex items-center justify-between p-4 bg-muted/50 rounded-lg border border-border">
                                    <div className="space-y-1">
                                        <div className="flex items-center gap-2">
                                            <p className="font-semibold text-foreground">{role.label || role.code}</p>
                                            {role.isActive ? (
                                                <Badge className="bg-green-500/10 text-green-600 hover:bg-green-500/20 border-green-200">Active</Badge>
                                            ) : (
                                                <Badge variant="outline" className="text-muted-foreground">Inactive</Badge>
                                            )}
                                        </div>
                                        <p className="text-sm text-muted-foreground">
                                            {role.code === 'SU' ? 'Supplier Access' :
                                                role.code === 'TN' ? 'Tenant Access' :
                                                    role.code === 'CU' ? 'Customer Access' : 'Generic Access'}
                                        </p>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        {togglingRoleId === role.pivotId && <Loader2 className="h-4 w-4 animate-spin text-primary" />}
                                        <Switch
                                            checked={role.isActive}
                                            onCheckedChange={() => handleToggleRole(role.pivotId, role.isActive, role.label || role.code)}
                                            disabled={togglingRoleId !== null}
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </CardContent>
            </Card>
        </motion.div>
    );
};

const ProfileDetailsCard: React.FC<{
    profile: UserProfile;
    onEdit: () => void;
    onPasswordChange: () => void;
    onProfilePictureClick: () => void;
}> = ({ profile, onEdit, onPasswordChange, onProfilePictureClick }) => (
    <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, delay: 0.2 }}
    >
        <Card className="border-0 shadow-medium bg-card">
            <CardHeader className="pb-4">
                <CardTitle className="flex items-center gap-3 text-2xl font-bold">
                    <div className="p-2 bg-gradient-primary rounded-lg">
                        <User className="h-5 w-5 text-primary-foreground" />
                    </div>
                    Personal Information
                </CardTitle>
            </CardHeader>
            <Separator />
            <CardContent className="pt-6 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="space-y-2">
                        <Label className="text-sm font-medium text-muted-foreground">First Name</Label>
                        <div className="p-3 bg-muted rounded-lg">
                            <p className="font-semibold text-foreground">{profile.firstName || '-'}</p>
                        </div>
                    </div>
                    <div className="space-y-2">
                        <Label className="text-sm font-medium text-muted-foreground">Last Name</Label>
                        <div className="p-3 bg-muted rounded-lg">
                            <p className="font-semibold text-foreground">{profile.lastName || '-'}</p>
                        </div>
                    </div>
                    <div className="space-y-2">
                        <Label className="text-sm font-medium text-muted-foreground">Email Address</Label>
                        <div className="p-3 bg-muted rounded-lg flex items-center gap-2 opacity-80">
                            <Mail className="h-4 w-4 text-muted-foreground" />
                            <p className="font-semibold text-foreground">{profile.email}</p>
                            <Badge variant="secondary" className="ml-auto text-xs">Read-only</Badge>
                        </div>
                    </div>
                    <div className="space-y-2">
                        <Label className="text-sm font-medium text-muted-foreground">Phone Number</Label>
                        <div className="p-3 bg-muted rounded-lg flex items-center gap-2">
                            <Phone className="h-4 w-4 text-muted-foreground" />
                            <p className="font-semibold text-foreground">{profile.phone || 'Not provided'}</p>
                        </div>
                    </div>
                </div>

                <Separator />
                <div className="flex flex-col sm:flex-row justify-between items-center pt-6 gap-3">
                    <Button onClick={onEdit} className="flex items-center gap-2 w-full sm:w-auto">
                        <Edit className="h-4 w-4" />
                        Edit Profile
                    </Button>
                    <Button variant="outline" onClick={onPasswordChange} className="flex items-center gap-2 w-full sm:w-auto">
                        <KeyRound className="h-4 w-4" />
                        Change Password
                    </Button>
                    <Button variant="outline" onClick={onProfilePictureClick} className="flex items-center gap-2 w-full sm:w-auto">
                        <UploadCloud className="h-4 w-4" />
                        Profile Picture
                    </Button>
                </div>
            </CardContent>
        </Card>
    </motion.div>
);

// ... (Existing Modals: EditProfileModal, PasswordChangeModal, ProfilePictureModal, NotificationModal)
// I will include them inline or we assume they are there.
// For stability, I will copy them back in to ensure nothing is lost.

const EditProfileModal: React.FC<{
    isOpen: boolean
    onClose: () => void
    profile: UserProfile
    mutateProfile: (data?: any, options?: boolean | MutatorOptions) => Promise<any>
    accessToken: string
}> = ({ isOpen, onClose, profile, mutateProfile, accessToken }) => {
    const [formData, setFormData] = useState({
        firstName: profile.firstName || '',
        lastName: profile.lastName || '',
        // email: profile.email, // Email not editable here
        phone: profile.phone || '',
        gender: profile.gender || ''
    });

    const [isPending, startTransition] = useTransition();
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        if (isOpen) {
            setFormData({
                firstName: profile.firstName || '',
                lastName: profile.lastName || '',
                phone: profile.phone || '',
                gender: profile.gender || ''
            });
        }
    }, [isOpen, profile]);

    const handleSave = useCallback(async () => {
        setSaving(true);
        try {
            // startTransition(() => {}); // Optional
            const updatedProfile = await apiService.updateProfile(formData, accessToken);
            await mutateProfile(); // Re-fetch
            toast.success('Profile updated successfully!');
            onClose();
        } catch (error: any) {
            toast.error(error.message || 'Failed to update profile');
        } finally {
            setSaving(false);
        }
    }, [formData, accessToken, mutateProfile, onClose]);

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[500px] border-0 shadow-strong">
                <DialogHeader className="pb-4">
                    <DialogTitle className="text-2xl font-bold flex items-center gap-3">
                        <div className="p-2 bg-gradient-primary rounded-lg">
                            <Edit className="h-5 w-5 text-primary-foreground" />
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
                                disabled={saving}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="lastName">Last Name</Label>
                            <Input
                                id="lastName"
                                value={formData.lastName}
                                onChange={e => setFormData(prev => ({ ...prev, lastName: e.target.value }))}
                                disabled={saving}
                            />
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="phone">Phone Number</Label>
                        <div className="p-2 bg-muted rounded-lg border border-input focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2">
                            <PhoneInput
                                country={'us'}
                                preferredCountries={['us', 'gb', 'ke', 'ng']}
                                enableSearch
                                value={formData.phone}
                                onChange={phone => setFormData(prev => ({ ...prev, phone }))}
                                disabled={saving}
                                inputClass="!w-full !bg-transparent !border-none !text-foreground !font-semibold focus:outline-none"
                                buttonClass="!bg-transparent !border-none"
                                containerClass="!w-full"
                                dropdownClass="!bg-background !text-foreground !z-50"
                            />
                        </div>
                    </div>
                </div>

                <div className="flex justify-end gap-3 pt-6">
                    <Button variant="outline" onClick={onClose} disabled={saving}>
                        Cancel
                    </Button>
                    <Button onClick={handleSave} disabled={saving} className="flex items-center gap-2">
                        {saving ? (
                            <>
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                Saving...
                            </>
                        ) : (
                            <>Save Changes</>
                        )}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}

const PasswordChangeModal: React.FC<{
    isOpen: boolean;
    onClose: () => void;
    accessToken: string;
}> = ({ isOpen, onClose, accessToken }) => {
    const [currentPassword, setCurrentPassword] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [confirmNewPassword, setConfirmNewPassword] = useState('');
    const [isPending, startTransition] = useTransition();

    const handleChangePassword = useCallback(async () => {
        if (newPassword !== confirmNewPassword) {
            toast.error('New password and confirmation do not match.');
            return;
        }
        try {
            startTransition(() => { });
            await apiService.changePassword({ currentPassword, newPassword, newPassword_confirmation: confirmNewPassword }, accessToken);
            toast.success('Password changed successfully!');
            onClose();
            setCurrentPassword('');
            setNewPassword('');
            setConfirmNewPassword('');
        } catch (error: any) {
            toast.error(error.message || 'Failed to change password.');
        }
    }, [currentPassword, newPassword, confirmNewPassword, accessToken, onClose]);

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[450px] border-0 shadow-strong">
                <DialogHeader className="pb-4">
                    <DialogTitle className="text-2xl font-bold flex items-center gap-3">
                        <div className="p-2 bg-gradient-primary rounded-lg">
                            <KeyRound className="h-5 w-5 text-primary-foreground" />
                        </div>
                        Change Password
                    </DialogTitle>
                </DialogHeader>

                <div className="space-y-6">
                    <div className="space-y-2">
                        <Label htmlFor="currentPassword">Current Password</Label>
                        <Input
                            id="currentPassword"
                            type="password"
                            value={currentPassword}
                            onChange={e => setCurrentPassword(e.target.value)}
                            disabled={isPending}
                        />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="newPassword">New Password</Label>
                        <Input
                            id="newPassword"
                            type="password"
                            value={newPassword}
                            onChange={e => setNewPassword(e.target.value)}
                            disabled={isPending}
                        />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="confirmNewPassword">Confirm New Password</Label>
                        <Input
                            id="confirmNewPassword"
                            type="password"
                            value={confirmNewPassword}
                            onChange={e => setConfirmNewPassword(e.target.value)}
                            disabled={isPending}
                        />
                    </div>
                </div>

                <div className="flex justify-end gap-3 pt-6">
                    <Button variant="outline" onClick={onClose} disabled={isPending}>
                        Cancel
                    </Button>
                    <Button onClick={handleChangePassword} disabled={isPending || !currentPassword || !newPassword || !confirmNewPassword} className="flex items-center gap-2">
                        {isPending && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                        Update Password
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
};

const ProfilePictureModal: React.FC<{
    isOpen: boolean;
    onClose: () => void;
    profile: UserProfile;
    mutateProfile: (data?: any, options?: boolean | MutatorOptions) => Promise<any>;
    accessToken: string
}> = ({ isOpen, onClose, profile, mutateProfile, accessToken }) => {
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [isPending, startTransition] = useTransition();
    const fileInputRef = useRef<HTMLInputElement>(null);

    const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        if (event.target.files && event.target.files[0]) {
            setSelectedFile(event.target.files[0]);
        } else {
            setSelectedFile(null);
        }
    };

    const handleUpload = useCallback(async () => {
        if (!selectedFile) {
            toast.info('Please select a file to upload.');
            return;
        }
        try {
            startTransition(() => { });
            await apiService.uploadProfilePicture(selectedFile, accessToken);
            await mutateProfile();
            toast.success('Profile picture updated successfully!');
            onClose();
            setSelectedFile(null);
        } catch (error: any) {
            toast.error(error.message || 'Failed to upload profile picture.');
        }
    }, [selectedFile, accessToken, mutateProfile, onClose]);

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[450px] border-0 shadow-strong">
                <DialogHeader className="pb-4">
                    <DialogTitle className="text-2xl font-bold flex items-center gap-3">
                        <div className="p-2 bg-gradient-primary rounded-lg">
                            <Camera className="h-5 w-5 text-primary-foreground" />
                        </div>
                        Update Profile Picture
                    </DialogTitle>
                </DialogHeader>

                <div className="space-y-6 flex flex-col items-center">
                    <Avatar className="h-32 w-32 border-4 border-primary/20 shadow-md">
                        <AvatarImage src={selectedFile ? URL.createObjectURL(selectedFile) : profile.imageUrl ?? undefined} alt="Profile Preview" className="object-cover" />
                        <AvatarFallback className="text-xl font-bold bg-muted text-muted-foreground">
                            {getInitials(profile.firstName, profile.lastName)}
                        </AvatarFallback>
                    </Avatar>
                    <Label htmlFor="picture-upload" className="cursor-pointer bg-accent hover:bg-accent/90 text-accent-foreground font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition-colors">
                        <UploadCloud className="h-4 w-4" />
                        {selectedFile ? selectedFile.name : 'Choose File'}
                        <Input
                            id="picture-upload"
                            type="file"
                            accept="image/*"
                            onChange={handleFileChange}
                            className="hidden"
                            disabled={isPending}
                            ref={fileInputRef}
                        />
                    </Label>
                </div>

                <div className="flex justify-end gap-3 pt-6">
                    <Button variant="outline" onClick={onClose} disabled={isPending}>
                        Cancel
                    </Button>
                    <Button onClick={handleUpload} disabled={isPending || !selectedFile} className="flex items-center gap-2">
                        {isPending && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                        Upload Picture
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
};

const UserProfilePage: React.FC = () => {
    const { data: session } = useSession();
    const accessToken = session?.accessToken || '';

    const { data: profile, error, isLoading, mutate } = useSWR<UserProfile>(
        accessToken ? ['/api/profile', accessToken] : null,
        ([_url, token]: [string, string]) => apiService.getProfile(token),
        {
            revalidateOnFocus: false,
            revalidateOnReconnect: false,
        }
    );

    const [isEditProfileModalOpen, setIsEditProfileModalOpen] = useState(false);
    const [isPasswordModalOpen, setIsPasswordModalOpen] = useState(false);
    const [isProfilePictureModalOpen, setIsProfilePictureModalOpen] = useState(false);

    if (isLoading) {
        return (
            <div className="flex justify-center items-center h-[50vh]">
                <Loader2 className="h-10 w-10 animate-spin text-primary" />
            </div>
        );
    }

    if (error || !profile) {
        return (
            <div className="flex flex-col items-center justify-center h-[50vh] space-y-4">
                <AlertTriangle className="h-12 w-12 text-destructive" />
                <h2 className="text-2xl font-bold text-foreground">Failed to load profile</h2>
                <p className="text-muted-foreground">Please try refreshing the page or contact support.</p>
                <Button onClick={() => window.location.reload()}>Retry</Button>
            </div>
        );
    }

    return (
        <div className="container mx-auto p-6 max-w-5xl space-y-8 animate-in fade-in duration-500">
            <motion.div
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5 }}
                className="flex items-center justify-between"
            >
                <div className="space-y-1">
                    <h1 className="text-3xl font-bold tracking-tight text-foreground">My Account</h1>
                    <p className="text-muted-foreground">
                        Manage your personal information, security, and role permissions.
                    </p>
                </div>
            </motion.div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Left Column: Profile Card */}
                <div className="lg:col-span-1 space-y-6">
                    <motion.div
                        initial={{ opacity: 0, scale: 0.95 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ duration: 0.5 }}
                    >
                        <Card className="relative overflow-hidden bg-gradient-accent border-0 shadow-medium p-0">
                            <div className="absolute inset-0 bg-gradient-primary opacity-5" />
                            <CardContent className="relative p-8 text-center flex flex-col items-center space-y-6">
                                <Avatar
                                    className="relative h-32 w-32 border-4 border-primary/20 shadow-strong cursor-pointer"
                                    onClick={() => setIsProfilePictureModalOpen(true)}
                                >
                                    <AvatarImage src={profile.imageUrl ?? undefined} alt={profile.firstName} className="object-cover" />
                                    <AvatarFallback className="text-3xl font-bold bg-gradient-primary-700">
                                        {getInitials(profile.firstName, profile.lastName)}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="space-y-2">
                                    <h2 className="text-2xl font-bold text-foreground">{profile.firstName} {profile.lastName}</h2>
                                    <p className="text-muted-foreground">{profile.thirdParty?.tradingName || 'No Company'}</p>
                                    <Badge variant="secondary" className="bg-blue-500/10 text-blue-600">
                                        {profile.thirdParty?.businessType || 'User'}
                                    </Badge>
                                </div>
                            </CardContent>
                        </Card>
                    </motion.div>
                </div>

                {/* Right Column: Details & Settings */}
                <div className="lg:col-span-2 space-y-6">
                    <ProfileDetailsCard
                        profile={profile}
                        onEdit={() => setIsEditProfileModalOpen(true)}
                        onPasswordChange={() => setIsPasswordModalOpen(true)}
                        onProfilePictureClick={() => setIsProfilePictureModalOpen(true)}
                    />

                    <RolesCard
                        profile={profile}
                        accessToken={accessToken}
                        mutateProfile={mutate}
                    />
                </div>
            </div>

            <EditProfileModal
                isOpen={isEditProfileModalOpen}
                onClose={() => setIsEditProfileModalOpen(false)}
                profile={profile}
                mutateProfile={mutate}
                accessToken={accessToken}
            />

            <PasswordChangeModal
                isOpen={isPasswordModalOpen}
                onClose={() => setIsPasswordModalOpen(false)}
                accessToken={accessToken}
            />

            <ProfilePictureModal
                isOpen={isProfilePictureModalOpen}
                onClose={() => setIsProfilePictureModalOpen(false)}
                profile={profile}
                mutateProfile={mutate}
                accessToken={accessToken}
            />
        </div>
    );
};

export default UserProfilePage;
