"use client"

import { useState } from "react"
import { motion, AnimatePresence } from "framer-motion"
import { ProfileEditForm } from "@/components/thirdparty-profile/profile-edit-form"
import { ThirdPartyProfileView } from "@/components/thirdparty-profile/third-party-profile-view"


export default function GeneralSettingsPage() {
    const [isEditing, setIsEditing] = useState(false)

    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.4 }}
            className="w-full"
        >
            <AnimatePresence mode="wait">
                {isEditing ? (
                    <motion.div
                        key="edit-mode"
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -10 }}
                        transition={{ duration: 0.3 }}
                    >
                        <ProfileEditForm onCancel={() => setIsEditing(false)} onSuccess={() => setIsEditing(false)} />
                    </motion.div>
                ) : (
                    <motion.div
                        key="view-mode"
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -10 }}
                        transition={{ duration: 0.3 }}
                    >
                        <ThirdPartyProfileView onEdit={() => setIsEditing(true)} />
                    </motion.div>
                )}
            </AnimatePresence>
        </motion.div>
    )
}
