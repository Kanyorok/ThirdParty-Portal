'use client'

import React, { useCallback, useReducer } from "react"
import { AnimatePresence, motion } from "framer-motion"
import {
    Check,
    ChevronRight,
    Plus,
    Trash2,
    Upload,
    FileUp,
    Loader2,
    Inbox
} from 'lucide-react'
import toast from "react-hot-toast"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/common/select"
import { Checkbox } from "@/components/common/checkbox"
import { Separator } from "@/components/common/separator"
import { cn } from "@/lib/utils"

type DocRow = {
    id: number
    description: string
    file: File | null
    selected: boolean
    error?: string | null
}

type State = {
    category: string
    rows: DocRow[]
    nextId: number
    isSaving: boolean
    lastSavedAt: number | null
    isDirty: boolean
}

type Action =
    | { type: "setCategory"; value: string }
    | { type: "addRow" }
    | { type: "deleteRow"; id: number }
    | { type: "toggleSelected"; id: number; value: boolean }
    | { type: "selectAll"; value: boolean }
    | { type: "setDescription"; id: number; value: string }
    | { type: "setFile"; id: number; file: File | null; error?: string | null }
    | { type: "saving" }
    | { type: "saved" }

const MAX_FILE_MB = 20
const ACCEPTED_TYPES = [
    "application/pdf",
    "image/jpeg",
    "image/jpg",
    "image/png",
    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
]

const documentCategories = [
    "Business Documents",
    "Financial Capacity",
    "Employee Details",
    "Contract Profile",
    "Additional Business Documents",
]

const mandatoryDocuments = [
    "Copy of KRA PIN",
    "Original National ID",
    "Registration Certificate",
    "Partnership Deed",
    "Copy of CR 12",
]

const initialState: State = {
    category: "",
    rows: [{ id: 1, description: "", file: null, selected: false }],
    nextId: 2,
    isSaving: false,
    lastSavedAt: null,
    isDirty: false,
}

function reducer(state: State, action: Action): State {
    switch (action.type) {
        case "setCategory":
            return { ...state, category: action.value, isDirty: true }
        case "addRow":
            return {
                ...state,
                rows: [...state.rows, { id: state.nextId, description: "", file: null, selected: false }],
                nextId: state.nextId + 1,
                isDirty: true,
            }
        case "deleteRow":
            return {
                ...state,
                rows: state.rows.filter((r) => r.id !== action.id),
                isDirty: true,
            }
        case "toggleSelected":
            return {
                ...state,
                rows: state.rows.map((r) => r.id === action.id ? { ...r, selected: action.value } : r),
            }
        case "selectAll":
            return {
                ...state,
                rows: state.rows.map((r) => ({ ...r, selected: action.value })),
            }
        case "setDescription":
            return {
                ...state,
                rows: state.rows.map((r) => r.id === action.id ? { ...r, description: action.value } : r),
                isDirty: true,
            }
        case "setFile":
            return {
                ...state,
                rows: state.rows.map((r) => r.id === action.id ? { ...r, file: action.file, error: action.error ?? null } : r),
                isDirty: true,
            }
        case "saving":
            return { ...state, isSaving: true }
        case "saved":
            return { ...state, isSaving: false, isDirty: false, lastSavedAt: Date.now() }
        default:
            return state
    }
}

