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
    Settings,
    Mail,
    Phone,
    BadgeCheckIcon,
    UploadCloud,
    X,
    Image as ImageIcon,
} from 'lucide-react';
import { Button } from '@/components/common/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/common/card';
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
import { Separator } from '@/components/common/separator';
import { Badge } from '@/components/common/badge';
import { UserProfile } from '@/types/next-auth';
import { useSession } from 'next-auth/react';
import { getInitials } from '@/lib/utils';
import { apiService } from '@/lib/api/profile';
import { motion, AnimatePresence } from 'framer-motion';

const ProfileCard: React.FC<{
    profile: UserProfile;
    onProfilePictureClick: () => void;
    onEditProfile: () => void;
}> = ({ profile, onProfilePictureClick, onEditProfile }) => {
    const displayName = profile.fullName || `${profile.firstName || ''} ${profile.lastName || ''}`.trim();

    return (
        <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.1 }}
        >
            <Card className="relative overflow-hidden bg-gradient-accent border-0 shadow-medium p-0">
                <div className="absolute inset-0 bg-gradient-primary opacity-5" />
                <CardHeader className="relative z-10 p-4 pb-0 flex flex-row justify-end">
                    <motion.div whileHover={{ scale: 1.1 }} whileTap={{ scale: 0.9 }}>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="text-primary-foreground/80 hover:text-primary-foreground"
                            onClick={onEditProfile}
                            aria-label="Edit profile"
                        >
                            <Edit className="h-5 w-5" />
                        </Button>
                    </motion.div>
                </CardHeader>
                <CardContent className="relative p-8 pt-0 text-center flex flex-col items-center space-y-6">
                    <motion.div
                        className="relative group"
                        whileHover={{ scale: 1.05 }}
                        whileTap={{ scale: 0.98 }}
                    >
                        <div className="absolute -inset-2 bg-gradient-primary rounded-full opacity-20 group-hover:opacity-40 transition-opacity animate-glow-pulse" />
                        <Avatar
                            className="relative h-32 w-32 border-4 border-primary/20 shadow-strong cursor-pointer"
                            onClick={onProfilePictureClick}
                        >
                            <AvatarImage src={profile.imageUrl ?? undefined} alt={displayName} className="object-cover" />
                            <AvatarFallback className="text-3xl font-bold bg-gradient-primary-700">
                                {getInitials(profile.firstName, profile.lastName)}
                            </AvatarFallback>
                        </Avatar>
                        <Button
                            size="icon"
                            variant="secondary"
                            className="absolute -bottom-2 -right-2 h-10 w-10 rounded-full shadow-medium transition-all group-hover:scale-110"
                            onClick={onProfilePictureClick}
                        >
                            <Camera className="h-4 w-4" />
                        </Button>
                    </motion.div>

                    <div className="space-y-3">
                        <h2 className="text-3xl font-bold text-foreground">{displayName}</h2>
                        <p className="text-muted-foreground text-lg">{profile.email}</p>
                        {profile.emailVerifiedOn && (
                            <motion.div
                                initial={{ opacity: 0, scale: 0.8 }}
                                animate={{ opacity: 1, scale: 1 }}
                                transition={{ duration: 0.3, delay: 0.2 }}
                            >
                                <Badge variant="secondary" className="bg-blue-500 text-white dark:bg-blue-600">
                                    <BadgeCheckIcon className="h-3 w-3 mr-1" />
                                    Verified
                                </Badge>
                            </motion.div>
                        )}
                    </div>
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
                            <p className="font-semibold text-foreground">{profile.firstName}</p>
                        </div>
                    </div>
                    <div className="space-y-2">
                        <Label className="text-sm font-medium text-muted-foreground">Last Name</Label>
                        <div className="p-3 bg-muted rounded-lg">
                            <p className="font-semibold text-foreground">{profile.lastName}</p>
                        </div>
                    </div>
                    <div className="space-y-2">
                        <Label className="text-sm font-medium text-muted-foreground">Email Address</Label>
                        <div className="p-3 bg-muted rounded-lg flex items-center gap-2">
                            <Mail className="h-4 w-4 text-muted-foreground" />
                            <p className="font-semibold text-foreground">{profile.email}</p>
                        </div>
                    </div>
                    <div className="space-y-2">
                        <Label className="text-sm font-medium text-muted-foreground">Phone Number</Label>
                        <div className="p-3 bg-muted rounded-lg flex items-center gap-2">
                            <Phone className="h-4 w-4 text-muted-foreground" />
                            <p className="font-semibold text-foreground">{profile.phone || 'Not provided'}</p>
                        </div>
                    </div>
                    {profile.gender && (
                        <div className="space-y-2">
                            <Label className="text-sm font-medium text-muted-foreground">Gender</Label>
                            <div className="p-3 bg-muted rounded-lg">
                                <p className="font-semibold text-foreground">{profile.gender}</p>
                            </div>
                        </div>
                    )}
                    <div className="space-y-2">
                        <Label className="text-sm font-medium text-muted-foreground">Password</Label>
                        <div className="p-3 bg-muted rounded-lg flex items-center gap-2">
                            <Shield className="h-4 w-4 text-muted-foreground" />
                            <p className="font-semibold text-foreground">••••••••</p>
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

