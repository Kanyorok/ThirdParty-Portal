'use client'

import React, { useCallback, useMemo, useReducer, useState } from "react"
import {
    Check,
    Plus,
    Trash2,
    Upload,
    FileUp,
    Loader2,
    Eye,
    RefreshCw,
    Search,
    Sparkles,
    X
} from 'lucide-react'
import toast from "react-hot-toast"
import useSWR from "swr"

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
import { cn } from "@/lib/utils"
import { useDebounce } from "@/hooks/use-debounce"

type DocRow = {
    id: number
    description: string
    file: File | null
    selected: boolean
    error?: string | null
    uploadStatus?: "idle" | "uploading" | "success" | "error"
    uploadMessage?: string | null
    uploadedDocumentId?: string | number | null
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
    | { type: "setUploadState"; id: number; status: DocRow["uploadStatus"]; message?: string | null; documentId?: DocRow["uploadedDocumentId"] }
    | { type: "saving" }
    | { type: "saved"; keepDirty?: boolean }

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

const initialState: State = {
    category: "",
    rows: [{ id: 1, description: "", file: null, selected: false, uploadStatus: "idle", uploadMessage: null, uploadedDocumentId: null }],
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
                    {
                        id: state.nextId,
                        description: "",
                        file: null,
                        selected: false,
                        uploadStatus: "idle",
                        uploadMessage: null,
                        uploadedDocumentId: null,
                    },
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
        case "setUploadState":
            return {
                ...state,
                rows: state.rows.map((r) =>
                    r.id === action.id
                        ? {
                            ...r,
                            uploadStatus: action.status ?? "idle",
                            uploadMessage: action.message ?? null,
                            uploadedDocumentId: action.documentId ?? r.uploadedDocumentId ?? null,
                        }
                        : r
                ),
            }
        case "saving":
            return { ...state, isSaving: true }
        case "saved":
            return { ...state, isSaving: false, isDirty: Boolean(action.keepDirty), lastSavedAt: Date.now() }
        default:
            return state
    }
}

type DmsDoc = Record<string, any>

function resolveDocId(d: DmsDoc): string | number | null {
    return (
        d?.id ??
        d?.documentId ??
        d?.document_id ??
        d?.dmsDocumentId ??
        d?.dms_document_id ??
        null
    )
}

function resolveDocName(d: DmsDoc): string {
    return String(d?.name ?? d?.documentName ?? d?.document_name ?? d?.title ?? "Untitled").trim() || "Untitled"
}

function resolveDocRepository(d: DmsDoc): string | null {
    const v = String(d?.repository ?? d?.folder ?? d?.bucket ?? d?.repo ?? "").trim()
    return v || null
}

function resolveDocModifiedOn(d: DmsDoc): string | null {
    const v = String(d?.modifiedOn ?? d?.modified_on ?? d?.updatedAt ?? d?.updated_at ?? d?.createdOn ?? d?.created_on ?? "").trim()
    return v || null
}

function humanSize(bytes?: number | null) {
    if (!bytes || bytes <= 0) return "-"
    const units = ["B", "KB", "MB", "GB", "TB"]
    let i = 0
    let val = bytes
    while (val >= 1024 && i < units.length - 1) {
        val /= 1024
        i++
    }
    return `${val.toFixed(val >= 10 || i === 0 ? 0 : 1)} ${units[i]}`
}

function buildPreviewUrl(id: string | number) {
    const encoded = encodeURIComponent(String(id))
    return `/api/dms/preview?id=${encoded}&documentId=${encoded}&document_id=${encoded}`
}

async function uploadDocument({
    category,
    description,
    file,
}: {
    category: string
    description: string
    file: File
}) {
    const formData = new FormData()
    formData.append("file", file, file.name)
    formData.append("category", category)
    formData.append("name", description)
    formData.append("description", description)

    const res = await fetch("/api/dms/documents", {
        method: "POST",
        body: formData,
    })

    const text = await res.text()
    let json: any = null
    try {
        json = JSON.parse(text)
    } catch { }

    if (!res.ok) {
        throw new Error(json?.error || json?.message || `Upload failed (HTTP ${res.status})`)
    }

    return json ?? { raw: text }
}

