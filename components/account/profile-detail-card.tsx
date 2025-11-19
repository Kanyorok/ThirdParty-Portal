"use client"

import { UserProfile } from "@/types/next-auth"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card"
import { motion } from "framer-motion"
import { easeInOut } from "framer-motion"
import { Separator } from "@/components/common/separator"
import { Label } from "@/components/common/label"
import { Button } from "@/components/common/button"
import { Edit, KeyRound, Mail, Phone, Shield, UploadCloud, User } from "lucide-react"

const container = {
    hidden: { opacity: 0, y: 22 },
    show: { opacity: 1, y: 0, transition: { duration: 0.45, ease: easeInOut } }
}

const field = {
    hidden: { opacity: 0, y: 8 },
    show: { opacity: 1, y: 0, transition: { duration: 0.28, ease: easeInOut } }
}

export default function ProfileDetailsCard({
    profile,
    onEdit,
    onPasswordChange,
    onProfilePictureClick
}: {
    profile: UserProfile
    onEdit: () => void
    onPasswordChange: () => void
    onProfilePictureClick: () => void
}) {
    return (
        <motion.div variants={container} initial="hidden" animate="show">
            <Card className="border bg-card/50 backdrop-blur-md shadow-none">
                <CardHeader className="pb-4">
                    <CardTitle className="flex items-center gap-3 text-2xl font-semibold">
                        <div className="p-2 bg-primary/15 dark:bg-primary/20 rounded-lg">
                            <User className="h-5 w-5 text-primary" />
                        </div>
                        Personal Information
                    </CardTitle>
                </CardHeader>

                <Separator />

                <CardContent className="pt-6 space-y-8">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <motion.div variants={field} className="space-y-2">
                            <Label className="text-sm font-medium text-muted-foreground">First Name</Label>
                            <div className="p-3 bg-muted rounded-lg">
                                <p className="font-semibold text-foreground">{profile.firstName}</p>
                            </div>
                        </motion.div>

                        <motion.div variants={field} className="space-y-2">
                            <Label className="text-sm font-medium text-muted-foreground">Last Name</Label>
                            <div className="p-3 bg-muted rounded-lg">
                                <p className="font-semibold text-foreground">{profile.lastName}</p>
                            </div>
                        </motion.div>

                        <motion.div variants={field} className="space-y-2">
                            <Label className="text-sm font-medium text-muted-foreground">Email Address</Label>
                            <div className="p-3 bg-muted rounded-lg flex items-center gap-2">
                                <Mail className="h-4 w-4 text-muted-foreground" />
                                <p className="font-semibold text-foreground">{profile.email}</p>
                            </div>
                        </motion.div>

                        <motion.div variants={field} className="space-y-2">
                            <Label className="text-sm font-medium text-muted-foreground">Phone Number</Label>
                            <div className="p-3 bg-muted rounded-lg flex items-center gap-2">
                                <Phone className="h-4 w-4 text-muted-foreground" />
                                <p className="font-semibold text-foreground">
                                    {profile.phone || "Not provided"}
                                </p>
                            </div>
                        </motion.div>

                        {profile.gender && (
                            <motion.div variants={field} className="space-y-2">
                                <Label className="text-sm font-medium text-muted-foreground">Gender</Label>
                                <div className="p-3 bg-muted rounded-lg">
                                    <p className="font-semibold text-foreground">
                                        {profile.gender === "m"
                                            ? "Male"
                                            : profile.gender === "f"
                                                ? "Female"
                                                : profile.gender === "o"
                                                    ? "Prefer not to say"
                                                    : profile.gender}
                                    </p>
                                </div>
                            </motion.div>
                        )}

                        <motion.div variants={field} className="space-y-2">
                            <Label className="text-sm font-medium text-muted-foreground">Password</Label>
                            <div className="p-3 bg-muted rounded-lg flex items-center gap-2">
                                <Shield className="h-4 w-4 text-muted-foreground" />
                                <p className="font-semibold text-foreground">••••••••••••</p>
                            </div>
                        </motion.div>
                    </div>

                    <Separator />

                    <motion.div
                        variants={field}
                        className="flex flex-col sm:flex-row justify-between items-center gap-3 pt-4"
                    >
                        <Button onClick={onEdit} className="flex items-center gap-2 w-full sm:w-auto">
                            <Edit className="h-4 w-4" />
                            Edit Profile
                        </Button>

                        <Button
                            variant="outline"
                            onClick={onPasswordChange}
                            className="flex items-center gap-2 w-full sm:w-auto"
                        >
                            <KeyRound className="h-4 w-4" />
                            Change Password
                        </Button>

                        <Button
                            variant="outline"
                            onClick={onProfilePictureClick}
                            className="flex items-center gap-2 w-full sm:w-auto"
                        >
                            <UploadCloud className="h-4 w-4" />
                            Profile Picture
                        </Button>
                    </motion.div>
                </CardContent>
            </Card>
        </motion.div>
    )
}