const NotificationCard: React.FC<{
    profile: UserProfile;
    onEdit: () => void;
}> = ({ profile, onEdit }) => (
    <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, delay: 0.3 }}
    >
        <Card className="border-0 shadow-medium bg-card">
            <CardHeader className="pb-4">
                <CardTitle className="flex items-center gap-3 text-2xl font-bold">
                    <div className="p-2 bg-gradient-primary rounded-lg">
                        <Bell className="h-5 w-5 text-primary-foreground" />
                    </div>
                    Notification Preferences
                </CardTitle>
            </CardHeader>
            <Separator />
            <CardContent className="pt-6 space-y-6">
                <div className="space-y-4">
                    <div className="flex items-start space-x-3 p-4 bg-muted rounded-lg">
                        <Checkbox checked={profile.receiveSmsNotifications || false} disabled className="mt-1" />
                        <div className="space-y-1">
                            <p className="font-medium text-foreground">SMS Notifications</p>
                            <p className="text-sm text-muted-foreground">Receive urgent alerts and updates via text message</p>
                        </div>
                    </div>

                    <div className="flex items-start space-x-3 p-4 bg-muted rounded-lg">
                        <Checkbox checked={profile.receiveNewsletter || false} disabled className="mt-1" />
                        <div className="space-y-1">
                            <p className="font-medium text-foreground">Newsletter Subscription</p>
                            <p className="text-sm text-muted-foreground">Stay informed with weekly insights and industry updates</p>
                        </div>
                    </div>
                </div>

                <Separator />
                <Button onClick={onEdit} className="flex items-center gap-2">
                    <Settings className="h-4 w-4" />
                    Manage Preferences
                </Button>
            </CardContent>
        </Card>
    </motion.div>
);