export default function DocsUpload() {
    const [state, dispatch] = useReducer(reducer, initialState)

    const selectedCount = state.rows.filter((r) => r.selected).length
    const allSelected = state.rows.length > 0 && selectedCount === state.rows.length
    const someSelected = selectedCount > 0 && !allSelected
    const readyCount = state.rows.filter((r) => r.file && r.description && !r.error).length
    const completion = Math.round((readyCount / Math.max(state.rows.length, 1)) * 100)

    const validateFile = useCallback((file: File | null): string | null => {
        if (!file) return null
        const mb = file.size / 1024 / 1024
        if (mb > MAX_FILE_MB) return `Limit ${MAX_FILE_MB}MB`
        if (!ACCEPTED_TYPES.includes(file.type)) return "Invalid format"
        return null
    }, [])

    const handleSave = useCallback(async () => {
        if (!state.category) return toast.error("Select category")
        dispatch({ type: "saving" })
        await new Promise((r) => setTimeout(r, 1000))
        dispatch({ type: "saved" })
        toast.success("Documents synced")
    }, [state.category])

    return (
        <div className="min-h-screen w-full overflow-x-hidden bg-background">
            <div className="mx-auto w-full max-w-none px-4 sm:px-6 py-8 md:py-10">
                <header className="mb-8 md:mb-12 flex flex-col lg:flex-row lg:items-center justify-between gap-4 md:gap-6 border-b pb-6 md:pb-8">
                    <div className="space-y-1">
                        <div className="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-muted-foreground/60">
                            <span>Dashboard</span>
                            <ChevronRight className="h-3 w-3" />
                            <span className="text-primary">Documentation</span>
                        </div>
                    </div>

                    <div className="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4 lg:gap-8 w-full sm:w-auto">
                        <div className="flex flex-col gap-1.5 w-full sm:min-w-[200px]">
                            <div className="flex justify-between text-[11px] font-bold uppercase tracking-wider">
                                <span className="text-muted-foreground">Verification Progress</span>
                                <span>{completion}%</span>
                            </div>
                            <div className="h-1.5 w-full overflow-hidden rounded-full bg-secondary">
                                <motion.div
                                    className="h-full bg-primary"
                                    initial={{ width: 0 }}
                                    animate={{ width: `${completion}%` }}
                                    transition={{ type: "spring", bounce: 0, duration: 1 }}
                                />
                            </div>
                        </div>
                        <Button
                            onClick={handleSave}
                            disabled={!state.isDirty || readyCount === 0 || state.isSaving}
                            className="h-11 px-6 md:px-8 font-bold shadow-xl shadow-primary/10 transition-all active:scale-95 w-full sm:w-auto"
                        >
                            {state.isSaving ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Check className="mr-2 h-4 w-4" />}
                            Commit Changes
                        </Button>
                    </div>
                </header>

                <div className="flex flex-col lg:flex-row gap-8 lg:gap-12">
                    <div className="lg:flex-1 space-y-8">
                        <div className="flex flex-col gap-3">
                            <Label className="text-xs font-black uppercase tracking-widest text-muted-foreground">Document Group</Label>
                            <Select value={state.category} onValueChange={(v) => dispatch({ type: "setCategory", value: v })}>
                                <SelectTrigger className="h-12 md:h-14 text-base md:text-lg border-2 hover:border-primary/40 bg-background transition-all">
                                    <SelectValue placeholder="Select classification..." />
                                </SelectTrigger>
                                <SelectContent>
                                    {documentCategories.map((c) => <SelectItem key={c} value={c}>{c}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-4">
                            <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 px-0">
                                <div className="flex items-center gap-3">
                                    <Checkbox
                                        checked={allSelected ? true : someSelected ? "indeterminate" : false}
                                        onCheckedChange={(v) => dispatch({ type: "selectAll", value: v === true })}
                                    />
                                    <span className="text-sm font-bold tracking-tight">File Queue ({state.rows.length})</span>
                                </div>
                                <Button variant="outline" size="sm" onClick={() => dispatch({ type: "addRow" })} className="h-8 rounded-full px-4 text-xs font-bold border-2">
                                    <Plus className="mr-1 h-3 w-3" /> New Entry
                                </Button>
                            </div>

                            <div className="flex flex-col gap-3">
                                <AnimatePresence mode="popLayout" initial={false}>
                                    {state.rows.map((row) => (
                                        <motion.div
                                            key={row.id}
                                            layout
                                            initial={{ opacity: 0, y: -5 }}
                                            animate={{ opacity: 1, y: 0 }}
                                            exit={{ opacity: 0, y: 5 }}
                                            className={cn(
                                                "flex flex-col sm:flex-row items-start sm:items-center gap-4 rounded-xl border-2 border-transparent p-4 transition-all hover:bg-muted/30",
                                                row.selected && "border-primary/10 bg-primary/[0.03]"
                                            )}
                                        >
                                            <Checkbox
                                                checked={row.selected}
                                                onCheckedChange={(v) => dispatch({ type: "toggleSelected", id: row.id, value: v === true })}
                                                className="mt-1 sm:mt-0"
                                            />

                                            <div className="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 w-full">
                                                <div className="sm:col-span-1 lg:col-span-5">
                                                    <Input
                                                        value={row.description}
                                                        onChange={(e) => dispatch({ type: "setDescription", id: row.id, value: e.target.value })}
                                                        placeholder="Untitled Document"
                                                        className="h-10 md:h-11 border-none bg-transparent text-sm md:text-base font-semibold shadow-none focus-visible:ring-0 px-0 placeholder:text-muted-foreground/30"
                                                    />
                                                </div>

                                                <div className="sm:col-span-1 lg:col-span-7 flex items-center gap-2 md:gap-3">
                                                    <Label
                                                        htmlFor={`file-${row.id}`}
                                                        className={cn(
                                                            "flex h-10 md:h-11 flex-1 cursor-pointer items-center justify-between rounded-lg border-2 border-dashed px-3 md:px-4 transition-all hover:bg-background",
                                                            row.file ? "border-primary/40 bg-background" : "border-muted-foreground/20",
                                                            row.error && "border-destructive/40 bg-destructive/[0.02]"
                                                        )}
                                                    >
                                                        <span className="truncate text-xs md:text-sm font-medium mr-2">
                                                            {row.file ? row.file.name : "Click to upload"}
                                                        </span>
                                                        {row.file ? <FileUp className="h-4 w-4 shrink-0 text-primary" /> : <Upload className="h-4 w-4 shrink-0 text-muted-foreground/50" />}
                                                    </Label>
                                                    <Input
                                                        id={`file-${row.id}`}
                                                        type="file"
                                                        className="sr-only"
                                                        onChange={(e) => {
                                                            const file = e.target.files?.[0] || null
                                                            dispatch({ type: "setFile", id: row.id, file, error: validateFile(file) })
                                                        }}
                                                    />
                                                    {state.rows.length > 1 && (
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            className="h-9 w-9 md:h-10 md:w-10 shrink-0 text-muted-foreground/40 hover:text-destructive hover:bg-destructive/5"
                                                            onClick={() => dispatch({ type: "deleteRow", id: row.id })}
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    )}
                                                </div>
                                            </div>
                                        </motion.div>
                                    ))}
                                </AnimatePresence>
                            </div>
                        </div>
                    </div>

                    <div className="lg:w-80 xl:w-96 shrink-0">
                        <div className="sticky top-6 space-y-6 md:space-y-8">
                            <section className="space-y-3 md:space-y-4">
                                <h3 className="text-[11px] font-black uppercase tracking-[0.15em] text-muted-foreground/80">Compliance Guide</h3>
                                <div className="space-y-1 md:space-y-2">
                                    {mandatoryDocuments.map((item) => (
                                        <div key={item} className="group flex items-center gap-3 rounded-lg p-2 transition-colors hover:bg-muted/50">
                                            <div className="h-1.5 w-1.5 rounded-full bg-primary/30 group-hover:bg-primary" />
                                            <span className="text-sm font-medium text-muted-foreground/80 group-hover:text-foreground">{item}</span>
                                        </div>
                                    ))}
                                </div>
                            </section>

                            <Separator className="opacity-50" />

                            <div className="rounded-xl md:rounded-2xl bg-secondary/30 p-4 md:p-6 space-y-3 md:space-y-4">
                                <div className="flex h-9 w-9 md:h-10 md:w-10 items-center justify-center rounded-xl bg-background">
                                    <Inbox className="h-4 w-4 md:h-5 md:w-5 text-primary" />
                                </div>
                                <div className="space-y-1">
                                    <p className="text-sm font-bold">Document Processing</p>
                                    <p className="text-xs leading-relaxed text-muted-foreground">
                                        All files are encrypted during transit. High resolution scans (300dpi) are recommended for OCR processing.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}
