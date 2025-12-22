import { NextResponse } from "next/server"
import type Country from "@/types/countries"

const mapper = (r: any): Country => ({
    id: String(r.id ?? ''),
    name: String(r.name ?? ''),
    code: String(r.code ?? ''),
    iso3: String(r.iso3 ?? ''),
    phoneCode: String(r.phoneCode ?? ''),
    flag: String(r.flag ?? ''),
    isActive: Boolean(r.isActive),
    sortOrder: Number(r.sortOrder ?? 0),
    currency: r.currency ? {
        id: String(r.currency.id ?? ''),
        name: String(r.currency.name ?? ''),
        code: String(r.currency.code ?? ''),
        symbol: String(r.currency.symbol ?? '')
    } : null
})

export async function GET() {
    if (!process.env.NEXT_PUBLIC_API_URL) {
        return NextResponse.json({ data: [], error: "Config Error" }, { status: 500 })
    }

    try {
        const controller = new AbortController()
        const timeoutId = setTimeout(() => controller.abort(), 5000)

        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/countries`, {
            headers: {
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            next: { revalidate: 3600 },
            signal: controller.signal,
        })

        clearTimeout(timeoutId)

        if (!res.ok) {
            const errorData = await res.json().catch(() => ({ message: "Upstream Error" }))
            return NextResponse.json(errorData, { status: res.status })
        }

        const json = await res.json()
        const rows = Array.isArray(json.data) ? json.data : []

        const data: Country[] = rows
            .map(mapper)
            .sort((a: Country, b: Country) => a.name.localeCompare(b.name))

        return NextResponse.json({ data })

    } catch (error: any) {
        const isTimeout = error.name === 'AbortError'
        return NextResponse.json(
            { message: isTimeout ? "Request timed out" : "Internal Server Error" },
            { status: isTimeout ? 504 : 500 }
        )
    }
}