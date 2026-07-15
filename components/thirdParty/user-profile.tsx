'use client';

import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { User, Package, Home, Plus, Check, ChevronDown, X } from 'lucide-react';

type ProfileType = 'tenant' | 'supplier';

interface Profile {
    id: string;
    name: string;
    type: ProfileType;
    email?: string;
    active: boolean;
}

interface ProfileSwitcherProps {
    profiles: Profile[];
    activeProfile: Profile;
    onSwitch: (profile: Profile) => void;
}

interface AddProfileModalProps {
    isOpen: boolean;
    onClose: () => void;
    onAdd: (profile: { name: string; type: ProfileType; email?: string }) => void;
}

const PROFILE_TYPES = [
    { value: 'tenant' as const, label: 'Tenant', icon: Home, color: 'text-blue-500', bg: 'bg-blue-500/10' },
    { value: 'supplier' as const, label: 'Supplier', icon: Package, color: 'text-green-500', bg: 'bg-green-500/10' },
];

const MOCK_PROFILES: Profile[] = [
    { id: '1', name: 'Personal Account', type: 'tenant', email: 'user@example.com', active: true },
    { id: '2', name: 'ABC Suppliers Ltd', type: 'supplier', email: 'contact@abcsuppliers.com', active: false },
];

const modalVariants = {
    hidden: { opacity: 0, scale: 0.95 },
    visible: {
        opacity: 1,
        scale: 1,
        transition: { type: 'spring' as const, stiffness: 500, damping: 35 },
    },
    exit: {
        opacity: 0,
        scale: 0.95,
        transition: { duration: 0.2 },
    },
};

const itemVariants = {
    hidden: { x: -12, opacity: 0 },
    visible: {
        x: 0,
        opacity: 1,
        transition: { type: 'spring' as const, stiffness: 500, damping: 35 },
    },
};

function ProfileSwitcher({ profiles, activeProfile, onSwitch }: ProfileSwitcherProps) {
    const [isOpen, setIsOpen] = useState(false);
    const activeTypeConfig = PROFILE_TYPES.find(t => t.value === activeProfile.type);
    const ActiveIcon = activeTypeConfig?.icon || User;

    return (
        <div className="relative">
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="flex items-center gap-2.5 px-3 py-2 rounded-lg border border-border/50 bg-background hover:bg-muted/50 transition-all text-sm w-full sm:w-auto"
            >
                <div className={`rounded-md p-1.5 ${activeTypeConfig?.bg}`}>
                    <ActiveIcon className={`h-3.5 w-3.5 ${activeTypeConfig?.color}`} />
                </div>
                <div className="flex-1 text-left min-w-0">
                    <div className="font-medium text-foreground leading-tight truncate">
                        {activeProfile.name}
                    </div>
                    <div className="text-xs text-muted-foreground leading-tight">
                        {activeTypeConfig?.label}
                    </div>
                </div>
                <ChevronDown className={`h-4 w-4 text-muted-foreground transition-transform ${isOpen ? 'rotate-180' : ''}`} />
            </button>

            <AnimatePresence>
                {isOpen && (
                    <>
                        <div
                            className="fixed inset-0 z-40"
                            onClick={() => setIsOpen(false)}
                        />
                        <motion.div
                            initial={{ opacity: 0, y: -8 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -8 }}
                            transition={{ duration: 0.2 }}
                            className="absolute top-full left-0 right-0 mt-2 p-1.5 rounded-lg border border-border/50 bg-card shadow-lg z-50 min-w-[280px]"
                        >
                            <div className="space-y-0.5">
                                {profiles.map((profile) => {
                                    const typeConfig = PROFILE_TYPES.find(t => t.value === profile.type);
                                    const Icon = typeConfig?.icon || User;
                                    const isActive = profile.id === activeProfile.id;

                                    return (
                                        <button
                                            key={profile.id}
                                            onClick={() => {
                                                onSwitch(profile);
                                                setIsOpen(false);
                                            }}
                                            className={`
                                                w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md text-sm
                                                transition-colors ${isActive
                                                    ? 'bg-primary/10 text-primary'
                                                    : 'hover:bg-muted/50 text-foreground'
                                                }
                                            `}
                                        >
                                            <div className={`rounded-md p-1.5 ${typeConfig?.bg}`}>
                                                <Icon className={`h-3.5 w-3.5 ${typeConfig?.color}`} />
                                            </div>
                                            <div className="flex-1 text-left min-w-0">
                                                <div className="font-medium leading-tight truncate">
                                                    {profile.name}
                                                </div>
                                                <div className="text-xs text-muted-foreground leading-tight truncate">
                                                    {profile.email || typeConfig?.label}
                                                </div>
                                            </div>
                                            {isActive && <Check className="h-4 w-4 shrink-0" />}
                                        </button>
                                    );
                                })}
                            </div>
                        </motion.div>
                    </>
                )}
            </AnimatePresence>
        </div>
    );
}

