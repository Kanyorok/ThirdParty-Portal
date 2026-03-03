"use client"

import { memo, useCallback, useEffect, useMemo, useState, useTransition } from "react"
import { useRouter } from "next/navigation"
import { AnimatePresence, motion } from "framer-motion"
import { Clock, Eraser, Search, Sparkles, File, AlertCircle } from "lucide-react"

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
}
type GroupedItems = { group: string; items: NavItem[] }

const SEARCH_SHORTCUT = { key: "j" } as const
const SEARCH_CONFIG = {
    placeholder: "Search pages, tickets, tenders, or actions...",
    emptyMessage: "No results found.",
    debounceMs: 150,
    recentLimit: 6,
    queryHistoryLimit: 5,
} as const

const RECENT_ITEMS_KEY = "search:recent-items"
const RECENT_QUERIES_KEY = "search:recent-queries"

function SearchSkeleton() {
    return (
        <div className="space-y-4 p-2">
            <div className="space-y-2">
                <div className="mx-2 mb-3 h-3 w-24 rounded bg-muted/60" />
                {[...Array(3)].map((_, i) => (
                    <div key={i} className="flex items-center gap-3 rounded-lg border border-border/50 bg-card p-2.5">
                        <div className="size-8 shrink-0 animate-pulse rounded-md bg-muted/60" />
                        <div className="flex flex-col gap-2 flex-1 min-w-0">
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
    if (label.startsWith(query)) score += 30
    if (label.includes(query)) score += 20
    if (hay.includes(query)) score += 10
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
        const raw = localStorage.getItem(key)
        if (raw) setList(JSON.parse(raw))
    }, [key])
    const add = useCallback((value: string) => {
        setList(prev => {
            const next = [value, ...prev.filter((v) => v !== value)].slice(0, limit)
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
            <mark className="bg-primary/20 text-primary rounded-sm px-0.5">{match}</mark>
            {text.slice(idx + q.length)}
        </>
    )
}

export const SearchButton = memo(({ onClick, className }: { onClick?: () => void; className?: string }) => (
    <Button
        variant="outline"
        className={cn(
            "h-10 w-full justify-start gap-3 rounded-full border-border/70 bg-card px-3 text-muted-foreground shadow-none transition-colors hover:bg-accent/60 hover:text-foreground sm:w-[22rem]",
            className
        )}
        onClick={onClick}
    >
        <Search className="size-3.5" />
        <span className="text-[13px] font-medium tracking-tight">Search dashboard</span>
        <kbd className="ml-auto hidden items-center gap-1 rounded-full border border-border/60 bg-muted/50 px-2 py-0.5 text-[11px] font-semibold text-muted-foreground sm:inline-flex">
            {typeof navigator !== "undefined" && /mac|iphone|ipad|ipod/i.test(navigator.platform) ? "⌘ J" : "Ctrl J"}
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

    const { list: recentHrefs, add: addRecentItem } = useLocalStorageList(RECENT_ITEMS_KEY, SEARCH_CONFIG.recentLimit)
    const { list: recentQueries, add: addRecentQuery, clear: clearQueries } = useLocalStorageList(RECENT_QUERIES_KEY, SEARCH_CONFIG.queryHistoryLimit)

    const items = useMemo(() => getFlatNavItems() || [], [])

    useEffect(() => {
        const down = (e: KeyboardEvent) => {
            if (e.key === SEARCH_SHORTCUT.key && (e.metaKey || e.ctrlKey)) {
                e.preventDefault()
                setOpen((o) => !o)
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
            return
        }
        let cancelled = false
        setRemoteLoading(true)
        setIsError(false)

        fetch(`/api/search?q=${encodeURIComponent(q)}&limit=8`)
            .then(async (res) => {
                if (!res.ok) throw new Error()
                const data = await res.json()
                if (!cancelled) setRemoteResults(data?.data || [])
            })
            .catch(() => {
                if (!cancelled) setIsError(true)
            })
            .finally(() => {
                if (!cancelled) setRemoteLoading(false)
            })
        return () => { cancelled = true }
    }, [debouncedQuery])

    const filteredGroups = useMemo(() => {
        const q = debouncedQuery.trim()
        if (!q) {
            const initial: GroupedItems[] = []
            const recents = recentHrefs.map(h => items.find(i => i.href === h)).filter(Boolean) as NavItem[]
            if (recents.length) initial.push({ group: "Recent Activity", items: recents })
            return initial
        }
        const scored = items
            .map(item => ({ item, score: scoreItem(q, item) }))
            .filter(res => res.score > 0)
            .sort((a, b) => b.score - a.score)
            .map(res => res.item)

        const grouped = new Map<string, NavItem[]>()
        scored.forEach(item => {
            const g = item.group || "General"
            if (!grouped.has(g)) grouped.set(g, [])
            grouped.get(g)!.push(item)
        })
        return Array.from(grouped.entries()).map(([group, items]) => ({ group, items }))
    }, [debouncedQuery, items, recentHrefs])

    const onSelect = useCallback((href: string) => {
        setOpen(false)
        addRecentItem(href)
        if (rawQuery.trim()) addRecentQuery(rawQuery.trim())
        startTransition(() => router.push(href))
    }, [router, rawQuery, addRecentItem, addRecentQuery])

    return (
        <>
            <SearchButton onClick={() => setOpen(true)} />

            <CommandDialog open={open} onOpenChange={setOpen}>
                <div className="overflow-hidden rounded-2xl border border-border/70 bg-popover">
                    <div className="flex items-center border-b border-border/70 px-4">
                        <Search className="mr-3 size-4 text-muted-foreground/80" />
                        <CommandInput
                            placeholder={SEARCH_CONFIG.placeholder}
                            value={rawQuery}
                            onValueChange={setRawQuery}
                            className="h-12 w-full bg-transparent text-sm font-medium tracking-tight placeholder:text-muted-foreground/70 focus:outline-none"
                        />
                        {(isPending || remoteLoading) && <Spinner className="size-3" />}
                    </div>

                    <CommandList className="max-h-[400px] p-2">
                        <CommandEmpty className="py-8 text-center text-sm text-muted-foreground">
                            {SEARCH_CONFIG.emptyMessage}
                        </CommandEmpty>

                        {isError && (
                            <div className="mx-2 mb-3 flex items-center gap-2 rounded-lg border border-destructive/25 bg-destructive/10 px-3 py-2.5">
                                <AlertCircle className="size-3.5 text-destructive" />
                                <span className="text-xs font-medium text-destructive">
                                    Search is temporarily degraded. Some results may be missing.
                                </span>
                            </div>
                        )}

                        {!rawQuery && recentQueries.length > 0 && (
                            <div className="mb-4 px-2 pt-2">
                                <div className="mb-2 flex items-center justify-between">
                                    <span className="text-[11px] font-semibold text-muted-foreground">Recent searches</span>
                                    <Button variant="ghost" size="sm" onClick={clearQueries} className="h-7 rounded-full px-2.5 text-[11px] font-semibold text-destructive hover:bg-destructive/10">
                                        <Eraser className="mr-1 size-3" /> Clear
                                    </Button>
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    {recentQueries.map((q) => (
                                        <button
                                            key={q}
                                            onClick={() => setRawQuery(q)}
                                            className="flex items-center gap-1.5 rounded-full border border-border/70 bg-card px-2.5 py-1 text-[11px] font-medium text-foreground/90 transition-colors hover:bg-accent/60"
                                        >
                                            <Clock className="size-3 text-muted-foreground" />
                                            {q}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        <AnimatePresence mode="popLayout">
                            {remoteLoading && (
                                <motion.div key="skeleton" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}>
                                    <SearchSkeleton />
                                </motion.div>
                            )}

                            {!remoteLoading && remoteResults && remoteResults.length > 0 && (
                                <motion.div key="remote" initial={{ opacity: 0 }} animate={{ opacity: 1 }}>
                                    <CommandGroup heading={<span className="px-2 text-[11px] font-semibold text-muted-foreground">Database</span>}>
                                        {remoteResults.map((r) => (
                                            <CommandItem
                                                key={`${r.type}-${r.id}`}
                                                onSelect={() => r.href && onSelect(r.href)}
                                                className="group flex cursor-pointer items-center gap-3 rounded-lg p-2.5 aria-selected:bg-accent/60"
                                            >
                                                <div className="flex size-8 items-center justify-center rounded-md border border-border/70 bg-card transition-colors">
                                                    <File className="size-3.5 text-muted-foreground group-aria-selected:text-foreground" />
                                                </div>
                                                <div className="flex flex-col min-w-0">
                                                    <span className="text-sm font-semibold tracking-tight text-foreground">
                                                        <Highlight text={r.title} query={rawQuery} />
                                                    </span>
                                                    {r.description && <span className="truncate text-xs text-muted-foreground">{r.description}</span>}
                                                </div>
                                            </CommandItem>
                                        ))}
                                    </CommandGroup>
                                </motion.div>
                            )}

                            {filteredGroups.map(({ group, items }, idx) => (
                                <motion.div
                                    key={group}
                                    initial={{ opacity: 0, y: 5 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ delay: idx * 0.05 }}
                                >
                                    <CommandGroup heading={<span className="px-2 text-[11px] font-semibold text-muted-foreground">{group}</span>} className="mb-2">
                                        {items.map((item) => (
                                            <CommandItem
                                                key={item.href}
                                                onSelect={() => onSelect(item.href)}
                                                className="group flex cursor-pointer items-center gap-3 rounded-lg p-2.5 aria-selected:bg-accent/60"
                                            >
                                                <div className="flex size-8 items-center justify-center rounded-md border border-border/70 bg-card transition-colors">
                                                    {item.icon ? <item.icon className="size-3.5 text-muted-foreground group-aria-selected:text-foreground" /> : <File className="size-3.5 text-muted-foreground" />}
                                                </div>
                                                <div className="flex flex-col min-w-0">
                                                    <span className="text-sm font-semibold tracking-tight text-foreground">
                                                        <Highlight text={item.label} query={rawQuery} />
                                                    </span>
                                                    {item.description && <span className="truncate text-xs text-muted-foreground">{item.description}</span>}
                                                </div>
                                                <kbd className="ml-auto hidden rounded-full border border-border/60 bg-card px-2 py-0.5 text-[10px] font-semibold text-muted-foreground group-aria-selected:block">Enter</kbd>
                                            </CommandItem>
                                        ))}
                                    </CommandGroup>
                                </motion.div>
                            ))}
                        </AnimatePresence>
                    </CommandList>

                    <div className="flex items-center justify-between border-t border-border/70 bg-muted/35 px-4 py-2 text-[10px] font-medium text-muted-foreground">
                        <div className="flex gap-4">
                            <span className="flex items-center gap-1"><kbd className="rounded-full border border-border/60 bg-card px-1.5 py-0.5">↑↓</kbd> Navigate</span>
                            <span className="flex items-center gap-1"><kbd className="rounded-full border border-border/60 bg-card px-1.5 py-0.5">↵</kbd> Open</span>
                        </div>
                        <div className="flex items-center gap-1">
                            <Sparkles className="size-3" /> Smart search
                        </div>
                    </div>
                </div>
            </CommandDialog>
        </>
    )
})
SearchDialog.displayName = "SearchDialog"
