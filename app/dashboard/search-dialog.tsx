"use client"

import { memo, useCallback, useEffect, useMemo, useRef, useState, useTransition } from "react"
import { useRouter } from "next/navigation"
import { AnimatePresence, motion } from "framer-motion"
import { Clock, Command as CmdIcon, Eraser, Search, Sparkles } from "lucide-react"

import {
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
} from "@/components/common/command"
import { Button } from "@/components/common/button"
import { Separator } from "@/components/common/separator"
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
type GroupedItems = { group: string; items: NavItem[] }

const SEARCH_SHORTCUT = { key: "j", display: "⌘J" } as const
const SEARCH_CONFIG = {
    placeholder: "Search pages, actions, and more…",
    emptyMessage: "No results. Try different keywords.",
    debounceMs: 120,
    recentLimit: 8,
    queryHistoryLimit: 6,
} as const

const RECENT_ITEMS_KEY = "search:recent-items"
const RECENT_QUERIES_KEY = "search:recent-queries"

function scoreItem(q: string, item: NavItem): number {
    if (!q) return 0
    const query = q.toLowerCase()
    const label = item.label.toLowerCase()
    const hay = [item.label, item.description || "", ...(item.keywords || [])].join(" ").toLowerCase()
    let score = 0
    if (label.startsWith(query)) score += 30
    if (label.includes(query)) score += 20
    if (hay.includes(query)) score += 10
    score += Math.max(0, 8 - Math.abs(label.length - query.length))
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
    const read = useCallback((): string[] => {
        try {
            const raw = localStorage.getItem(key)
            return raw ? (JSON.parse(raw) as string[]) : []
        } catch {
            return []
        }
    }, [key])

    const write = useCallback(
        (items: string[]) => {
            try {
                localStorage.setItem(key, JSON.stringify(items.slice(0, limit)))
            } catch { }
        },
        [key, limit]
    )

    const add = useCallback(
        (value: string) => {
            const cur = read()
            const next = [value, ...cur.filter((v) => v !== value)].slice(0, limit)
            write(next)
        },
        [limit, read, write]
    )

    const clear = useCallback(() => {
        try {
            localStorage.removeItem(key)
        } catch { }
    }, [key])

    return { read, write, add, clear }
}

function Highlight({ text, query }: { text: string; query: string }) {
    if (!query) return <>{text}</>
    const q = query.toLowerCase()
    const idx = text.toLowerCase().indexOf(q)
    if (idx === -1) return <>{text}</>
    const before = text.slice(0, idx)
    const match = text.slice(idx, idx + q.length)
    const after = text.slice(idx + q.length)
    return (
        <>
            <span className="text-foreground/80">{before}</span>
            <mark className="rounded bg-primary/10 px-0.5 text-primary">{match}</mark>
            <span className="text-foreground/80">{after}</span>
        </>
    )
}

export function SearchButton({
    onClick,
    className,
}: {
    onClick?: () => void
    className?: string
}) {
    return (
        <Button
            type="button"
            variant="outline"
            className={cn(
                "h-8 w-full gap-2 border-dashed bg-transparent text-muted-foreground hover:text-foreground sm:w-[220px]",
                className
            )}
            onClick={onClick}
            aria-label="Open search dialog"
        >
            <Search className="size-4" aria-hidden />
            <span className="hidden sm:inline">Search</span>
            <kbd className="ml-auto hidden items-center gap-1 rounded bg-muted px-1.5 text-[10px] font-medium sm:inline-flex">
                <span aria-hidden>⌘</span>
                <span>J</span>
            </kbd>
        </Button>
    )
}

