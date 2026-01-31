import { NextResponse } from "next/server"
import { z } from "zod"
import { Currency } from '@/types/currencies';
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

const RawCurrencySchema = z.object({
    id: z.union([z.string(), z.number()]).optional(),
    Id: z.union([z.string(), z.number()]).optional(),
    name: z.string().optional(),
    Name: z.string().optional(),
    code: z.string().optional(),
    Code: z.string().optional(),
    symbol: z.string().optional(),
    Symbol: z.string().optional(),
    isDefault: z.union([z.boolean(), z.number(), z.string()]).optional(),
})

const RawResponseSchema = z.union([
    z.object({ data: z.array(RawCurrencySchema) }),
    z.array(RawCurrencySchema),
])

function normalizeCurrencies(input: unknown): Currency[] {
    const parsed = RawResponseSchema.safeParse(input)
    if (!parsed.success) return []

    const rows = "data" in parsed.data ? parsed.data.data : parsed.data

    return rows.map((row) => {
        const id = String(row.id ?? row.Id ?? "")
        const name = String(row.name ?? row.Name ?? "")
        const code = String(row.code ?? row.Code ?? "").trim()
        const symbol = String(row.symbol ?? row.Symbol ?? "").trim()

        const isDefault =
            Boolean(row.isDefault) ||
            row.isDefault === 1 ||
            row.isDefault === "1" ||
            ["kes", "ksh"].includes(code.toLowerCase()) ||
            ["kes", "ksh"].includes(symbol.toLowerCase())

        return { id, name, code, symbol, isDefault }
    }).sort((a, b) => {
        if (a.isDefault !== b.isDefault) return a.isDefault ? -1 : 1
        return a.name.localeCompare(b.name, undefined, { sensitivity: "base" })
    })
}

export async function GET() {
    try {
        if (!process.env.NEXT_PUBLIC_API_URL) return NextResponse.json({ data: [] })

        const session = await getServerSession(authOptions)
        const hasToken = Boolean(session?.accessToken)

        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/currencies`, {
            headers: {
                Accept: "application/json",
                ...(hasToken ? { Authorization: `Bearer ${session!.accessToken}` } : {}),
            },
            ...(hasToken ? { cache: "no-store" as const } : { next: { revalidate: 3600 } }),
        })

        if (!res.ok) {
            const body = await res.json().catch(() => ({ message: "Upstream Error" }))
            return NextResponse.json(body, { status: res.status })
        }

        const body = await res.json()
        return NextResponse.json({ data: normalizeCurrencies(body) })
    } catch {
        return NextResponse.json({ message: "Internal server error" }, { status: 500 })
    }
}