const DangerZoneCard: React.FC<{ accessToken: string }> = ({ accessToken }) => {
    const [isPending, startTransition] = useTransition();
    const [password, setPassword] = useState('');

    const handleDelete = () => {
        startTransition(() => {
            toast.promise(apiService.deleteAccount(password, accessToken), {
                loading: 'Deleting account...',
                success: () => {
                    return 'Account deleted successfully.';
                },
                error: err => {
                    const errorMessage = err instanceof Error ? err.message : 'Deletion failed.';
                    return errorMessage;
                },
            });
        });
    };

    return (
        <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.4 }}
        >
            <Card className="border-destructive/30 bg-destructive-light/10 shadow-medium">
                <CardHeader className="pb-4">
                    <CardTitle className="flex items-center gap-3 text-2xl font-bold text-destructive">
                        <div className="p-2 bg-destructive/10 rounded-lg">
                            <AlertTriangle className="h-5 w-5 text-destructive" />
                        </div>
                        Danger Zone
                    </CardTitle>
                </CardHeader>
                <Separator className="bg-destructive/20" />
                <CardContent className="pt-6">
                    <p className="text-muted-foreground mb-6 text-sm">
                        This action is permanent and cannot be undone. All your data will be permanently deleted.
                    </p>

                    <AlertDialog>
                        <AlertDialogTrigger asChild>
                            <Button variant="destructive" className="flex items-center gap-2">
                                <Trash2 className="h-4 w-4" />
                                Delete Account
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent className="border-0 shadow-strong">
                            <AlertDialogHeader>
                                <AlertDialogTitle className="text-xl font-bold text-destructive">Delete Account</AlertDialogTitle>
                                <AlertDialogDescription className="text-muted-foreground">
                                    This will permanently delete your account and all associated data. This action cannot be undone.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <div className="space-y-4 py-4">
                                <div className="space-y-2">
                                    <Label htmlFor="password-confirm">Confirm with your password</Label>
                                    <Input
                                        id="password-confirm"
                                        type="password"
                                        value={password}
                                        onChange={e => setPassword(e.target.value)}
                                        placeholder="Enter your password"
                                    />
                                </div>
                                <div className="flex items-center gap-2 p-3 bg-destructive-light rounded-lg">
                                    <Info className="h-4 w-4 text-destructive" />
                                    <p className="text-sm text-destructive">This action is irreversible</p>
                                </div>
                            </div>
                            <AlertDialogFooter>
                                <AlertDialogCancel disabled={isPending}>Cancel</AlertDialogCancel>
                                <AlertDialogAction
                                    onClick={handleDelete}
                                    disabled={isPending || !password}
                                    className="bg-destructive hover:bg-destructive/90"
                                >
                                    {isPending && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                                    Delete Account
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </CardContent>
            </Card>
        </motion.div>
    );
};

const EditProfileModal: React.FC<{
    isOpen: boolean
    onClose: () => void
    profile: UserProfile
    mutateProfile: (data?: any, options?: boolean | MutatorOptions) => Promise<any>
    accessToken: string
}> = ({ isOpen, onClose, profile, mutateProfile, accessToken }) => {
    const [formData, setFormData] = useState({
        firstName: profile.firstName,
        lastName: profile.lastName,
        email: profile.email,
        phone: profile.phone || '',
        gender: profile.gender || ''
    })

    const [isPending, startTransition] = useTransition()

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
        try {
            startTransition(() => { })
            const updatedProfile = await apiService.updateProfile(formData, accessToken)
            await mutateProfile(updatedProfile, { revalidate: false })
            toast.success('Profile updated successfully!')
            onClose()
        } catch (error: any) {
            toast.error(error.message || 'Failed to update profile')
        }
    }, [formData, accessToken, mutateProfile, onClose])

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
                                disabled={isPending}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="lastName">Last Name</Label>
                            <Input
                                id="lastName"
                                value={formData.lastName}
                                onChange={e => setFormData(prev => ({ ...prev, lastName: e.target.value }))}
                                disabled={isPending}
                            />
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="email">Email Address</Label>
                        <Input
                            id="email"
                            type="email"
                            value={formData.email}
                            onChange={e => setFormData(prev => ({ ...prev, email: e.target.value }))}
                            disabled={isPending}
                        />
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
                                disabled={isPending}
                                inputClass="!w-full !bg-transparent !border-none !text-foreground !font-semibold focus:outline-none"
                                buttonClass="!bg-transparent !border-none"
                                containerClass="!w-full"
                                dropdownClass="!bg-background !text-foreground !z-50"
                            />
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="gender">Gender</Label>
                        <Input
                            id="gender"
                            value={formData.gender}
                            onChange={e => setFormData(prev => ({ ...prev, gender: e.target.value }))}
                            disabled={isPending}
                        />
                    </div>
                </div>

                <div className="flex justify-end gap-3 pt-6">
                    <Button variant="outline" onClick={onClose} disabled={isPending}>
                        Cancel
                    </Button>
                    <Button onClick={handleSave} disabled={isPending} className="flex items-center gap-2">
                        {isPending && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                        Save Changes
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    )
}

