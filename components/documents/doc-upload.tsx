'use client'

import React, { useCallback, useMemo, useReducer } from "react"
import { AnimatePresence, motion } from "framer-motion"
import { Check, ChevronRight, FileText, Info, Plus, Trash2, Upload } from 'lucide-react'
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
import { Badge } from "@/components/common/badge"
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
    "Scanned Copy of Original National ID",
    "Business Registration Certificate",
    "Registration certificate (community service provider)",
    "Partnership Deed (partnership business)",
    "Copy of CR 12 (directors/shareholders)",
    "Power of Attorney (authorized person)",
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
                rows: [
                    ...state.rows,
                    { id: state.nextId, description: "", file: null, selected: false },
                ],
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
                rows: state.rows.map((r) =>
                    r.id === action.id ? { ...r, selected: action.value } : r
                ),
            }
        case "selectAll":
            return {
                ...state,
                rows: state.rows.map((r) => ({ ...r, selected: action.value })),
            }
        case "setDescription":
            return {
                ...state,
                rows: state.rows.map((r) =>
                    r.id === action.id ? { ...r, description: action.value } : r
                ),
                isDirty: true,
            }
        case "setFile":
            return {
                ...state,
                rows: state.rows.map((r) =>
                    r.id === action.id ? { ...r, file: action.file, error: action.error ?? null } : r
                ),
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

    const selectedCount = useMemo(
        () => state.rows.filter((r) => r.selected).length,
        [state.rows]
    )
    const allSelected = state.rows.length > 0 && selectedCount === state.rows.length
    const someSelected = selectedCount > 0 && !allSelected
    const readyCount = useMemo(
        () => state.rows.filter((r) => r.file && r.description && !r.error).length,
        [state.rows]
    )
    const completion = Math.round((readyCount / Math.max(state.rows.length, 1)) * 100)

    const validateFile = useCallback((file: File | null): string | null => {
        if (!file) return null
        const mb = file.size / 1024 / 1024
        if (mb > MAX_FILE_MB) return `File too large. Max ${MAX_FILE_MB}MB.`
        if (!ACCEPTED_TYPES.includes(file.type)) return "Unsupported file type."
        return null
    }, [])

    const onDropFile = useCallback(
        (id: number, file: File | null) => {
            const error = validateFile(file)
            dispatch({ type: "setFile", id, file, error })
        },
        [validateFile]
    )

    const handleSave = useCallback(async () => {
        if (!state.category) {
            toast.error("Please select a document category first.")
            return
        }
        dispatch({ type: "saving" })
        await new Promise((r) => setTimeout(r, 800))
        dispatch({ type: "saved" })
        toast.success("Documents saved successfully!")
    }, [state.category])

    const canSave = state.isDirty && readyCount > 0 && state.category && !state.isSaving

    return (
        <div className="w-full overflow-x-hidden">
            <div className="mb-8 text-sm text-gray-500 dark:text-gray-400">
                Dashboard <ChevronRight className="inline-block h-3 w-3 mx-1" /> <span className="font-semibold text-gray-700 dark:text-gray-200">Documents</span>
            </div>
            <div className="mb-4">
                <h1 className="text-2xl font-bold tracking-tight text-foreground">My Documents</h1>
            </div>

            <section className="grid items-center gap-3 rounded-lg border bg-card p-3 md:grid-cols-[minmax(0,1fr)_auto]">
                <div className="min-w-0">
                    <div className="flex items-center gap-2">
                        <div className="flex h-7 w-7 items-center justify-center rounded-full bg-primary/10">
                            <FileText className="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div className="min-w-0 flex-1">
                            <div className="flex items-center justify-between gap-3">
                                <span className="truncate text-xs font-medium">
                                    {readyCount} of {state.rows.length} documents ready
                                </span>
                                <span className="text-[10px] text-muted-foreground">{completion}%</span>
                            </div>
                            <div className="mt-1.5 h-1 w-full rounded-full bg-secondary">
                                <motion.div
                                    className="h-full rounded-full bg-primary"
                                    initial={{ width: 0 }}
                                    animate={{ width: `${completion}%` }}
                                    transition={{ duration: 0.35, ease: "easeOut" }}
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div className="min-w-0">
                    <Label htmlFor="category" className="sr-only">
                        Document Category
                    </Label>
                    <Select
                        value={state.category}
                        onValueChange={(value) => dispatch({ type: "setCategory", value })}
                    >
                        <SelectTrigger id="category" className="h-9 max-w-full min-w-0 truncate">
                            <SelectValue placeholder="Select document category" />
                        </SelectTrigger>
                        <SelectContent className="w-[--radix-select-trigger-width] max-h-64">
                            {documentCategories.map((c) => (
                                <SelectItem key={c} value={c} className="whitespace-normal text-sm">
                                    {c}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
            </section>

            <div className="mx-auto mt-4 grid w-full max-w-6xl grid-cols-1 gap-4 lg:grid-cols-12">
                <section className="min-w-0 space-y-3 lg:col-span-8">
                    <div className="flex items-center justify-between gap-2 rounded-lg border bg-card px-3 py-2">
                        <div className="flex min-w-0 items-center gap-2">
                            <Checkbox
                                checked={allSelected ? true : someSelected ? "indeterminate" : false}
                                onCheckedChange={(v) => dispatch({ type: "selectAll", value: v === true })}
                                aria-label="Select all rows"
                            />
                            <span className="truncate text-sm font-medium">Documents</span>
                            {selectedCount > 0 && (
                                <Badge variant="secondary" className="ml-1 truncate rounded-full text-xs">
                                    {selectedCount} selected
                                </Badge>
                            )}
                        </div>
                        <div className="flex items-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                className="gap-1"
                                onClick={() => dispatch({ type: "addRow" })}
                            >
                                <Plus className="h-4 w-4" />
                                <span className="hidden sm:inline">Add</span>
                            </Button>
                            <Button
                                size="sm"
                                className="gap-2"
                                onClick={handleSave}
                                disabled={!canSave}
                                aria-label="Save documents"
                            >
                                {state.isSaving ? (
                                    <svg className="h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                ) : (
                                    <span>Save</span>
                                )}
                            </Button>
                        </div>
                    </div>

                    <div className="space-y-2">
                        <AnimatePresence mode="popLayout">
                            {state.rows.map((row) => (
                                <motion.div
                                    key={row.id}
                                    layout
                                    initial={{ opacity: 0, y: -6 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    exit={{ opacity: 0, y: -6 }}
                                    transition={{ duration: 0.18, ease: "easeOut" }}
                                    className={cn(
                                        "rounded-lg border bg-card p-3",
                                        row.selected && "border-primary/30 bg-primary/5"
                                    )}
                                >
                                    <div className="grid grid-cols-1 gap-3 md:grid-cols-[auto_minmax(0,1fr)_minmax(0,1fr)_auto]">
                                        <div className="md:pt-2">
                                            <Checkbox
                                                checked={row.selected}
                                                onCheckedChange={(v) =>
                                                    dispatch({
                                                        type: "toggleSelected",
                                                        id: row.id,
                                                        value: v === true,
                                                    })
                                                }
                                                aria-label={`Select row ${row.id}`}
                                            />
                                        </div>

                                        <div className="min-w-0">
                                            <Label
                                                htmlFor={`file-${row.id}`}
                                                className={cn(
                                                    "group relative flex min-h-12 w-full cursor-pointer items-center gap-3 rounded-lg border-2 border-dashed p-3 transition-colors",
                                                    row.file
                                                        ? "border-primary/30 bg-primary/5 hover:bg-primary/10"
                                                        : "hover:border-primary/50 hover:bg-accent/50"
                                                )}
                                                onDragOver={(e) => e.preventDefault()}
                                                onDrop={(e) => {
                                                    e.preventDefault()
                                                    const file = e.dataTransfer.files?.[0] || null
                                                    onDropFile(row.id, file)
                                                }}
                                            >
                                                {row.file ? (
                                                    <>
                                                        <div className="flex h-7 w-7 items-center justify-center rounded-full bg-primary/10">
                                                            <Check className="h-4 w-4 text-primary" />
                                                        </div>
                                                        <div className="min-w-0 flex-1">
                                                            <p className="truncate text-sm font-medium">{row.file.name}</p>
                                                            <p className="text-[11px] text-muted-foreground">
                                                                {(row.file.size / 1024 / 1024).toFixed(1)} MB
                                                            </p>
                                                        </div>
                                                    </>
                                                ) : (
                                                    <>
                                                        <div className="flex h-7 w-7 items-center justify-center rounded-full bg-muted group-hover:bg-primary/10">
                                                            <Upload className="h-4 w-4 text-muted-foreground group-hover:text-primary" />
                                                        </div>
                                                        <div className="min-w-0">
                                                            <p className="text-sm font-medium text-muted-foreground group-hover:text-foreground">
                                                                Choose file
                                                            </p>
                                                            <p className="text-[11px] text-muted-foreground">
                                                                PDF, JPG, PNG, XLSX • Max {MAX_FILE_MB}MB
                                                            </p>
                                                        </div>
                                                    </>
                                                )}
                                            </Label>
                                            <Input
                                                id={`file-${row.id}`}
                                                type="file"
                                                className="sr-only"
                                                accept=".pdf,.jpg,.jpeg,.png,.xlsx"
                                                onChange={(e) => onDropFile(row.id, e.target.files?.[0] || null)}
                                            />
                                            {row.error && (
                                                <p className="mt-1 text-[11px] text-destructive">{row.error}</p>
                                            )}
                                        </div>

                                        <div className="min-w-0">
                                            <Label htmlFor={`desc-${row.id}`} className="sr-only">
                                                Document description
                                            </Label>
                                            <Input
                                                id={`desc-${row.id}`}
                                                value={row.description}
                                                onChange={(e) =>
                                                    dispatch({
                                                        type: "setDescription",
                                                        id: row.id,
                                                        value: e.target.value,
                                                    })
                                                }
                                                placeholder="e.g. Audited Financial Statements 2023"
                                                className="border-0 bg-muted/50 focus-visible:bg-background"
                                            />
                                        </div>

                                        <div className="flex items-start justify-end">
                                            {state.rows.length > 1 && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="h-8 w-8 text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                                                    aria-label={`Delete row ${row.id}`}
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

                    <p className="px-1 pt-1 text-[11px] text-muted-foreground">
                        Allowed types: PDF, JPG, JPEG, PNG, XLSX • Max size: {MAX_FILE_MB} MB
                    </p>
                </section>

                <aside className="min-w-0 space-y-4 lg:col-span-4">
                    <div className="sticky top-20 space-y-4">
                        <div className="rounded-lg border bg-card p-3">
                            <div className="mb-2 flex items-center gap-2">
                                <Info className="h-4 w-4 text-primary" />
                                <span className="text-sm font-medium">Recommended Documents</span>
                            </div>
                            <ul className="space-y-1.5">
                                {mandatoryDocuments.map((item) => (
                                    <li key={item} className="flex items-start gap-2">
                                        <span className="mt-2 inline-block h-1.5 w-1.5 flex-shrink-0 rounded-full bg-primary/60" />
                                        <span className="text-sm text-muted-foreground">{item}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <div className="rounded-lg border bg-card p-3">
                            <span className="text-sm font-medium">Tips</span>
                            <Separator className="my-2" />
                            <ul className="space-y-1.5">
                                <li className="text-sm text-muted-foreground">
                                    Use clear descriptions to speed up reviews.
                                </li>
                                <li className="text-sm text-muted-foreground">
                                    Prefer PDFs for multi-page documents.
                                </li>
                                <li className="text-sm text-muted-foreground">
                                    Keep scanned images under {MAX_FILE_MB}MB for faster uploads.
                                </li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    )
}