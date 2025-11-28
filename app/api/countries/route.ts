import { NextResponse } from "next/server"

const API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL ?? ""

function toStringSafe(v: unknown) {
    if (v == null) return ""
    if (typeof v === "string") return v
    if (typeof v === "number") return String(v)
    return ""
}

function parseBoolish(v: unknown) {
    if (typeof v === "boolean") return v
    if (typeof v === "number") return v !== 0
    if (typeof v === "string") {
        const t = v.trim().toLowerCase()
        return t === "true" || t === "1"
    }
    return false
}

export async function GET() {
    try {
        if (!API_BASE) return NextResponse.json({ data: [] })
        const res = await fetch(`${API_BASE}/api/v1/countries`, {
            headers: { Accept: "application/json" },
            next: { revalidate: 60 },
        })
        const ct = res.headers.get("content-type") ?? ""
        const body = ct.includes("application/json") ? await res.json() : await res.text()
        if (!res.ok) {
            const payload = typeof body === "string" ? { message: body } : body
            return NextResponse.json(payload, { status: res.status })
        }
        const rows = Array.isArray(body.data) ? body.data : body
        const normalized = rows.map((r: any) => {
            const currency = r.currency
                ? { id: toStringSafe(r.currency.id), name: toStringSafe(r.currency.name), code: toStringSafe(r.currency.code), symbol: toStringSafe(r.currency.symbol) }
                : null
            return {
                id: toStringSafe(r.id),
                name: toStringSafe(r.name),
                code: toStringSafe(r.code),
                iso3: toStringSafe(r.iso3),
                phoneCode: toStringSafe(r.phoneCode),
                flag: toStringSafe(r.flag),
                isActive: parseBoolish(r.isActive),
                sortOrder: Number(r.sortOrder ?? 0),
                currency,
            }
        }).sort((a: { name: string }, b: { name: string }) => a.name.localeCompare(b.name))
        return NextResponse.json({ data: normalized })
    } catch {
        return NextResponse.json({ message: "Error while fetching countries." }, { status: 500 })
    }
}
