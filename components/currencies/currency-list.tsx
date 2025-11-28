"use client"

import { useEffect, useMemo, useState } from "react"
import { motion, AnimatePresence } from "framer-motion"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/common/card"
import { Input } from "@/components/common/input"
import { Button } from "@/components/common/button"
import { Skeleton } from "@/components/common/skeleton"
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"

type Currency = { id: string; name: string; code: string; symbol: string; isDefault: boolean }

export default function CurrencyList({ initialFetch = "/api/currencies" }: { initialFetch?: string }) {
    const [data, setData] = useState<Currency[]>([])
    const [q, setQ] = useState("")
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState<string | null>(null)
    const [selected, setSelected] = useState<string | null>(null)

    useEffect(() => {
        let mounted = true
        setLoading(true)
        fetch(initialFetch, { cache: "no-store" })
            .then(async (r) => {
                const j = await r.json()
                if (!mounted) return
                if (!r.ok) {
                    setError(j?.message ?? "Failed to load")
                    setData([])
                } else {
                    setData(Array.isArray(j?.data) ? j.data : [])
                    const def = (Array.isArray(j?.data) ? j.data.find((c: any) => c.isDefault) : null) as Currency | undefined
                    setSelected(def?.id ?? (Array.isArray(j?.data) && j.data[0]?.id) ?? null)
                }
            })
            .catch((e) => {
                if (!mounted) return
                setError(String(e))
                setData([])
            })
            .finally(() => {
                if (mounted) setLoading(false)
            })
        return () => { mounted = false }
    }, [initialFetch])

    const filtered = useMemo(() => {
        const ql = q.trim().toLowerCase()
        if (!ql) return data
        return data.filter((c) => (c.name + " " + c.code + " " + c.symbol).toLowerCase().includes(ql))
    }, [data, q])

    return (
        <Card className="max-w-xl w-full mx-auto">
            <CardHeader>
                <CardTitle className="flex items-center justify-between gap-4">
                    <span>Choose currency</span>
                    <div className="flex items-center gap-2">
                        <Select onValueChange={(v) => setSelected(v || null)} value={selected ?? undefined}>
                            <SelectTrigger className="w-44">
                                <SelectValue placeholder="Select" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    {data.map((c) => (
                                        <SelectItem key={c.id} value={c.id}>
                                            {c.symbol || c.code} — {c.name}
                                        </SelectItem>
                                    ))}
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <Button onClick={() => { if (selected) navigator.clipboard?.writeText(selected) }} variant="ghost">Copy ID</Button>
                    </div>
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div className="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <Input placeholder="Search by name, code or symbol" value={q} onChange={(e) => setQ(e.target.value)} />
                    <Button onClick={() => { setQ("") }} variant="outline">Clear</Button>
                </div>

                {loading ? (
                    <div className="space-y-2">
                        <Skeleton className="h-8 w-full" />
                        <Skeleton className="h-8 w-full" />
                        <Skeleton className="h-8 w-3/4" />
                    </div>
                ) : error ? (
                    <div className="text-sm text-destructive">{error}</div>
                ) : (
                    <motion.ul layout className="space-y-2">
                        <AnimatePresence>
                            {filtered.length === 0 && (
                                <motion.li initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} className="text-sm text-muted-foreground">
                                    No currencies found.
                                </motion.li>
                            )}
                            {filtered.map((c) => (
                                <motion.li
                                    key={c.id}
                                    layout
                                    initial={{ opacity: 0, y: -6 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    exit={{ opacity: 0, y: 6 }}
                                >
                                    <button
                                        onClick={() => setSelected(c.id)}
                                        className={`w-full p-3 rounded-xl flex items-center justify-between transition-shadow focus:outline-none ${selected === c.id ? "shadow-lg ring-2 ring-offset-2 ring-primary" : "hover:shadow-sm"
                                            }`}
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="text-lg font-medium">{c.symbol || c.code}</div>
                                            <div className="text-sm leading-none">
                                                <div className="font-semibold">{c.name || c.code}</div>
                                                <div className="text-muted-foreground text-xs">{c.code}</div>
                                            </div>
                                        </div>
                                        <div className="text-xs">{c.isDefault ? "Default" : ""}</div>
                                    </button>
                                </motion.li>
                            ))}
                        </AnimatePresence>
                    </motion.ul>
                )}
            </CardContent>
        </Card>
    )
}
