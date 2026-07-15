"use client"

import { useEffect, useId, useMemo, useState, useTransition } from "react"
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
import { maintenanceService } from "@/lib/api/maintenance"
import { useQuery } from "@tanstack/react-query"

export function MaintenanceRequestSheet({
    children,
    onSuccess
}: {
    children?: React.ReactNode
    onSuccess?: () => void
}) {
    const [open, setOpen] = useState(false)
    const [isPending, startTransition] = useTransition()
    const attachmentInputId = useId()
    const formId = useId()

    const { data: options, isLoading: isLoadingOptions, isError: optionsFailed } = useQuery({
        queryKey: ["tenant-maintenance-options"],
        queryFn: () => maintenanceService.getOptions(),
        enabled: open,
        staleTime: 60_000,
    })

    const units = options?.units ?? []
    const categoryOptions = useMemo(
        () => options?.categories?.length
            ? options.categories.map((option) => option.description)
            : MAINTENANCE_CATEGORIES,
        [options?.categories]
    )
    const priorityOptions = useMemo(
        () => options?.priorities?.length
            ? options.priorities.map((option) => ({ value: option.description, label: option.description, color: "bg-slate-400" }))
            : PRIORITY_LEVELS,
        [options?.priorities]
    )

    const [formData, setFormData] = useState<Partial<CreateMaintenanceRequestPayload>>({
        priority: "Medium",
        category: "Plumbing"
    })

    useEffect(() => {
        if (!options) return

        setFormData((previous) => ({
            ...previous,
            category: categoryOptions.includes(String(previous.category ?? ""))
                ? previous.category
                : categoryOptions[0],
            priority: priorityOptions.some((option) => option.value === previous.priority)
                ? previous.priority
                : priorityOptions[0]?.value,
        }))
    }, [options, categoryOptions, priorityOptions])

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault()

        if (!formData.title || !formData.description || !formData.propertyId || !formData.unitId) {
            toast.error("Please fill in all required fields")
            return
        }

        startTransition(async () => {
            try {
                await maintenanceService.createRequest(formData as CreateMaintenanceRequestPayload)
                toast.success("Maintenance request submitted successfully")
                setOpen(false)
                setFormData({ priority: "Medium", category: "Plumbing" })
                onSuccess?.()
            } catch (error) {
                toast.error(error instanceof Error ? error.message : "Failed to submit request")
            }
        })
    }

    return (
        <Sheet open={open} onOpenChange={setOpen}>
            <SheetTrigger asChild>
                {children || (
                    <Button className="rounded-xl h-10 px-4 text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white transition-colors shadow-none">
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
                        <SheetDescription className="text-xs text-slate-500 font-medium tracking-tight">
                            Submit a new service ticket for your unit
                        </SheetDescription>
                    </SheetHeader>
                </div>

                <form id={formId} onSubmit={handleSubmit} className="flex-1 overflow-y-auto px-8 py-6 space-y-6">
                    <div className="space-y-5">
                        <div className="space-y-2">
                            <Label className="text-[11px] font-medium text-slate-600">Property <span className="text-rose-500">*</span></Label>
                            <Select value={formData.unitId ? String(formData.unitId) : ""} onValueChange={(val) => {
                                const selected = units.find((unit) => String(unit.unitId) === val)
                                setFormData(prev => ({
                                    ...prev,
                                    propertyId: selected?.propertyId,
                                    unitId: selected?.unitId,
                                }))
                            }}>
                                <SelectTrigger className="h-11 rounded-lg bg-slate-50 border-slate-200 text-sm focus:ring-1 focus:ring-slate-950">
                                    <SelectValue placeholder="Select relevant property/unit" />
                                </SelectTrigger>
                                <SelectContent className="bg-white border-slate-200">
                                    {units.map(unit => (
                                        <SelectItem key={`${unit.leaseId}-${unit.unitId}`} value={unit.unitId.toString()} className="text-sm">
                                            {unit.propertyName} - {unit.unitCode} ({unit.leaseNumber})
                                        </SelectItem>
                                    ))}
                                    {!isLoadingOptions && units.length === 0 ? (
                                        <div className="px-3 py-2 text-xs text-slate-500">
                                            {optionsFailed
                                                ? "Unable to load your rented units. Close this panel and try again."
                                                : "No approved leased units are available."}
                                        </div>
                                    ) : null}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-[11px] font-medium text-slate-600">Issue title <span className="text-rose-500">*</span></Label>
                            <Input
                                placeholder="e.g. Kitchen sink blockage"
                                className="h-11 rounded-lg bg-slate-50 border-slate-200 text-sm focus:ring-1 focus:ring-slate-950"
                                value={formData.title || ""}
                                onChange={e => setFormData(prev => ({ ...prev, title: e.target.value }))}
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label className="text-[11px] font-medium text-slate-600">Category</Label>
                                <Select
                                    value={formData.category || ""}
                                    onValueChange={(val) => setFormData(prev => ({ ...prev, category: val }))}
                                >
                                    <SelectTrigger className="h-11 rounded-lg bg-slate-50 border-slate-200 text-sm">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent className="bg-white border-slate-200">
                                        {categoryOptions.map(cat => (
                                            <SelectItem key={cat} value={cat} className="text-sm">{cat}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label className="text-[11px] font-medium text-slate-600">Priority</Label>
                                <Select
                                    value={formData.priority || ""}
                                    onValueChange={(val) => setFormData(prev => ({ ...prev, priority: val }))}
                                >
                                    <SelectTrigger className="h-11 rounded-lg bg-slate-50 border-slate-200 text-sm">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent className="bg-white border-slate-200">
                                        {priorityOptions.map(level => (
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
                            <Label className="text-[11px] font-medium text-slate-600">Description <span className="text-rose-500">*</span></Label>
                            <Textarea
                                placeholder="Please explain the problem clearly..."
                                className="min-h-[120px] rounded-lg bg-slate-50 border-slate-200 text-sm resize-none p-3 focus:ring-1 focus:ring-slate-950"
                                value={formData.description || ""}
                                onChange={e => setFormData(prev => ({ ...prev, description: e.target.value }))}
                            />
                        </div>

                        <div className="space-y-2">
                            <Label className="text-[11px] font-medium text-slate-600">Attachments</Label>
                            <input
                                id={attachmentInputId}
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                multiple
                                className="sr-only"
                                onChange={(event) => setFormData((previous) => ({
                                    ...previous,
                                    images: Array.from(event.target.files ?? []).slice(0, 5),
                                }))}
                            />
                            <label htmlFor={attachmentInputId} className="border border-dashed border-slate-200 rounded-lg p-6 flex flex-col items-center justify-center text-center hover:bg-slate-50 transition-all cursor-pointer group">
                                <div className="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center mb-2 group-hover:bg-slate-900 transition-colors">
                                    <UploadCloud className="h-4 w-4 text-slate-500 group-hover:text-white" />
                                </div>
                                <p className="text-xs font-medium text-slate-500">
                                    {formData.images?.length
                                        ? `${formData.images.length} attachment${formData.images.length === 1 ? "" : "s"} selected`
                                        : "Click to upload up to 5 images"}
                                </p>
                            </label>
                        </div>
                    </div>
                </form>

                <div className="px-8 py-6 border-t border-slate-100 flex gap-3 bg-slate-50/50">
                    <Button
                        type="button"
                        variant="outline"
                        className="flex-1 h-11 rounded-xl text-xs font-semibold border-slate-200 bg-white"
                        onClick={() => setOpen(false)}
                        disabled={isPending}
                    >
                        Discard
                    </Button>
                    <Button
                        type="submit"
                        form={formId}
                        className="flex-1 h-11 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white"
                        disabled={isPending || isLoadingOptions || optionsFailed || units.length === 0}
                    >
                        {isPending && <Loader2 className="mr-2 h-3 w-3 animate-spin" />}
                        Submit Ticket
                    </Button>
                </div>
            </SheetContent>
        </Sheet>
    )
}
