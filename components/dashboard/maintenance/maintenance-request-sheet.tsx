"use client"

import { useState, useTransition } from "react"
import { Button } from "@/components/common/button"
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from "@/components/common/sheet"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/common/select"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Textarea } from "@/components/common/textarea"
import { toast } from "sonner"
import { Loader2, Plus, UploadCloud, Wrench } from "lucide-react"
import { MAINTENANCE_CATEGORIES, PRIORITY_LEVELS, CreateMaintenanceRequestPayload } from "@/types/maintenance"

export function MaintenanceRequestSheet({
    children,
    onSuccess
}: {
    children?: React.ReactNode
    onSuccess?: () => void
}) {
    const [open, setOpen] = useState(false)
    const [isPending, startTransition] = useTransition()

    const properties = [
        { id: 1, name: "Sunset Apartments - Unit 101" },
        { id: 2, name: "Downtown Loft - Unit 3B" }
    ]

    const [formData, setFormData] = useState<Partial<CreateMaintenanceRequestPayload>>({
        priority: "Medium",
        category: "Plumbing"
    })

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault()

        if (!formData.title || !formData.description || !formData.propertyId) {
            toast.error("Please fill in all required fields")
            return
        }

        startTransition(async () => {
            try {
                await new Promise(resolve => setTimeout(resolve, 1000))
                toast.success("Maintenance request submitted successfully")
                setOpen(false)
                setFormData({ priority: "Medium", category: "Plumbing" })
                onSuccess?.()
            } catch (error) {
                toast.error("Failed to submit request")
            }
        })
    }

    return (
        <Sheet open={open} onOpenChange={setOpen}>
            <SheetTrigger asChild>
                {children || (
                    <Button className="rounded-xl h-10 px-4 font-bold uppercase tracking-tight text-[11px] bg-slate-900 hover:bg-slate-800 text-white transition-all shadow-sm">
                        <Plus className="h-3.5 w-3.5 mr-2" />
                        New Request
                    </Button>
                )}
            </SheetTrigger>
            <SheetContent className="sm:max-w-[480px] bg-white border-l border-slate-200 p-0 flex flex-col shadow-none">
                <div className="px-8 py-8 border-b border-slate-100 bg-white">
                    <SheetHeader>
                        <div className="inline-flex h-10 w-10 rounded-lg bg-slate-900 items-center justify-center mb-4">
                            <Wrench className="h-5 w-5 text-white" />
                        </div>
                        <SheetTitle className="text-xl font-bold tracking-tight text-slate-900">
                            Maintenance Request
                        </SheetTitle>
                        <SheetDescription className="text-xs text-slate-500 font-medium tracking-tight uppercase">
                            Submit a new service ticket for your unit
                        </SheetDescription>
                    </SheetHeader>
                </div>

                <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto px-8 py-6 space-y-6">
                    <div className="space-y-5">
                        <div className="space-y-2">
                            <Label className="text-[10px] font-bold uppercase tracking-widest text-slate-400">Property <span className="text-rose-500">*</span></Label>
                            <Select
                                onValueChange={(val) => setFormData(prev => ({ ...prev, propertyId: parseInt(val) }))}
                            >
                                <SelectTrigger className="h-11 rounded-lg bg-slate-50 border-slate-200 text-sm focus:ring-1 focus:ring-slate-950">
                                    <SelectValue placeholder="Select relevant property/unit" />
                                </SelectTrigger>
                                <SelectContent className="bg-white border-slate-200">
                                    {properties.map(prop => (
                                        <SelectItem key={prop.id} value={prop.id.toString()} className="text-sm">
                                            {prop.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-[10px] font-bold uppercase tracking-widest text-slate-400">Issue Title <span className="text-rose-500">*</span></Label>
                            <Input
                                placeholder="e.g. Kitchen sink blockage"
                                className="h-11 rounded-lg bg-slate-50 border-slate-200 text-sm focus:ring-1 focus:ring-slate-950"
                                value={formData.title || ""}
                                onChange={e => setFormData(prev => ({ ...prev, title: e.target.value }))}
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label className="text-[10px] font-bold uppercase tracking-widest text-slate-400">Category</Label>
                                <Select
                                    defaultValue="Plumbing"
                                    onValueChange={(val) => setFormData(prev => ({ ...prev, category: val }))}
                                >
                                    <SelectTrigger className="h-11 rounded-lg bg-slate-50 border-slate-200 text-sm">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent className="bg-white border-slate-200">
                                        {MAINTENANCE_CATEGORIES.map(cat => (
                                            <SelectItem key={cat} value={cat} className="text-sm">{cat}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label className="text-[10px] font-bold uppercase tracking-widest text-slate-400">Priority Level</Label>
                                <Select
                                    defaultValue="Medium"
                                    onValueChange={(val) => setFormData(prev => ({ ...prev, priority: val }))}
                                >
                                    <SelectTrigger className="h-11 rounded-lg bg-slate-50 border-slate-200 text-sm">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent className="bg-white border-slate-200">
                                        {PRIORITY_LEVELS.map(level => (
                                            <SelectItem key={level.value} value={level.value} className="text-sm">
                                                <div className="flex items-center gap-2">
                                                    <div className={`h-1.5 w-1.5 rounded-full ${level.color}`} />
                                                    {level.label}
                                                </div>
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-[10px] font-bold uppercase tracking-widest text-slate-400">Detailed Description <span className="text-rose-500">*</span></Label>
                            <Textarea
                                placeholder="Please explain the problem clearly..."
                                className="min-h-[120px] rounded-lg bg-slate-50 border-slate-200 text-sm resize-none p-3 focus:ring-1 focus:ring-slate-950"
                                value={formData.description || ""}
                                onChange={e => setFormData(prev => ({ ...prev, description: e.target.value }))}
                            />
                        </div>

                        <div className="space-y-2">
                            <Label className="text-[10px] font-bold uppercase tracking-widest text-slate-400">Attachments</Label>
                            <div className="border border-dashed border-slate-200 rounded-lg p-6 flex flex-col items-center justify-center text-center hover:bg-slate-50 transition-all cursor-pointer group">
                                <div className="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center mb-2 group-hover:bg-slate-900 transition-colors">
                                    <UploadCloud className="h-4 w-4 text-slate-500 group-hover:text-white" />
                                </div>
                                <p className="text-[11px] font-bold text-slate-400 uppercase tracking-tight">
                                    Click to upload media
                                </p>
                            </div>
                        </div>
                    </div>
                </form>

                <div className="px-8 py-6 border-t border-slate-100 flex gap-3 bg-slate-50/50">
                    <Button
                        type="button"
                        variant="outline"
                        className="flex-1 h-11 rounded-lg font-bold uppercase tracking-widest text-[10px] border-slate-200 bg-white"
                        onClick={() => setOpen(false)}
                        disabled={isPending}
                    >
                        Discard
                    </Button>
                    <Button
                        type="submit"
                        onClick={handleSubmit}
                        className="flex-1 h-11 rounded-lg font-bold uppercase tracking-widest text-[10px] bg-slate-900 hover:bg-slate-800 text-white"
                        disabled={isPending}
                    >
                        {isPending && <Loader2 className="mr-2 h-3 w-3 animate-spin" />}
                        Submit Ticket
                    </Button>
                </div>
            </SheetContent>
        </Sheet>
    )
}