const NotificationModal: React.FC<{
    isOpen: boolean;
    onClose: () => void;
    profile: UserProfile;
    mutateProfile: (data?: any, options?: boolean | MutatorOptions) => Promise<any>;
    accessToken: string;
}> = ({ isOpen, onClose, profile, mutateProfile, accessToken }) => {
    const [smsEnabled, setSmsEnabled] = useState(profile.receiveSmsNotifications || false);
    const [newsletterEnabled, setNewsletterEnabled] = useState(profile.receiveNewsletter || false);
    const [isPending, startTransition] = useTransition();

    useEffect(() => {
        if (isOpen) {
            setSmsEnabled(profile.receiveSmsNotifications || false);
            setNewsletterEnabled(profile.receiveNewsletter || false);
        }
    }, [isOpen, profile]);

    const handleSave = useCallback(async () => {
        try {
            startTransition(() => { });
            const updatedSettings = await apiService.updateNotifications(
                { receiveSmsNotifications: smsEnabled, receiveNewsletter: newsletterEnabled },
                accessToken
            );
            await mutateProfile(
                (prevProfile: UserProfile | undefined) => {
                    if (!prevProfile) return updatedSettings;
                    return { ...prevProfile, ...updatedSettings };
                },
                { revalidate: false }
            );
            toast.success('Notification settings updated successfully!');
            onClose();
        } catch (error: any) {
            toast.error(error.message || 'Failed to save settings. Please try again.');
        }
    }, [smsEnabled, newsletterEnabled, accessToken, mutateProfile, onClose]);

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[450px] border-0 shadow-strong">
                <DialogHeader className="pb-4">
                    <DialogTitle className="text-2xl font-bold flex items-center gap-3">
                        <div className="p-2 bg-gradient-primary rounded-lg">
                            <Bell className="h-5 w-5 text-primary-foreground" />
                        </div>
                        Notification Settings
                    </DialogTitle>
                </DialogHeader>

                <div className="space-y-6">
                    <div className="flex items-start space-x-3 p-4 bg-muted rounded-lg">
                        <Checkbox
                            checked={smsEnabled}
                            onCheckedChange={checked => setSmsEnabled(Boolean(checked))}
                            className="mt-1"
                            disabled={isPending}
                        />
                        <div className="space-y-1">
                            <p className="font-medium text-foreground">SMS Notifications</p>
                            <p className="text-sm text-muted-foreground">Receive urgent alerts and updates via text message</p>
                        </div>
                    </div>

                    <div className="flex items-start space-x-3 p-4 bg-muted rounded-lg">
                        <Checkbox
                            checked={newsletterEnabled}
                            onCheckedChange={checked => setNewsletterEnabled(Boolean(checked))}
                            className="mt-1"
                            disabled={isPending}
                        />
                        <div className="space-y-1">
                            <p className="font-medium text-foreground">Newsletter Subscription</p>
                            <p className="text-sm text-muted-foreground">Stay informed with weekly insights and industry updates</p>
                        </div>
                    </div>
                </div>

                <div className="flex justify-end gap-3 pt-6">
                    <Button variant="outline" onClick={onClose} disabled={isPending}>
                        Cancel
                    </Button>
                    <Button onClick={handleSave} disabled={isPending} className="flex items-center gap-2">
                        {isPending && <Loader2 className="h-4 w-4 animate-spin" />}
                        Save Preferences
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
};

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
            await apiService.changePassword({ currentPassword, newPassword }, accessToken);
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
            const result = await apiService.uploadProfilePicture(selectedFile, accessToken);
            await mutateProfile((prevProfile: UserProfile | undefined) => {
                if (!prevProfile) return prevProfile;
                return { ...prevProfile, imageUrl: result.imageUrl };
            }, { revalidate: false });
            toast.success('Profile picture updated successfully!');
            onClose();
            setSelectedFile(null);
            if (fileInputRef.current) {
                fileInputRef.current.value = '';
            }
        } catch (error: any) {
            toast.error(error.message || 'Failed to upload profile picture.');
        }
    }, [selectedFile, accessToken, mutateProfile, onClose]);

    const removeSelectedFile = useCallback(() => {
        setSelectedFile(null);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    }, []);

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
                    {selectedFile && (
                        <Button variant="ghost" size="icon" onClick={removeSelectedFile} className="mt-2">
                            <X className="h-4 w-4" />
                        </Button>
                    )}
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
    const [isNotificationModalOpen, setIsNotificationModalOpen] = useState(false);
    const [isPasswordChangeModalOpen, setIsPasswordChangeModalOpen] = useState(false);
    const [isProfilePictureModalOpen, setIsProfilePictureModalOpen] = useState(false);

    const openEditProfileModal = useCallback(() => setIsEditProfileModalOpen(true), []);
    const closeEditProfileModal = useCallback(() => setIsEditProfileModalOpen(false), []);

    const openNotificationModal = useCallback(() => setIsNotificationModalOpen(true), []);
    const closeNotificationModal = useCallback(() => setIsNotificationModalOpen(false), []);

    const openPasswordChangeModal = useCallback(() => setIsPasswordChangeModalOpen(true), []);
    const closePasswordChangeModal = useCallback(() => setIsPasswordChangeModalOpen(false), []);

    const openProfilePictureModal = useCallback(() => setIsProfilePictureModalOpen(true), []);
    const closeProfilePictureModal = useCallback(() => setIsProfilePictureModalOpen(false), []);

    if (isLoading) {
        return (
            <div className="flex justify-center items-center min-h-[calc(100vh-200px)]">
                <Loader2 className="h-10 w-10 animate-spin text-primary" />
            </div>
        );
    }

    if (error || !profile) {
        return (
            <div className="flex flex-col items-center justify-center min-h-[calc(100vh-200px)] text-center p-4">
                <AlertTriangle className="h-16 w-16 text-destructive mb-4" />
                <h2 className="text-2xl font-bold text-foreground">Failed to load profile</h2>
                <p className="text-muted-foreground mt-2">Please try refreshing the page or contact support.</p>
                <Button onClick={() => window.location.reload()} className="mt-6">
                    Retry
                </Button>
            </div>
        );
    }

    return (
        <AnimatePresence mode="wait">
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -20 }}
                transition={{ duration: 0.3 }}
                className="container mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8"
            >
                {/* <h1 className="text-4xl font-extrabold text-center text-foreground mb-10">
                    My Profile
                </h1> */}

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div className="lg:col-span-1">
                        <ProfileCard
                            profile={profile}
                            onProfilePictureClick={openProfilePictureModal}
                            onEditProfile={openEditProfileModal}
                        />
                    </div>
                    <div className="lg:col-span-2 space-y-8">
                        <ProfileDetailsCard
                            profile={profile}
                            onEdit={openEditProfileModal}
                            onPasswordChange={openPasswordChangeModal}
                            onProfilePictureClick={openProfilePictureModal}
                        />
                        <NotificationCard
                            profile={profile}
                            onEdit={openNotificationModal}
                        />
                        <DangerZoneCard accessToken={accessToken} />
                    </div>
                </div>

                <EditProfileModal
                    isOpen={isEditProfileModalOpen}
                    onClose={closeEditProfileModal}
                    profile={profile}
                    mutateProfile={mutate}
                    accessToken={accessToken}
                />
                <NotificationModal
                    isOpen={isNotificationModalOpen}
                    onClose={closeNotificationModal}
                    profile={profile}
                    mutateProfile={mutate}
                    accessToken={accessToken}
                />
                <PasswordChangeModal
                    isOpen={isPasswordChangeModalOpen}
                    onClose={closePasswordChangeModal}
                    accessToken={accessToken}
                />
                <ProfilePictureModal
                    isOpen={isProfilePictureModalOpen}
                    onClose={closeProfilePictureModal}
                    profile={profile}
                    mutateProfile={mutate}
                    accessToken={accessToken}
                />
            </motion.div>
        </AnimatePresence>
    );
};

export default UserProfilePage;
