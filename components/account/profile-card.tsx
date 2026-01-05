"use client"

import React from "react"
import { motion, Variants } from "framer-motion"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar"
import { Card, CardContent } from "@/components/common/card"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { BadgeCheck, Camera, Edit } from "lucide-react"
import { UserProfile } from "@/types/next-auth"
import { getInitials } from "@/lib/utils"

const containerVariants: Variants = {
    hidden: { opacity: 0, y: 15 },
    show: { opacity: 1, y: 0, transition: { duration: 0.4, ease: [0.4, 0, 0.2, 1] } }
}

const contentVariants: Variants = {
    hidden: { opacity: 0, y: 5 },
    show: { opacity: 1, y: 0, transition: { duration: 0.3, delay: 0.05 } }
}

const stampVariants: Variants = {
    hidden: { opacity: 0, scale: 0.9 },
    show: { opacity: 1, scale: 1, transition: { type: "spring", stiffness: 400, damping: 30 } }
}

export default function ProfileCard({
    profile,
    onProfilePictureClick,
    onEditProfile
}: {
    profile: UserProfile
    onProfilePictureClick: () => void
    onEditProfile: () => void
}) {
    const displayName =
        profile.fullName ||
        `${profile.firstName || ""} ${profile.lastName || ""}`.trim()

    return (
        <motion.div variants={containerVariants} initial="hidden" animate="show" className="max-w-xs mx-auto">
            <Card className="relative p-0  border border-border">
                <motion.div whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }} className="absolute top-2 right-2 z-10">
                    <Button
                        variant="ghost"
                        size="icon"
                        className="h-8 w-8 text-muted-foreground hover:bg-accent/70 hover:text-foreground"
                        aria-label="Edit profile details"
                        onClick={onEditProfile}
                    >
                        <Edit className="h-4 w-4" />
                    </Button>
                </motion.div>

                <CardContent className="flex flex-col items-center p-6 gap-4">

                    <div className="relative group">
                        <motion.div
                            whileHover={{ scale: 1.05 }}
                            whileTap={{ scale: 0.98 }}
                            className="cursor-pointer"
                            onClick={onProfilePictureClick}
                            aria-label="Change profile picture"
                        >
                            <Avatar
                                className="h-20 w-20 border-2 border-background ring-2 ring-primary/20 transition-transform duration-300"
                            >
                                <AvatarImage
                                    src={profile.imageUrl ?? undefined}
                                    alt={displayName}
                                    className="object-cover"
                                />
                                <AvatarFallback className="text-xl font-semibold bg-primary text-primary-foreground">
                                    {getInitials(profile.firstName, profile.lastName)}
                                </AvatarFallback>
                            </Avatar>
                        </motion.div>

                        <motion.div
                            initial={{ opacity: 0 }}
                            whileHover={{ opacity: 1 }}
                            className="absolute inset-0 rounded-full flex items-center justify-center bg-black/40 text-white cursor-pointer transition-opacity duration-300"
                            onClick={onProfilePictureClick}
                        >
                            <Camera className="h-5 w-5" />
                        </motion.div>
                    </div>

                    <motion.div
                        variants={contentVariants}
                        initial="hidden"
                        animate="show"
                        className="flex flex-col items-center gap-1.5 text-center"
                    >
                        <h2 className="text-xl font-bold tracking-tight text-foreground">
                            {displayName}
                        </h2>
                        <p className="text-sm text-muted-foreground font-medium">
                            {profile.email}
                        </p>

                        {profile.emailVerifiedOn && (
                            <motion.div variants={stampVariants as any} initial="hidden" animate="show" className="pt-1">
                                <Badge
                                    className="flex items-center gap-1 bg-success text-success-foreground hover:bg-success text-xs font-semibold"
                                >
                                    <BadgeCheck className="h-3 w-3 fill-success-foreground" />
                                    Verified
                                </Badge>
                            </motion.div>
                        )}
                    </motion.div>
                </CardContent>
            </Card>
        </motion.div>
    )
}