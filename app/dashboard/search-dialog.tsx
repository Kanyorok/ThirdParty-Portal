"use client"

import { memo, useCallback, useEffect, useMemo, useState, useTransition } from "react"
import { useRouter } from "next/navigation"
import { AnimatePresence, motion } from "framer-motion"
import {
    AlertCircle,
    BriefcaseBusiness,
    Clock,
    Eraser,
    File,
    FileStack,
    LayoutGrid,
    Search,
    Target,
} from "lucide-react"

import {
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from "@/components/common/command"
import { Button } from "@/components/common/button"
import { Spinner } from "@/components/common/spinner"
import { cn } from "@/lib/utils"
import { getFlatNavItems } from "@/utils/navigation"

type NavItem = {
    href: string
    label: string
    icon?: React.ComponentType<{ className?: string }>
    group?: string
    description?: string
    keywords?: string[]
}

type RemoteResult = {
    id: string | number
    type: string
    title: string
    description?: string
    href?: string
    source?: string
    badge?: string
    meta?: Record<string, unknown>
}

type SearchApiResponse = {
    data?: RemoteResult[]
    degraded?: boolean
    minimumCharacters?: number
    queryTooShort?: boolean
}

type GroupedItems = { group: string; items: NavItem[] }

type ResultVisual = {
    label: string
    icon: React.ComponentType<{ className?: string }>
    accent: string
}

const SEARCH_SHORTCUT = { key: "j" } as const
const SEARCH_CONFIG = {
    placeholder: "Search tenders, RFQs, documents, or pages...",
    emptyMessage: "No matching results.",
    debounceMs: 180,
    recentLimit: 6,
    queryHistoryLimit: 5,
    minimumCharacters: 2,
} as const

const RECENT_ITEMS_KEY = "search:recent-items"
const RECENT_QUERIES_KEY = "search:recent-queries"
const RESULT_VISUALS: Record<string, ResultVisual> = {
    tender: { label: "Tender", icon: BriefcaseBusiness, accent: "border-amber-500/20 bg-amber-500/10 text-amber-700" },
    rfq: { label: "RFQ", icon: Target, accent: "border-emerald-500/20 bg-emerald-500/10 text-emerald-700" },
    document: { label: "Document", icon: FileStack, accent: "border-sky-500/20 bg-sky-500/10 text-sky-700" },
}

function SearchSkeleton() {
    return (
        <div className="space-y-4 p-2">
            <div className="space-y-2">
                <div className="mx-2 mb-3 h-3 w-28 rounded bg-muted/60" />
                {[...Array(3)].map((_, i) => (
                    <div key={i} className="flex items-center gap-3 rounded-xl border border-border/50 bg-card/80 p-2.5">
                        <div className="size-9 shrink-0 animate-pulse rounded-xl bg-muted/60" />
                        <div className="flex min-w-0 flex-1 flex-col gap-2">
                            <div className="h-2.5 w-1/3 animate-pulse rounded bg-muted/60" />
                            <div className="h-2 w-2/3 animate-pulse rounded bg-muted/60" />
                        </div>
                    </div>
                ))}
            </div>
        </div>
    )
}

function scoreItem(q: string, item: NavItem): number {
    if (!q) return 0
    const query = q.toLowerCase()
    const label = item.label.toLowerCase()
    const hay = [item.label, item.description || "", ...(item.keywords || [])].join(" ").toLowerCase()
    let score = 0
    if (label === query) score += 80
    if (label.startsWith(query)) score += 50
    if (label.includes(query)) score += 30
    if (hay.includes(query)) score += 20
    query.split(/\s+/).filter(Boolean).forEach((token) => {
        if (label.startsWith(token)) score += 12
        if (hay.includes(token)) score += 8
    })
    return score
}

function useDebounced<T>(value: T, delay = 120) {
    const [v, setV] = useState(value)
    useEffect(() => {
        const id = setTimeout(() => setV(value), delay)
        return () => clearTimeout(id)
    }, [value, delay])
    return v
}

function useLocalStorageList(key: string, limit: number) {
    const [list, setList] = useState<string[]>([])

    useEffect(() => {
        try {
            const raw = localStorage.getItem(key)
            if (!raw) return
            const parsed = JSON.parse(raw)
            if (Array.isArray(parsed)) {
                setList(parsed.filter((value): value is string => typeof value === "string"))
            }
        } catch {
            localStorage.removeItem(key)
        }
    }, [key])

    const add = useCallback((value: string) => {
        setList((prev) => {
            const next = [value, ...prev.filter((currentValue) => currentValue !== value)].slice(0, limit)
            localStorage.setItem(key, JSON.stringify(next))
            return next
        })
    }, [key, limit])

    const clear = useCallback(() => {
        localStorage.removeItem(key)
        setList([])
    }, [key])

    return { list, add, clear }
}

function Highlight({ text, query }: { text: string; query: string }) {
    if (!query) return <>{text}</>
    const q = query.toLowerCase()
    const idx = text.toLowerCase().indexOf(q)
    if (idx === -1) return <>{text}</>
    const match = text.slice(idx, idx + q.length)
    return (
        <>
            {text.slice(0, idx)}
            <mark className="rounded-sm bg-primary/20 px-0.5 text-primary">{match}</mark>
            {text.slice(idx + q.length)}
        </>
    )
}

function getResultVisual(type: string): ResultVisual {
    return RESULT_VISUALS[type] || { label: "Page", icon: LayoutGrid, accent: "border-border/70 bg-muted/60 text-foreground" }
}

function getKeyboardShortcutLabel() {
    if (typeof navigator === "undefined") return "Ctrl J"
    return /mac|iphone|ipad|ipod/i.test(navigator.platform) ? "⌘ J" : "Ctrl J"
}

function isExternalHref(href: string) {
    return /^https?:\/\//i.test(href)
}

function getRemoteMeta(result: RemoteResult) {
    const values = [result.badge, result.source]

    if (result.meta?.status && typeof result.meta.status === "string") {
        values.push(result.meta.status)
    }

    if (result.meta?.submissionDeadline) {
        values.push(`Due ${String(result.meta.submissionDeadline)}`)
    }

    return values.filter(Boolean).slice(0, 3) as string[]
}

export const SearchButton = memo(({ onClick, className }: { onClick?: () => void; className?: string }) => (
    <Button
        variant="outline"
        className={cn(
            "h-10 w-full justify-start gap-3 rounded-full border-border/70 bg-card px-3 text-left text-muted-foreground shadow-none transition-colors hover:border-border hover:bg-accent/40 hover:text-foreground sm:w-[22rem]",
            className
        )}
        onClick={onClick}
    >
        <Search className="size-3.5" />
        <span className="truncate text-[13px] font-medium tracking-tight">Search</span>
        <kbd className="ml-auto hidden items-center gap-1 rounded-full border border-border/60 bg-muted/50 px-2 py-0.5 text-[11px] font-semibold text-muted-foreground sm:inline-flex">
            {getKeyboardShortcutLabel()}
        </kbd>
    </Button>
))
SearchButton.displayName = "SearchButton"

export const SearchDialog = memo(() => {
    const router = useRouter()
    const [open, setOpen] = useState(false)
    const [rawQuery, setRawQuery] = useState("")
    const debouncedQuery = useDebounced(rawQuery, SEARCH_CONFIG.debounceMs)
    const [isPending, startTransition] = useTransition()

    const [remoteResults, setRemoteResults] = useState<RemoteResult[] | null>(null)
    const [remoteLoading, setRemoteLoading] = useState(false)
    const [isError, setIsError] = useState(false)
    const [isDegraded, setIsDegraded] = useState(false)

    const { list: recentHrefs, add: addRecentItem } = useLocalStorageList(RECENT_ITEMS_KEY, SEARCH_CONFIG.recentLimit)
    const { list: recentQueries, add: addRecentQuery, clear: clearQueries } = useLocalStorageList(RECENT_QUERIES_KEY, SEARCH_CONFIG.queryHistoryLimit)

    const items = useMemo(() => getFlatNavItems() || [], [])
    const trimmedQuery = rawQuery.trim()
    const canRunRemoteSearch = trimmedQuery.length >= SEARCH_CONFIG.minimumCharacters

    useEffect(() => {
        const down = (e: KeyboardEvent) => {
            if (e.key === SEARCH_SHORTCUT.key && (e.metaKey || e.ctrlKey)) {
                e.preventDefault()
                setOpen((currentOpen) => !currentOpen)
            }
        }
        document.addEventListener("keydown", down)
        return () => document.removeEventListener("keydown", down)
    }, [])

    useEffect(() => {
        const q = debouncedQuery.trim()
        if (!q) {
            setRemoteResults(null)
            setIsError(false)
            setIsDegraded(false)
            return
        }

        if (q.length < SEARCH_CONFIG.minimumCharacters) {
            setRemoteResults(null)
            setIsError(false)
            setIsDegraded(false)
            return
        }

        const controller = new AbortController()
        setRemoteLoading(true)
        setIsError(false)
        setIsDegraded(false)

        fetch(`/api/search?q=${encodeURIComponent(q)}&limit=8`, { signal: controller.signal, cache: "no-store" })
            .then(async (res) => {
                if (!res.ok) throw new Error()
                const data = await res.json() as SearchApiResponse
                setRemoteResults(data?.data || [])
                setIsDegraded(Boolean(data?.degraded))
            })
            .catch((error: unknown) => {
                if (controller.signal.aborted) return
                console.error("Search request failed", error)
                setIsError(true)
            })
            .finally(() => {
                if (!controller.signal.aborted) setRemoteLoading(false)
            })

        return () => controller.abort()
    }, [debouncedQuery])

    const filteredGroups = useMemo(() => {
        const q = debouncedQuery.trim()
        if (!q) {
            const initial: GroupedItems[] = []
            const recents = recentHrefs.map((href) => items.find((item) => item.href === href)).filter(Boolean) as NavItem[]
            if (recents.length) initial.push({ group: "Recent Activity", items: recents })
            return initial
        }

        const scored = items
            .map((item) => ({ item, score: scoreItem(q, item) }))
            .filter((result) => result.score > 0)
            .sort((left, right) => right.score - left.score)
            .map((result) => result.item)

        const grouped = new Map<string, NavItem[]>()
        scored.forEach((item) => {
            const group = item.group || "General"
            if (!grouped.has(group)) grouped.set(group, [])
            grouped.get(group)?.push(item)
        })

        return Array.from(grouped.entries()).map(([group, groupItems]) => ({ group, items: groupItems }))
    }, [debouncedQuery, items, recentHrefs])

    const remoteCount = remoteResults?.length ?? 0
    const localCount = filteredGroups.reduce((count, group) => count + group.items.length, 0)

    const onSelect = useCallback((href: string) => {
        setOpen(false)
        addRecentItem(href)
        if (rawQuery.trim()) addRecentQuery(rawQuery.trim())

        if (isExternalHref(href)) {
            window.open(href, "_blank", "noopener,noreferrer")
            return
        }

        startTransition(() => router.push(href))
    }, [router, rawQuery, addRecentItem, addRecentQuery])

    return (
        <>
            <SearchButton onClick={() => setOpen(true)} />

            <CommandDialog open={open} onOpenChange={setOpen} className="max-w-2xl overflow-hidden border-0 bg-transparent p-0 shadow-none">
                <div className="overflow-hidden rounded-[1.25rem] border border-border/70 bg-popover">
                    <div className="border-b border-border/70 px-4 py-3">
                        <CommandInput
                            placeholder={SEARCH_CONFIG.placeholder}
                            value={rawQuery}
                            onValueChange={setRawQuery}
                            className="h-11 w-full bg-transparent text-sm font-medium tracking-tight placeholder:text-muted-foreground/70 focus:outline-none"
                        />

                        <div className="mt-2 flex items-center justify-between gap-3 px-1 text-[11px] text-muted-foreground">
                            <span>
                                {trimmedQuery
                                    ? `${remoteCount + localCount} results`
                                    : `Recent and live search`}
                            </span>
                            {(isPending || remoteLoading) && <Spinner className="ml-auto size-3" />}
                        </div>
                    </div>

                    <CommandList className="max-h-[420px] p-2">
                        <CommandEmpty className="py-8 text-center text-sm text-muted-foreground">
                            {canRunRemoteSearch
                                ? SEARCH_CONFIG.emptyMessage
                                : `Type at least ${SEARCH_CONFIG.minimumCharacters} characters to search live records.`}
                        </CommandEmpty>

                        {(isError || isDegraded) && (
                            <div className="mx-2 mb-3 flex items-center gap-2 rounded-xl border border-destructive/25 bg-destructive/10 px-3 py-2.5">
                                <AlertCircle className="size-3.5 text-destructive" />
                                <span className="text-xs font-medium text-destructive">
                                    {isError
                                        ? "Live search unavailable."
                                        : "Some results are unavailable."}
                                </span>
                            </div>
                        )}

                        {!trimmedQuery && recentQueries.length > 0 && (
                            <div className="mb-4 px-2 pt-2">
                                <div className="mb-2 flex items-center justify-between">
                                    <span className="text-[11px] font-semibold text-muted-foreground">Recent searches</span>
                                    <Button variant="ghost" size="sm" onClick={clearQueries} className="h-7 rounded-full px-2.5 text-[11px] font-semibold text-destructive hover:bg-destructive/10">
                                        <Eraser className="mr-1 size-3" /> Clear
                                    </Button>
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    {recentQueries.map((query) => (
                                        <button
                                            key={query}
                                            onClick={() => setRawQuery(query)}
                                            className="flex items-center gap-1.5 rounded-full border border-border/70 bg-card px-2.5 py-1 text-[11px] font-medium text-foreground/90 transition-colors hover:bg-accent/60"
                                        >
                                            <Clock className="size-3 text-muted-foreground" />
                                            {query}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        {!!trimmedQuery && !canRunRemoteSearch && (
                            <div className="mx-2 mb-3 rounded-xl border border-border/70 bg-card/70 px-3 py-2.5 text-xs text-muted-foreground">
                                Keep typing to search live tenders, RFQs, and documents. Navigation shortcuts stay available below.
                            </div>
                        )}

                        <AnimatePresence mode="popLayout">
                            {remoteLoading && canRunRemoteSearch && (
                                <motion.div key="skeleton" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}>
                                    <SearchSkeleton />
                                </motion.div>
                            )}

                            {!remoteLoading && remoteResults && remoteResults.length > 0 && canRunRemoteSearch && (
                                <motion.div key="remote" initial={{ opacity: 0 }} animate={{ opacity: 1 }}>
                                    <CommandGroup heading={<span className="px-2 text-[11px] font-semibold text-muted-foreground">Live ERP results</span>}>
                                        {remoteResults.map((result) => {
                                            const visual = getResultVisual(result.type)
                                            const Icon = visual.icon

                                            return (
                                                <CommandItem
                                                    key={`${result.type}-${result.id}`}
                                                    value={`${result.type} ${result.title} ${result.description || ""} ${result.source || ""} ${result.badge || ""}`}
                                                    onSelect={() => result.href && onSelect(result.href)}
                                                    className="group flex cursor-pointer items-center gap-3 rounded-xl border border-transparent p-2.5 aria-selected:border-primary/15 aria-selected:bg-primary/5"
                                                >
                                                    <div className="flex size-9 items-center justify-center rounded-xl border border-border/70 bg-card transition-colors">
                                                        <Icon className="size-3.5 text-muted-foreground group-aria-selected:text-foreground" />
                                                    </div>
                                                    <div className="flex min-w-0 flex-1 flex-col">
                                                        <div className="flex items-center gap-2">
                                                            <span className="text-sm font-semibold tracking-tight text-foreground">
                                                                <Highlight text={result.title} query={rawQuery} />
                                                            </span>
                                                            <span className={cn("rounded-full border px-2 py-0.5 text-[10px] font-semibold", visual.accent)}>{visual.label}</span>
                                                        </div>
                                                        <span className="truncate text-xs text-muted-foreground">
                                                            {[result.description, ...getRemoteMeta(result)].filter(Boolean).join(" • ")}
                                                        </span>
                                                    </div>
                                                </CommandItem>
                                            )
                                        })}
                                    </CommandGroup>
                                </motion.div>
                            )}

                            {filteredGroups.map(({ group, items: groupItems }, idx) => (
                                <motion.div
                                    key={group}
                                    initial={{ opacity: 0, y: 5 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ delay: idx * 0.05 }}
                                >
                                    <CommandGroup heading={<span className="px-2 text-[11px] font-semibold text-muted-foreground">{group}</span>} className="mb-2">
                                        {groupItems.map((item) => (
                                            <CommandItem
                                                key={item.href}
                                                value={`${item.label} ${item.group} ${item.description || ""} ${(item.keywords || []).join(" ")}`}
                                                onSelect={() => onSelect(item.href)}
                                                className="group flex cursor-pointer items-center gap-3 rounded-xl border border-transparent p-2.5 aria-selected:border-primary/15 aria-selected:bg-primary/5"
                                            >
                                                <div className="flex size-9 items-center justify-center rounded-xl border border-border/70 bg-card transition-colors">
                                                    {item.icon ? <item.icon className="size-3.5 text-muted-foreground group-aria-selected:text-foreground" /> : <File className="size-3.5 text-muted-foreground" />}
                                                </div>
                                                <div className="flex min-w-0 flex-1 flex-col">
                                                    <span className="text-sm font-semibold tracking-tight text-foreground">
                                                        <Highlight text={item.label} query={rawQuery} />
                                                    </span>
                                                    <span className="truncate text-xs text-muted-foreground">{item.description || item.group}</span>
                                                </div>
                                                <kbd className="ml-auto hidden rounded-full border border-border/60 bg-card px-2 py-0.5 text-[10px] font-semibold text-muted-foreground group-aria-selected:block">Enter</kbd>
                                            </CommandItem>
                                        ))}
                                    </CommandGroup>
                                </motion.div>
                            ))}
                        </AnimatePresence>
                    </CommandList>

                    <div className="flex flex-wrap items-center justify-between gap-3 border-t border-border/70 bg-muted/35 px-4 py-2 text-[10px] font-medium text-muted-foreground">
                        <div className="flex flex-wrap gap-4">
                            <span className="flex items-center gap-1"><kbd className="rounded-full border border-border/60 bg-card px-1.5 py-0.5">↑↓</kbd> Navigate</span>
                            <span className="flex items-center gap-1"><kbd className="rounded-full border border-border/60 bg-card px-1.5 py-0.5">↵</kbd> Open</span>
                            <span className="flex items-center gap-1"><kbd className="rounded-full border border-border/60 bg-card px-1.5 py-0.5">Esc</kbd> Close</span>
                        </div>
                        <span>{canRunRemoteSearch ? "Live + local" : "Local first"}</span>
                    </div>
                </div>
            </CommandDialog>
        </>
    )
})
SearchDialog.displayName = "SearchDialog"