export const SearchDialog = memo(function SearchDialog() {
    const router = useRouter()
    const [open, setOpen] = useState(false)
    const [rawQuery, setRawQuery] = useState("")
    const debouncedQuery = useDebounced(rawQuery, SEARCH_CONFIG.debounceMs)
    const [isPending, startTransition] = useTransition()

    const recentItemsStore = useLocalStorageList(RECENT_ITEMS_KEY, SEARCH_CONFIG.recentLimit)
    const recentQueriesStore = useLocalStorageList(
        RECENT_QUERIES_KEY,
        SEARCH_CONFIG.queryHistoryLimit
    )

    const items = useMemo<NavItem[]>(() => {
        try {
            return getFlatNavItems() || []
        } catch (err) {
            console.error("Failed to load navigation items:", err)
            return []
        }
    }, [])

    useEffect(() => {
        const onKey = (e: KeyboardEvent) => {
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === SEARCH_SHORTCUT.key) {
                e.preventDefault()
                setOpen((o) => !o)
            }
            if (e.key === "Escape") setOpen(false)
        }
        document.addEventListener("keydown", onKey)
        return () => document.removeEventListener("keydown", onKey)
    }, [])

    useEffect(() => {
        if (!open) setRawQuery("")
    }, [open])

    const groups = useMemo(() => {
        const grouped = new Map<string, NavItem[]>()
        for (const item of items) {
            const key = item.group || "General"
            if (!grouped.has(key)) grouped.set(key, [])
            grouped.get(key)!.push(item)
        }
        for (const [key, arr] of grouped) {
            grouped.set(
                key,
                [...arr].sort((a, b) => a.label.localeCompare(b.label, undefined, { sensitivity: "base" }))
            )
        }
        return grouped
    }, [items])

    const recentHrefs = useMemo(() => recentItemsStore.read(), [recentItemsStore])
    const recentItems = useMemo(
        () => recentHrefs.map((href) => items.find((i) => i.href === href)).filter(Boolean) as NavItem[],
        [items, recentHrefs]
    )

    const filteredGroups: GroupedItems[] = useMemo(() => {
        const q = debouncedQuery.trim()
        if (!q) {
            const groupsArr: GroupedItems[] = []
            if (recentItems.length) groupsArr.push({ group: "Recent", items: recentItems })
            for (const [group, gi] of groups) groupsArr.push({ group, items: gi })
            return groupsArr
        }
        const scored = items
            .map((item) => ({ item, score: scoreItem(q, item) }))
            .filter(({ score }) => score > 0)
            .sort((a, b) => b.score - a.score)
            .map((r) => r.item)

        const byGroup = new Map<string, NavItem[]>()
        for (const item of scored) {
            const key = item.group || "General"
            if (!byGroup.has(key)) byGroup.set(key, [])
            byGroup.get(key)!.push(item)
        }
        return Array.from(byGroup.entries()).map(([group, gi]) => ({ group, items: gi }))
    }, [debouncedQuery, groups, items, recentItems])

    const navigate = useCallback(
        (href: string, q: string) => {
            setOpen(false)
            recentItemsStore.add(href)
            if (q.trim()) recentQueriesStore.add(q.trim())
            startTransition(() => router.push(href))
        },
        [recentItemsStore, recentQueriesStore, router]
    )

    const openDialog = useCallback(() => setOpen(true), [])

    return (
        <>
            <SearchButton className="w-full sm:w-[240px]" onClick={openDialog} />

            <CommandDialog open={open} onOpenChange={setOpen} className="sm:rounded-xl">
                <div className="w-[92vw] max-w-2xl p-0">
                    <div className="flex items-center gap-2 px-3 pt-3">
                        <CmdIcon className="size-4 text-muted-foreground" aria-hidden />
                        <CommandInput
                            placeholder={SEARCH_CONFIG.placeholder}
                            value={rawQuery}
                            onValueChange={setRawQuery}
                            className="h-10 border-0 bg-transparent text-sm focus:ring-0"
                        />
                    </div>
                    <Separator className="my-2" />

                    {!debouncedQuery && (
                        <QueryHistory
                            getHistory={recentQueriesStore.read}
                            onClear={recentQueriesStore.clear}
                            onApply={(val) => setRawQuery(val)}
                        />
                    )}

                    {/* Results */}
                    <CommandList className="max-h-[60vh] overflow-auto">
                        <CommandEmpty>{SEARCH_CONFIG.emptyMessage}</CommandEmpty>

                        <AnimatePresence initial={false} mode="popLayout">
                            {filteredGroups.map(({ group, items }, gi) => (
                                <motion.div
                                    key={group}
                                    initial={{ opacity: 0, y: 6 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    exit={{ opacity: 0, y: -6 }}
                                    transition={{ duration: 0.15 }}
                                >
                                    {gi > 0 && <CommandSeparator />}
                                    <CommandGroup heading={group}>
                                        {items.map((item) => {
                                            const Icon = item.icon
                                            return (
                                                <CommandItem
                                                    key={item.href}
                                                    value={`${item.label} ${item.description || ""} ${item.keywords?.join(" ") || ""}`}
                                                    onSelect={() => navigate(item.href, debouncedQuery)}
                                                    className="cursor-pointer px-3 py-2 aria-selected:bg-accent aria-selected:text-accent-foreground"
                                                    disabled={isPending}
                                                >
                                                    <div className="flex w-full min-w-0 items-center gap-2">
                                                        {Icon ? (
                                                            <Icon className="size-4 flex-shrink-0 text-muted-foreground" aria-hidden />
                                                        ) : (
                                                            <Sparkles className="size-4 flex-shrink-0 text-muted-foreground" aria-hidden />
                                                        )}
                                                        <div className="min-w-0 flex-1">
                                                            <div className="truncate text-sm font-medium">
                                                                <Highlight text={item.label} query={debouncedQuery} />
                                                            </div>
                                                            {item.description ? (
                                                                <div className="truncate text-xs text-muted-foreground">
                                                                    <Highlight text={item.description} query={debouncedQuery} />
                                                                </div>
                                                            ) : null}
                                                        </div>
                                                        <kbd className="ml-2 hidden items-center gap-1 rounded bg-muted px-1.5 text-[10px] font-medium sm:inline-flex">
                                                            ↵
                                                        </kbd>
                                                    </div>
                                                </CommandItem>
                                            )
                                        })}
                                    </CommandGroup>
                                </motion.div>
                            ))}
                        </AnimatePresence>
                    </CommandList>
                </div>
            </CommandDialog>
        </>
    )
})

function QueryHistory({
    getHistory,
    onClear,
    onApply,
}: {
    getHistory: () => string[]
    onClear: () => void
    onApply: (q: string) => void
}) {
    const history = getHistory()
    if (!history.length) return null
    return (
        <div className="flex flex-wrap items-center gap-2 px-3 pb-2">
            <span className="text-xs text-muted-foreground">Recent searches:</span>
            <div className="flex flex-wrap items-center gap-2">
                {history.map((q) => (
                    <button
                        key={q}
                        type="button"
                        onClick={() => onApply(q)}
                        className="rounded-full border bg-muted/60 px-2.5 py-1 text-xs text-foreground/90 transition-colors hover:bg-muted"
                    >
                        <Clock className="mr-1 inline size-3.5 text-muted-foreground" />
                        {q}
                    </button>
                ))}
                <button
                    type="button"
                    onClick={onClear}
                    className="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs text-muted-foreground transition-colors hover:bg-muted"
                    aria-label="Clear recent searches"
                >
                    <Eraser className="size-3.5" />
                    Clear
                </button>
            </div>
        </div>
    )
}
