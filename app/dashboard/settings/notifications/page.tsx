"use client"

import { Separator } from "@/components/common/separator"
import Link from "next/link"
import { Bell } from "lucide-react"
import { Button } from "@/components/common/button"
import { Card, CardContent } from "@/components/common/card"

export default function BusinessDetails() {
    return (
        <div className="space-y-6">
            <div className="flex items-center gap-3">
                <div className="flex size-10 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                    <Bell className="h-5 w-5" />
                </div>
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Notification Settings</h1>
                    <p className="text-sm text-muted-foreground">Manage how you receive updates.</p>
                </div>
            </div>
            <Separator />
            <Card className="rounded-2xl border border-border/60 bg-card shadow-none">
                <CardContent className="p-6 space-y-4">
                    <div className="text-sm text-muted-foreground">
                        Notifications are delivered to your inbox and shown in the bell menu. Preference controls can be managed under General settings.
                    </div>
                    <div className="flex flex-col gap-2 sm:flex-row">
                        <Button asChild className="h-11 rounded-xl shadow-none">
                            <Link href="/dashboard/notifications">Open notifications</Link>
                        </Button>
                        <Button asChild variant="outline" className="h-11 rounded-xl border-border/60 bg-background shadow-none">
                            <Link href="/dashboard/settings/general">Manage preferences</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    )
}
