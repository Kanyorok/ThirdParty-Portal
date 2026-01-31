"use client"

import { useProcurementStore } from "@/store/use-procurement-store"
import { useParams, useRouter } from "next/navigation"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card"
import {
    CalendarDays,
    Users,
    ArrowLeft,
    CheckCircle2,
    Clock,
    FileText,
    Trophy,
    FileEdit,
    ArrowRight
} from "lucide-react"
import { Separator } from "@/components/common/separator"
import { cn } from "@/lib/utils"
import Loading from "@/components/common/custom-loader"

export default function RoundDetailPage() {
    const params = useParams()
    const router = useRouter()
    const id = Number(params.id)

    const { rounds, selectedRound, applicationStatus, updateApplicationStatus } = useProcurementStore()

    const round = rounds.find(r => r.id === id) || selectedRound
    const userStatus = applicationStatus[id]

    if (!round) {
        return (
            <div className="flex flex-col items-center justify-center min-h-[60vh] gap-4">
                <div className="animate-spin rounded-full h-10 w-10 border-t-2 border-primary"></div>
                <Loading />
                <p className="text-muted-foreground animate-pulse font-medium">Loading Round Data...</p>
            </div>
        )
    }

    const handleAction = () => {
        if (!userStatus) {
            updateApplicationStatus(id, "draft")
        }
        router.push(`/dashboard/procurement/rounds/${id}/apply`)
    }

    const statusConfig = {
        O: { label: "Accepting Applications", class: "bg-emerald-500 text-white border-none" },
        C: { label: "Applications Closed", class: "bg-slate-500 text-white border-none" },
        P: { label: "Under Review", class: "bg-yellow-500 text-black border-none" }
    }

    const currentStatus = statusConfig[round.status as keyof typeof statusConfig] || statusConfig.P

    return (
        <div className="flex-1 space-y-6 p-4 md:p-8 pt-6 max-w-[1600px] mx-auto">
            <div className="flex items-center justify-between">
                <Button
                    variant="ghost"
                    size="sm"
                    className="group -ml-2 text-muted-foreground hover:text-primary transition-all"
                    onClick={() => router.back()}
                >
                    <ArrowLeft className="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" />
                    Go Back
                </Button>
            </div>

            <div className="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
                <div className="xl:col-span-8 space-y-8">
                    <header className="space-y-3">
                        <div className="flex items-center justify-between gap-4">
                            <h1 className="text-3xl md:text-5xl font-black tracking-tight text-foreground leading-[1.1]">
                                {round.title}
                            </h1>

                            <Badge
                                className={cn(
                                    "px-4 py-1.5 text-xs font-black uppercase tracking-widest rounded-full whitespace-nowrap",
                                    currentStatus.class,
                                )}
                            >
                                {currentStatus.label}
                            </Badge>
                        </div>

                        {round.dates.isClosingSoon && (
                            <div className="flex justify-end">
                                <Badge variant="destructive" className="flex items-center gap-1.5 font-bold animate-pulse">
                                    <Clock className="h-3 w-3" />
                                    Closing Soon
                                </Badge>
                            </div>
                        )}

                        {userStatus && (
                            <div className="flex justify-end gap-2">
                                <Badge
                                    className={cn(
                                        "px-3 py-1 uppercase tracking-wider font-bold border-none text-white",
                                        userStatus === "draft" ? "bg-blue-500" : "bg-primary"
                                    )}
                                >
                                    {userStatus === "draft" ? "Draft In Progress" : "Application Submitted"}
                                </Badge>
                            </div>
                        )}
                    </header>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {[
                            { label: "Category", value: round.targetedCategories[0]?.name || "General", icon: FileText },
                            { label: "Max Participants", value: `${round.metadata.maxVendors} Vendors`, icon: Users },
                            { label: "Deadline", value: new Date(round.dates.end).toLocaleDateString(), icon: CalendarDays }
                        ].map((item, i) => (
                            <div key={i} className="flex items-center gap-3 p-4 rounded-xl bg-muted/30 border border-border/50">
                                <div className="p-2 rounded-lg bg-background shadow-sm">
                                    <item.icon className="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <p className="text-[11px] uppercase tracking-wider font-bold text-muted-foreground">{item.label}</p>
                                    <p className="text-sm font-semibold">{item.value}</p>
                                </div>
                            </div>
                        ))}
                    </div>

                    <section className="space-y-4">
                        <div className="flex items-center gap-2">
                            <div className="h-8 w-1 bg-primary rounded-full" />
                            <h2 className="text-xl font-bold tracking-tight uppercase">Requirement Overview</h2>
                        </div>
                        <div className="bg-card/50 rounded-2xl p-6 border-2 border-muted border-dashed">
                            <p className="text-base md:text-lg text-muted-foreground leading-relaxed whitespace-pre-wrap">
                                {round.description}
                            </p>
                        </div>
                    </section>

                    <section
                        className={cn(
                            "rounded-3xl p-8 md:p-12 border-2 border-dashed relative overflow-hidden",
                            userStatus === "draft"
                                ? "bg-blue-50/50 border-blue-200 dark:bg-blue-900/10 dark:border-blue-900/30"
                                : "bg-yellow-50/50 border-yellow-200 dark:bg-yellow-900/10 dark:border-yellow-900/30"
                        )}
                    >
                        <div className="grid md:grid-cols-2 gap-12 items-center">
                            <div className="space-y-6 text-center md:text-left">
                                <div className="space-y-3">
                                    <h3 className="text-2xl md:text-3xl font-bold">
                                        {userStatus === "draft" ? "Continue Application" : "Start Your Application"}
                                    </h3>
                                    <p className="text-muted-foreground text-sm md:text-base">
                                        {userStatus === "draft"
                                            ? "Your draft is saved. Pick up exactly where you left off."
                                            : "Begin the prequalification workflow and submit your documents."}
                                    </p>
                                </div>

                                <div className="space-y-3">
                                    {["Electronic Submission", "Real-Time Status", "Secure Processing"].map((feat, i) => (
                                        <div key={i} className="flex items-center gap-2 text-sm font-bold uppercase justify-center md:justify-start">
                                            <CheckCircle2
                                                className={cn("h-4 w-4", userStatus === "draft" ? "text-blue-500" : "text-yellow-600")}
                                            />
                                            {feat}
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="flex flex-col gap-3">
                                <Button
                                    onClick={handleAction}
                                    size="lg"
                                    className={cn(
                                        "h-14 text-md font-bold transition-all hover:-translate-y-0.5 active:scale-95 border-none",
                                        userStatus === "draft"
                                            ? "bg-blue-600 hover:bg-blue-700 text-white"
                                            : "bg-yellow-500 hover:bg-yellow-600 text-black"
                                    )}
                                >
                                    {userStatus === "draft" ? (
                                        <>
                                            <FileEdit className="mr-2 h-4 w-4" /> Resume Draft
                                        </>
                                    ) : (
                                        <>
                                            <ArrowRight className="mr-2 h-4 w-4" /> Start Application
                                        </>
                                    )}
                                </Button>
                                <p className="text-[10px] text-center text-muted-foreground uppercase tracking-widest font-bold">
                                    Progress saves automatically
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                <aside className="xl:col-span-4 space-y-6 xl:sticky xl:top-6">
                    <Card className="border-l-4 border-l-yellow-500 shadow-none rounded-none">
                        <CardHeader>
                            <CardTitle className="text-base font-bold flex items-center gap-2">
                                <Trophy className="h-4 w-4 text-amber-500" />
                                Key Milestones
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="relative space-y-6 before:absolute before:ml-2 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-primary before:to-muted">
                                <div className="relative flex gap-4 pl-6">
                                    <div className="absolute left-0 h-4 w-4 rounded-full border-2 border-primary bg-background translate-x-[2px]" />
                                    <div>
                                        <p className="text-xs font-bold uppercase text-muted-foreground">Published</p>
                                        <p className="text-sm font-semibold">{new Date(round.metadata.createdAt).toLocaleDateString()}</p>
                                    </div>
                                </div>
                                <div className="relative flex gap-4 pl-6 opacity-60">
                                    <div className="absolute left-0 h-4 w-4 rounded-full border-2 border-muted bg-background translate-x-[2px]" />
                                    <div>
                                        <p className="text-xs font-bold uppercase text-muted-foreground">Deadline</p>
                                        <p className="text-sm font-semibold">{new Date(round.dates.end).toLocaleDateString()}</p>
                                    </div>
                                </div>
                            </div>

                            <Separator />

                            <div className="space-y-4">
                                <h4 className="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Target Categories</h4>
                                <div className="flex flex-wrap gap-2">
                                    {round.targetedCategories.length > 0 ? (
                                        round.targetedCategories.map(cat => (
                                            <Badge key={cat.id} variant="outline" className="px-3 py-1 border-primary/20 text-primary font-bold rounded-none">
                                                {cat.name}
                                            </Badge>
                                        ))
                                    ) : (
                                        <p className="text-xs text-muted-foreground italic">General Round</p>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </aside>
            </div>
        </div>
    )
}