function AddProfileModal({ isOpen, onClose, onAdd }: AddProfileModalProps) {
    const [selectedType, setSelectedType] = useState<ProfileType>('tenant');
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');

    const handleSubmit = () => {
        if (name.trim()) {
            onAdd({ name: name.trim(), type: selectedType, email: email.trim() || undefined });
            setName('');
            setEmail('');
            setSelectedType('tenant');
            onClose();
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0 }}
                onClick={onClose}
                className="absolute inset-0 bg-black/50 backdrop-blur-sm"
            />

            <motion.div
                variants={modalVariants}
                initial="hidden"
                animate="visible"
                exit="exit"
                onClick={(e) => e.stopPropagation()}
                className="relative w-full max-w-md bg-card border border-border/50 rounded-lg p-5 shadow-xl"
            >
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-lg font-semibold text-foreground">Add New Profile</h3>
                    <button
                        onClick={onClose}
                        className="p-1 rounded-md hover:bg-muted transition-colors"
                    >
                        <X className="h-4 w-4 text-muted-foreground" />
                    </button>
                </div>

                <div className="space-y-4">
                    <div>
                        <label className="text-sm font-medium text-foreground mb-2 block">
                            Profile Type
                        </label>
                        <div className="grid grid-cols-2 gap-2">
                            {PROFILE_TYPES.map((type) => {
                                const Icon = type.icon;
                                const isSelected = selectedType === type.value;

                                return (
                                    <button
                                        key={type.value}
                                        onClick={() => setSelectedType(type.value)}
                                        className={`
                                            flex items-center gap-2 px-3 py-2.5 rounded-lg border text-sm font-medium
                                            transition-all ${isSelected
                                                ? 'bg-primary/10 border-primary text-primary'
                                                : 'border-border/50 hover:bg-muted/50'
                                            }
                                        `}
                                    >
                                        <Icon className="h-4 w-4" />
                                        {type.label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    <div>
                        <label className="text-sm font-medium text-foreground mb-2 block">
                            Profile Name
                        </label>
                        <input
                            type="text"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            placeholder="e.g., ABC Suppliers Ltd"
                            className="w-full px-3 py-2 text-sm rounded-lg border border-border/50 bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                        />
                    </div>

                    <div>
                        <label className="text-sm font-medium text-foreground mb-2 block">
                            Email (Optional)
                        </label>
                        <input
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            placeholder="contact@example.com"
                            className="w-full px-3 py-2 text-sm rounded-lg border border-border/50 bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                        />
                    </div>

                    <div className="flex gap-2 pt-2">
                        <button
                            onClick={onClose}
                            className="flex-1 px-4 py-2 text-sm font-medium rounded-lg border border-border/50 hover:bg-muted/50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            onClick={handleSubmit}
                            className="flex-1 px-4 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
                        >
                            Add Profile
                        </button>
                    </div>
                </div>
            </motion.div>
        </div>
    );
}

export default function ProfileManagement() {
    const [profiles, setProfiles] = useState<Profile[]>(MOCK_PROFILES);
    const [activeProfile, setActiveProfile] = useState<Profile>(MOCK_PROFILES[0]);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const handleSwitch = (profile: Profile) => {
        setProfiles(prev => prev.map(p => ({ ...p, active: p.id === profile.id })));
        setActiveProfile(profile);
    };

    const handleAdd = (newProfile: { name: string; type: ProfileType; email?: string }) => {
        const profile: Profile = {
            ...newProfile,
            id: Date.now().toString(),
            active: false,
        };
        setProfiles(prev => [...prev, profile]);
    };

    const handleDelete = (id: string) => {
        if (profiles.length === 1) return;
        setProfiles(prev => prev.filter(p => p.id !== id));
        if (activeProfile.id === id) {
            const newActive = profiles.find(p => p.id !== id);
            if (newActive) {
                setActiveProfile(newActive);
            }
        }
    };

    return (
        <div className="w-full space-y-4">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-border/40">
                <div>
                    <h2 className="text-xl font-semibold tracking-tight">Profiles</h2>
                    <p className="text-sm text-muted-foreground mt-0.5">
                        Manage and switch between your profiles
                    </p>
                </div>
                <ProfileSwitcher
                    profiles={profiles}
                    activeProfile={activeProfile}
                    onSwitch={handleSwitch}
                />
            </div>

            <div className="space-y-2.5">
                {profiles.map((profile) => {
                    const typeConfig = PROFILE_TYPES.find(t => t.value === profile.type);
                    const Icon = typeConfig?.icon || User;

                    return (
                        <motion.div
                            key={profile.id}
                            variants={itemVariants}
                            initial="hidden"
                            animate="visible"
                            className="p-3.5 border rounded-lg bg-card hover:border-primary/30 transition-all"
                        >
                            <div className="flex items-center gap-3">
                                <div className={`rounded-md p-2 ${typeConfig?.bg}`}>
                                    <Icon className={`h-5 w-5 ${typeConfig?.color}`} />
                                </div>
                                <div className="flex-1 min-w-0">
                                    <div className="flex items-center gap-2 flex-wrap">
                                        <h3 className="font-medium text-foreground">{profile.name}</h3>
                                        {profile.active && (
                                            <span className="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary font-medium">
                                                Active
                                            </span>
                                        )}
                                    </div>
                                    <div className="flex items-center gap-2 mt-0.5 flex-wrap">
                                        <p className="text-xs text-muted-foreground">{typeConfig?.label}</p>
                                        {profile.email && (
                                            <>
                                                <span className="text-xs text-muted-foreground">•</span>
                                                <p className="text-xs text-muted-foreground truncate">{profile.email}</p>
                                            </>
                                        )}
                                    </div>
                                </div>
                                <div className="flex gap-1 flex-shrink-0">
                                    {!profile.active && (
                                        <button
                                            onClick={() => handleSwitch(profile)}
                                            className="px-3 py-1.5 text-xs font-medium rounded-md border border-border/50 hover:bg-muted/50 transition-colors"
                                        >
                                            Switch
                                        </button>
                                    )}
                                    {profiles.length > 1 && (
                                        <button
                                            onClick={() => handleDelete(profile.id)}
                                            className="px-3 py-1.5 text-xs font-medium rounded-md text-destructive hover:bg-destructive/10 transition-colors"
                                        >
                                            Delete
                                        </button>
                                    )}
                                </div>
                            </div>
                        </motion.div>
                    );
                })}

                <button
                    onClick={() => setIsModalOpen(true)}
                    className="w-full p-3.5 border-2 border-dashed rounded-lg hover:border-primary/50 hover:bg-primary/5 transition-all group"
                >
                    <div className="flex items-center justify-center gap-2 text-sm font-medium text-muted-foreground group-hover:text-primary">
                        <Plus className="h-4 w-4" />
                        Add New Profile
                    </div>
                </button>
            </div>

            <AnimatePresence>
                {isModalOpen && (
                    <AddProfileModal
                        isOpen={isModalOpen}
                        onClose={() => setIsModalOpen(false)}
                        onAdd={handleAdd}
                    />
                )}
            </AnimatePresence>
        </div>
    );
}
