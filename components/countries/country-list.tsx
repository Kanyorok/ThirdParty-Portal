"use client"

import { useEffect, useMemo, useState } from "react"
import { motion, AnimatePresence } from "framer-motion"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/common/card"
import { Input } from "@/components/common/input"
import { Button } from "@/components/common/button"
import { Skeleton } from "@/components/common/skeleton"
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/common/select"

type Country = {
    id: string
    name: string
    code: string
    iso3: string
    phoneCode: string
    flag: string
    isActive: boolean
    sortOrder: number
    currency: { id: string; name: string; code: string; symbol: string } | null
}

export default function CountryList({ endpoint = "/api/countries" }) {
    const [data, setData] = useState<Country[]>([])
    const [loading, setLoading] = useState(true)
    const [selected, setSelected] = useState<string | null>(null)
    const [q, setQ] = useState("")
    const [error, setError] = useState<string | null>(null)

    useEffect(() => {
        let alive = true
        fetch(endpoint, { cache: "no-store" })
            .then(async (r) => {
                const j = await r.json()
                if (!alive) return
                if (!r.ok) {
                    setError(j?.message ?? "Failed to load")
                    return
                }
                setData(Array.isArray(j.data) ? j.data : [])
                setSelected(j.data?.[0]?.id ?? null)
            })
            .catch((e) => {
                if (!alive) return
                setError(String(e))
            })
            .finally(() => alive && setLoading(false))
        return () => {
            alive = false
        }
    }, [endpoint])

    const filtered = useMemo(() => {
        const s = q.trim().toLowerCase()
        if (!s) return data
        return data.filter((c) =>
            (c.name + " " + c.code + " " + c.iso3 + " " + c.phoneCode)
                .toLowerCase()
                .includes(s)
        )
    }, [q, data])

    return (
        <Card className="max-w-2xl mx-auto w-full">
            <CardHeader>
                <CardTitle className="flex items-center justify-between gap-4">
                    <span>Select Country</span>
                    <Select value={selected ?? undefined} onValueChange={setSelected}>
                        <SelectTrigger className="w-48">
                            <SelectValue placeholder="Select Country" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                {data.map((c) => (
                                    <SelectItem key={c.id} value={c.id}>
                                        {c.flag} {c.name}
                                    </SelectItem>
                                ))}
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <Input
                        placeholder="Search by name, ISO or phone code"
                        value={q}
                        onChange={(e) => setQ(e.target.value)}
                    />
                    <Button variant="outline" onClick={() => setQ("")}>
                        Clear
                    </Button>
                </div>

                {loading ? (
                    <div className="space-y-2">
                        <Skeleton className="h-10 w-full" />
                        <Skeleton className="h-10 w-full" />
                        <Skeleton className="h-10 w-3/4" />
                    </div>
                ) : error ? (
                    <div className="text-destructive text-sm">{error}</div>
                ) : (
                    <motion.ul layout className="space-y-2">
                        <AnimatePresence>
                            {filtered.length === 0 && (
                                <motion.li
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                    exit={{ opacity: 0 }}
                                    className="text-muted-foreground text-sm"
                                >
                                    No countries found.
                                </motion.li>
                            )}

                            {filtered.map((c) => (
                                <motion.li
                                    key={c.id}
                                    layout
                                    initial={{ opacity: 0, y: -8 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    exit={{ opacity: 0, y: 8 }}
                                >
                                    <button
                                        onClick={() => setSelected(c.id)}
                                        className={`w-full p-4 rounded-xl flex items-center justify-between transition-all ${selected === c.id
                                            ? "shadow-lg ring-2 ring-primary ring-offset-2"
                                            : "hover:shadow"
                                            }`}
                                    >
                                        <div className="flex items-center gap-4 text-left">
                                            <div className="text-3xl">{c.flag}</div>
                                            <div>
                                                <div className="font-semibold">{c.name}</div>
                                                <div className="text-xs text-muted-foreground">
                                                    {c.code} • {c.iso3} • +{c.phoneCode}
                                                </div>
                                            </div>
                                        </div>

                                        <div className="text-xs text-muted-foreground">
                                            {c.currency ? `${c.currency.symbol} (${c.currency.code})` : ""}
                                        </div>
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