export default function DocsUpload() {
    const [state, dispatch] = useReducer(reducer, initialState)

    const selectedCount = state.rows.filter((r) => r.selected).length
    const allSelected = state.rows.length > 0 && selectedCount === state.rows.length
    const someSelected = selectedCount > 0 && !allSelected
    const selectedValidCount = state.rows.filter((r) => r.selected && r.file && r.description && !r.error).length
    const canUpload = Boolean(state.category) && selectedValidCount > 0 && !state.isSaving

    const [docsQuery, setDocsQuery] = useState("")
    const [docsPage, setDocsPage] = useState(1)
    const docsLimit = 20
    const debouncedDocsQuery = useDebounce(docsQuery, 450)

    const validateFile = useCallback((file: File | null): string | null => {
        if (!file) return null
        const mb = file.size / 1024 / 1024
        if (mb > MAX_FILE_MB) return `Limit ${MAX_FILE_MB}MB`
        if (!ACCEPTED_TYPES.includes(file.type)) return "Invalid format"
        return null
    }, [])

    const docsKey = useMemo(() => {
        const params = new URLSearchParams()
        params.set("my", "1")
        params.set("page", String(docsPage))
        params.set("limit", String(docsLimit))
        if (debouncedDocsQuery.trim()) params.set("q", debouncedDocsQuery.trim())
        return [`/api/dms/documents?${params.toString()}`] as const
    }, [docsPage, docsLimit, debouncedDocsQuery])

    const {
        data: docsData,
        error: docsError,
        isLoading: docsLoading,
        isValidating: docsValidating,
        mutate: mutateDocs,
    } = useSWR(
        docsKey,
        async ([url]) => {
            const res = await fetch(url, { headers: { Accept: "application/json" } })
            if (!res.ok) {
                const err = await res.json().catch(() => ({}))
                throw new Error((err as any)?.error || `HTTP ${res.status}`)
            }
            return res.json()
        },
        { keepPreviousData: true }
    )

    const normalizedDocs = useMemo(() => {
        const raw = docsData as any
        const items =
            Array.isArray(raw?.data) ? raw.data :
                Array.isArray(raw?.items) ? raw.items :
                    Array.isArray(raw?.documents) ? raw.documents : []

        const total =
            raw?.total ??
            raw?.meta?.total ??
            raw?.pagination?.total ??
            (Array.isArray(items) ? items.length : 0)

        const pages =
            raw?.pages ??
            raw?.meta?.last_page ??
            raw?.pagination?.last_page ??
            raw?.lastPage ??
            1

        return {
            items: Array.isArray(items) ? (items as DmsDoc[]) : ([] as DmsDoc[]),
            total: Number(total || 0),
            pages: Number(pages || 1),
        }
    }, [docsData])

    const isFetchingDocs = docsLoading || docsValidating

    const handleSave = useCallback(async () => {
        if (!state.category) return toast.error("Select category")

        const targets = state.rows.filter(
            (r) => r.selected && r.file && r.description && !r.error
        ) as Array<DocRow & { file: File }>

        if (targets.length === 0) {
            return toast.error("Select at least one valid row to upload")
        }

        dispatch({ type: "saving" })

        let okCount = 0
        let failCount = 0

        for (const row of targets) {
            dispatch({ type: "setUploadState", id: row.id, status: "uploading", message: "Uploading..." })
            try {
                const result = await uploadDocument({
                    category: state.category,
                    description: row.description,
                    file: row.file,
                })
                const docId = resolveDocId(result?.data ?? result)
                okCount++
                dispatch({ type: "setUploadState", id: row.id, status: "success", message: "Uploaded", documentId: docId })
            } catch (e: any) {
                failCount++
                dispatch({ type: "setUploadState", id: row.id, status: "error", message: e?.message || "Upload failed" })
            }
        }

        dispatch({ type: "saved", keepDirty: failCount > 0 })

        if (okCount > 0) {
            toast.success(`Uploaded ${okCount} document${okCount === 1 ? "" : "s"}`)
            mutateDocs()
        }
        if (failCount > 0) {
            toast.error(`${failCount} upload${failCount === 1 ? "" : "s"} failed`)
        }
    }, [state.category, state.rows, mutateDocs])

    return (
        <div className="w-full space-y-8 antialiased overflow-x-hidden">
            <header className="space-y-6">
                <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div className="space-y-2.5">
                        <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200">
                            <Sparkles className="h-3.5 w-3.5 text-blue-600" />
                            <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">Documents</span>
                        </div>
                        <h1 className="text-3xl font-semibold tracking-tight text-slate-900">Upload & documents</h1>
                        <p className="text-sm text-slate-600">Upload files and preview what is already in your library.</p>
                    </div>

                    <Button
                        type="button"
                        onClick={handleSave}
                        disabled={!canUpload}
                        className="h-11 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-5 text-xs font-semibold transition-colors shadow-none disabled:opacity-50"
                    >
                        {state.isSaving ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Upload className="mr-2 h-4 w-4" />}
                        Upload selected ({selectedValidCount})
                    </Button>
                </div>
            </header>

            <div className="space-y-10">
                <div className="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div className="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                        <p className="text-[11px] font-black uppercase tracking-[0.2em] text-slate-600">
                            Upload queue
                        </p>
                        <p className="text-xs font-medium text-slate-500">
                            {selectedValidCount} ready • {selectedCount} selected
                        </p>
                    </div>

                    <div className="p-4 sm:p-6 space-y-8">
                        <div className="flex flex-col gap-3">
                            <Label className="text-[11px] font-semibold tracking-tight text-slate-600">Category</Label>
                            <Select value={state.category} onValueChange={(v) => dispatch({ type: "setCategory", value: v })}>
                                <SelectTrigger className="h-11 rounded-xl bg-white border border-slate-200 focus:border-blue-300 focus:ring-4 focus:ring-blue-50 transition-all">
                                    <SelectValue placeholder="Select category..." />
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
                                    <span className="text-sm font-semibold text-slate-900">File queue ({state.rows.length})</span>
                                </div>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => dispatch({ type: "addRow" })}
                                    className="h-9 rounded-xl px-4 text-xs font-semibold border border-slate-200 bg-white hover:bg-slate-50 shadow-none"
                                >
                                    <Plus className="mr-2 h-4 w-4" /> New entry
                                </Button>
                            </div>

                            <div className="flex flex-col gap-3">
                                {state.rows.map((row) => (
                                    <div
                                        key={row.id}
                                        className={cn(
                                            "flex flex-col sm:flex-row items-start sm:items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 transition-colors hover:bg-slate-50/50",
                                            row.selected && "border-blue-200 bg-blue-50/40"
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
                                                    placeholder="Document description"
                                                    className="h-11 rounded-xl bg-white border border-slate-200 focus:border-blue-300 focus:ring-4 focus:ring-blue-50 transition-all text-sm font-medium"
                                                />
                                            </div>

                                            <div className="sm:col-span-1 lg:col-span-7 flex items-center gap-2 md:gap-3">
                                                <Label
                                                    htmlFor={`file-${row.id}`}
                                                    className={cn(
                                                        "flex h-11 flex-1 cursor-pointer items-center justify-between rounded-xl border border-dashed px-4 transition-all bg-white",
                                                        row.file ? "border-blue-300" : "border-slate-200 hover:border-slate-300",
                                                        row.error && "border-rose-300 bg-rose-50/20"
                                                    )}
                                                >
                                                    <span className="truncate text-xs md:text-sm font-medium mr-2">
                                                        {row.file ? row.file.name : "Click to upload"}
                                                    </span>
                                                    {row.file ? <FileUp className="h-4 w-4 shrink-0 text-blue-600" /> : <Upload className="h-4 w-4 shrink-0 text-slate-400" />}
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
                                                {row.uploadStatus === "uploading" ? (
                                                    <span className="hidden lg:inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-blue-600">
                                                        <Loader2 className="h-3 w-3 animate-spin" />
                                                        Uploading
                                                    </span>
                                                ) : row.uploadStatus === "success" ? (
                                                    <span className="hidden lg:inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-600">
                                                        <Check className="h-3 w-3" />
                                                        Uploaded
                                                    </span>
                                                ) : row.uploadStatus === "error" ? (
                                                    <span className="hidden lg:inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-rose-600">
                                                        <Trash2 className="h-3 w-3" />
                                                        Failed
                                                    </span>
                                                ) : null}
                                                {state.rows.length > 1 && (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="h-9 w-9 md:h-10 md:w-10 shrink-0 text-slate-400 hover:text-rose-600 hover:bg-rose-50 shadow-none"
                                                        onClick={() => dispatch({ type: "deleteRow", id: row.id })}
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
                <div className="mt-10 md:mt-14 space-y-6">
                    <div className="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                        <div className="space-y-2">
                            <p className="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">
                                Library
                            </p>
                            <h2 className="text-xl md:text-2xl font-bold tracking-tight text-slate-900">
                                Your documents
                            </h2>
                            <p className="text-sm text-slate-600">
                                Search, preview, and confirm what is already uploaded.
                            </p>
                        </div>

                        <div className="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                            <div className="relative w-full sm:w-[340px] group">
                                <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors" strokeWidth={2} />
                                <Input
                                    value={docsQuery}
                                    onChange={(e) => {
                                        setDocsQuery(e.target.value)
                                        setDocsPage(1)
                                    }}
                                    placeholder="Search by name, repository, or date..."
                                    className="w-full pl-11 pr-10 h-11 rounded-xl bg-white border border-slate-200 focus:border-blue-300 focus:ring-4 focus:ring-blue-50 transition-all text-sm placeholder:text-slate-400"
                                />
                                <div className="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 rounded-lg flex items-center justify-center">
                                    {isFetchingDocs ? (
                                        <RefreshCw className="h-4 w-4 animate-spin text-blue-600" strokeWidth={2} />
                                    ) : docsQuery ? (
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setDocsQuery("")
                                                setDocsPage(1)
                                            }}
                                            className="h-7 w-7 rounded-lg hover:bg-slate-100 flex items-center justify-center transition-colors"
                                            aria-label="Clear search"
                                        >
                                            <X className="h-4 w-4 text-slate-400" strokeWidth={2} />
                                        </button>
                                    ) : null}
                                </div>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                className="h-11 rounded-xl shadow-none border-slate-200 bg-white hover:bg-slate-50"
                                onClick={() => mutateDocs()}
                                disabled={isFetchingDocs}
                            >
                                <RefreshCw className={cn("mr-2 h-4 w-4", isFetchingDocs && "animate-spin")} />
                                Refresh
                            </Button>
                        </div>
                    </div>

                    <div className="mt-6 rounded-2xl border border-slate-200 bg-white overflow-hidden">
                        <div className="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                            <p className="text-[11px] font-black uppercase tracking-[0.2em] text-slate-600">
                                Total: {normalizedDocs.total} • Page {docsPage} / {normalizedDocs.pages}
                            </p>
                        </div>

                        {docsError ? (
                            <div className="p-4 sm:p-6 text-sm text-rose-600">
                                {(docsError as any)?.message || "Failed to load documents"}
                            </div>
                        ) : isFetchingDocs && normalizedDocs.items.length === 0 ? (
                            <div className="p-4 sm:p-6 text-sm text-slate-600">Loading documents…</div>
                        ) : normalizedDocs.items.length === 0 ? (
                            <div className="p-4 sm:p-6 text-sm text-slate-600">No documents found.</div>
                        ) : (
                            <>
                                <div className="hidden md:block">
                                    <div className="overflow-hidden">
                                        <table className="w-full border-collapse table-fixed">
                                            <thead>
                                                <tr className="border-b border-slate-200 bg-slate-50">
                                                    <th className="h-10 px-4 sm:px-6 text-left text-[10px] font-semibold uppercase tracking-widest text-slate-700">
                                                        Name
                                                    </th>
                                                    <th className="h-10 px-4 text-left text-[10px] font-semibold uppercase tracking-widest text-slate-700 hidden md:table-cell">
                                                        Repository
                                                    </th>
                                                    <th className="h-10 px-4 text-left text-[10px] font-semibold uppercase tracking-widest text-slate-700 hidden lg:table-cell">
                                                        Size
                                                    </th>
                                                    <th className="h-10 px-4 text-left text-[10px] font-semibold uppercase tracking-widest text-slate-700 hidden lg:table-cell">
                                                        Updated
                                                    </th>
                                                    <th className="h-10 px-4 sm:px-6 text-right text-[10px] font-semibold uppercase tracking-widest text-slate-700">
                                                        Action
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-slate-100">
                                                {normalizedDocs.items.map((doc) => {
                                                    const id = resolveDocId(doc)
                                                    const name = resolveDocName(doc)
                                                    const repo = resolveDocRepository(doc)
                                                    const modifiedOn = resolveDocModifiedOn(doc)
                                                    const size = Number(doc?.size ?? doc?.file_size ?? doc?.bytes ?? 0) || 0

                                                    return (
                                                        <tr key={String(id ?? name)} className="hover:bg-slate-50/50">
                                                            <td className="px-4 sm:px-6 py-4 align-top">
                                                                <div className="flex flex-col gap-1">
                                                                    <span className="text-sm font-semibold leading-tight line-clamp-2">{name}</span>
                                                                </div>
                                                            </td>
                                                            <td className="px-4 py-4 align-top hidden md:table-cell">
                                                                <span className="text-sm text-slate-600 break-words">{repo || "-"}</span>
                                                            </td>
                                                            <td className="px-4 py-4 align-top hidden lg:table-cell">
                                                                <span className="text-sm text-slate-600">{humanSize(size)}</span>
                                                            </td>
                                                            <td className="px-4 py-4 align-top hidden lg:table-cell">
                                                                <span className="text-sm text-slate-600">
                                                                    {modifiedOn ? new Date(modifiedOn).toLocaleString() : "-"}
                                                                </span>
                                                            </td>
                                                            <td className="px-4 sm:px-6 py-4 text-right align-top">
                                                                {id ? (
                                                                    <Button
                                                                        asChild
                                                                        variant="outline"
                                                                        size="sm"
                                                                        className="h-9 rounded-xl text-xs font-semibold border-slate-200 bg-white hover:bg-slate-50 shadow-none"
                                                                    >
                                                                        <a href={buildPreviewUrl(id)} target="_blank" rel="noreferrer">
                                                                            <Eye className="mr-2 h-4 w-4" />
                                                                            Preview
                                                                        </a>
                                                                    </Button>
                                                                ) : (
                                                                    <span className="text-xs text-slate-400">—</span>
                                                                )}
                                                            </td>
                                                        </tr>
                                                    )
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div className="md:hidden divide-y divide-slate-100">
                                    {normalizedDocs.items.map((doc) => {
                                        const id = resolveDocId(doc)
                                        const name = resolveDocName(doc)
                                        const repo = resolveDocRepository(doc)
                                        const modifiedOn = resolveDocModifiedOn(doc)
                                        const size = Number(doc?.size ?? doc?.file_size ?? doc?.bytes ?? 0) || 0

                                        return (
                                            <div key={String(id ?? name)} className="p-4 space-y-2">
                                                <div className="text-sm font-semibold text-slate-900 leading-snug line-clamp-2">
                                                    {name}
                                                </div>
                                                <div className="text-xs text-slate-600 space-y-1">
                                                    <div className="break-words">{repo || "-"}</div>
                                                    <div>
                                                        {humanSize(size)} • {modifiedOn ? new Date(modifiedOn).toLocaleString() : "-"}
                                                    </div>
                                                </div>
                                                {id ? (
                                                    <Button
                                                        asChild
                                                        variant="outline"
                                                        size="sm"
                                                        className="h-9 w-full rounded-xl text-xs font-semibold border-slate-200 bg-white hover:bg-slate-50 shadow-none"
                                                    >
                                                        <a href={buildPreviewUrl(id)} target="_blank" rel="noreferrer">
                                                            <Eye className="mr-2 h-4 w-4" />
                                                            Preview
                                                        </a>
                                                    </Button>
                                                ) : null}
                                            </div>
                                        )
                                    })}
                                </div>
                            </>
                        )}

                        <div className="flex items-center justify-between px-4 sm:px-6 py-4 border-t border-slate-200 bg-white">
                            <Button
                                type="button"
                                variant="outline"
                                className="h-9 rounded-xl shadow-none border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold"
                                disabled={docsPage <= 1 || isFetchingDocs}
                                onClick={() => setDocsPage((p) => Math.max(1, p - 1))}
                            >
                                Prev
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                className="h-9 rounded-xl shadow-none border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold"
                                disabled={docsPage >= normalizedDocs.pages || isFetchingDocs}
                                onClick={() => setDocsPage((p) => Math.min(normalizedDocs.pages, p + 1))}
                            >
                                Next
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}
