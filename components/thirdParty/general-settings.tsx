'use client'

import { useState } from 'react'
import { motion, Variants } from 'framer-motion'
import { Globe, Bell, Shield, ChevronDown } from 'lucide-react'

type Language = 'en'

interface SettingOption {
    value: string;
    label: string;
    icon?: React.ComponentType<{ className?: string }>;
}

const LANGUAGES: SettingOption[] = [
    { value: 'en', label: 'English' },
    { value: 'sw', label: 'Swahili' }
];

const containerVariants: Variants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: {
            staggerChildren: 0.04,
            delayChildren: 0.1,
        },
    },
};

const itemVariants: Variants = {
    hidden: { y: 20, opacity: 0 },
    visible: {
        y: 0,
        opacity: 1,
        transition: {
            type: 'spring',
            stiffness: 400,
            damping: 30,
        },
    },
};

function SettingSection({
    icon: Icon,
    title,
    description,
    children,
}: {
    icon: React.ComponentType<{ className?: string }>;
    title: string;
    description: string;
    children: React.ReactNode;
}) {
    return (
        <motion.div
            variants={itemVariants}
            className="group relative overflow-hidden rounded-xl border border-border/50 bg-card p-6 shadow-sm transition-all duration-300 hover:border-primary/40 hover:shadow-md"
        >
            <div className="absolute inset-0 bg-gradient-to-br from-primary/[0.02] to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />

            <div className="relative flex items-start gap-4">
                <motion.div
                    className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 transition-colors duration-300 group-hover:bg-primary/15"
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                >
                    <Icon className="h-5 w-5 text-primary" />
                </motion.div>

                <div className="min-w-0 flex-1 space-y-4">
                    <div>
                        <h3 className="text-base font-semibold leading-tight text-foreground">
                            {title}
                        </h3>
                        <p className="mt-1.5 text-sm leading-relaxed text-muted-foreground">
                            {description}
                        </p>
                    </div>
                    {children}
                </div>
            </div>
        </motion.div>
    );
}

function Select({
    options,
    value,
    onChange,
}: {
    options: SettingOption[];
    value: string;
    onChange: (value: string) => void;
}) {
    return (
        <div className="relative">
            <select
                value={value}
                onChange={(e) => onChange(e.target.value)}
                className="w-full sm:max-w-xs px-4 py-2.5 text-sm rounded-lg border-2 border-border/50 bg-background text-foreground hover:border-border focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none cursor-pointer font-medium"
            >
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
            <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-muted-foreground">
                <ChevronDown className="h-4 w-4" />
            </div>
        </div>
    );
}

function Toggle({
    checked,
    onChange,
    label,
}: {
    checked: boolean;
    onChange: (checked: boolean) => void;
    label: string;
}) {
    return (
        <label className="flex items-center justify-between cursor-pointer group py-1">
            <span className="text-sm font-medium text-foreground select-none">{label}</span>
            <motion.button
                type="button"
                onClick={() => onChange(!checked)}
                whileTap={{ scale: 0.95 }}
                className={`
                    relative inline-flex h-6 w-11 items-center rounded-full transition-all duration-300
                    ${checked ? 'bg-primary shadow-lg shadow-primary/30' : 'bg-muted group-hover:bg-muted/80'}
                `}
            >
                <motion.span
                    initial={false}
                    animate={{
                        x: checked ? 22 : 2,
                    }}
                    transition={{
                        type: 'spring',
                        stiffness: 500,
                        damping: 30,
                    }}
                    className={`inline-block h-5 w-5 rounded-full shadow-md transition-colors ${checked ? 'bg-white' : 'bg-white'
                        }`}
                />
            </motion.button>
        </label>
    );
}

export function GeneralSettings() {
    const [language, setLanguage] = useState<Language>('en');
    const [notifications, setNotifications] = useState(true);
    const [emailNotifications, setEmailNotifications] = useState(true);
    const [twoFactor, setTwoFactor] = useState(false);

    return (
        <div className="w-full space-y-4">
            <motion.div
                variants={containerVariants}
                initial="hidden"
                animate="visible"
                className="space-y-4"
            >
                <SettingSection
                    icon={Globe}
                    title="Language & Region"
                    description="Choose your preferred display language"
                >
                    <Select
                        options={LANGUAGES}
                        value={language}
                        onChange={(value) => setLanguage(value as Language)}
                    />
                </SettingSection>

                <SettingSection
                    icon={Bell}
                    title="Notifications"
                    description="Control how and when you receive notifications"
                >
                    <div className="space-y-3">
                        <Toggle
                            checked={notifications}
                            onChange={setNotifications}
                            label="Push notifications"
                        />
                        <Toggle
                            checked={emailNotifications}
                            onChange={setEmailNotifications}
                            label="Email notifications"
                        />
                    </div>
                </SettingSection>

                <SettingSection
                    icon={Shield}
                    title="Security"
                    description="Protect your account with additional security measures"
                >
                    <Toggle
                        checked={twoFactor}
                        onChange={setTwoFactor}
                        label="Two-factor authentication"
                    />
                </SettingSection>
            </motion.div>
        </div>
    );
}

export default function GeneralSettingsPage() {
    return (
        <div className="min-h-screen bg-background transition-colors duration-300">
            <div className="w-full max-w-4xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
                <motion.div
                    initial={{ opacity: 0, y: -20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.5 }}
                    className="mb-8"
                >
                    <h1 className="text-3xl font-bold text-foreground mb-2">Settings</h1>
                    <p className="text-muted-foreground">
                        Manage your account preferences and settings
                    </p>
                </motion.div>

                <GeneralSettings />
            </div>
        </div>
    );
}
