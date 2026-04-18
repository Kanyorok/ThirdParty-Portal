'use client'

import { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { Sparkles, X, ArrowRight } from 'lucide-react'
import { useProfileStore } from '@/store/use-profile-store'
import { useOnboardingStore } from '@/store/use-onboarding-store'
import { Button } from '@/components/common/button'

const TOUR_CONTENT: Record<string, { title: string; desc: string }> = {
    Supplier: {
        title: "Welcome to the Third Party Portal",
        desc: "You can now manage bids, view active procurement rounds, and track your application status in real-time."
    },
    Tenant: {
        title: "Tenant Dashboard Ready",
        desc: "Manage your lease agreements, pay rent, and submit maintenance requests directly from this view."
    }
}

export function OnboardingWatcher() {
    const activeProfile = useProfileStore((s) => s.activeProfile)
    const { hasSeenTour, markTourComplete } = useOnboardingStore()
    const [show, setShow] = useState(false)

    useEffect(() => {
        if (activeProfile !== 'base' && !hasSeenTour(activeProfile)) {
            const timer = setTimeout(() => setShow(true), 1200)
            return () => clearTimeout(timer)
        }
        setShow(false)
    }, [activeProfile, hasSeenTour])

    const handleDismiss = () => {
        markTourComplete(activeProfile)
        setShow(false)
    }

    return (
        <AnimatePresence>
            {show && TOUR_CONTENT[activeProfile] && (
                <motion.div
                    initial={{ opacity: 0, y: 20, scale: 0.95 }}
                    animate={{ opacity: 1, y: 0, scale: 1 }}
                    exit={{ opacity: 0, scale: 0.95 }}
                    className="fixed bottom-8 right-8 z-[100] w-80 overflow-hidden rounded-2xl border border-primary/20 bg-card p-0 shadow-2xl"
                >
                    <div className="bg-primary/10 px-4 py-3 flex items-center gap-2 border-b border-primary/10">
                        <Sparkles className="size-4 text-primary" />
                        <span className="text-[10px] font-black uppercase tracking-widest text-primary">New Workspace Active</span>
                        <button onClick={handleDismiss} className="ml-auto hover:text-primary transition-colors">
                            <X className="size-4" />
                        </button>
                    </div>

                    <div className="p-5">
                        <h4 className="text-sm font-black uppercase tracking-tight mb-2">
                            {TOUR_CONTENT[activeProfile].title}
                        </h4>
                        <p className="text-xs text-muted-foreground leading-relaxed mb-6">
                            {TOUR_CONTENT[activeProfile].desc}
                        </p>

                        <Button
                            onClick={handleDismiss}
                            className="w-full justify-between bg-primary hover:bg-primary/90 text-primary-foreground font-bold text-[10px] uppercase tracking-widest h-10"
                        >
                            Start Exploring
                            <ArrowRight className="size-3" />
                        </Button>
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    )
}