import { NextResponse } from "next/server";
import { z } from "zod";

const API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL ?? "";

const RawCurrency = z.object({
    id: z.any().optional(),
    Id: z.any().optional(),
    name: z.any().optional(),
    Name: z.any().optional(),
    code: z.any().optional(),
    Code: z.any().optional(),
    symbol: z.any().optional(),
    Symbol: z.any().optional(),
    isDefault: z.any().optional(),
});

const RawResponse = z.union([
    z.object({ data: z.array(RawCurrency) }),
    z.array(RawCurrency),
]);

function toStringSafe(value: unknown) {
    if (value == null) return "";
    if (typeof value === "string") return value;
    if (typeof value === "number") return String(value);
    return "";
}

function parseBoolish(value: unknown) {
    if (typeof value === "boolean") return value;
    if (typeof value === "number") return value !== 0;
    if (typeof value === "string") {
        const v = value.trim().toLowerCase();
        return v === "true" || v === "1";
    }
    return false;
}

function normalizeCurrencies(input: unknown) {
    const parsed = RawResponse.safeParse(input);
    if (!parsed.success) return [];
    const rows = Array.isArray((parsed.data as any).data)
        ? (parsed.data as any).data
        : (parsed.data as any);
    const list = rows.map((row: any) => {
        const id = toStringSafe(row.id ?? row.Id);
        const name = toStringSafe(row.name ?? row.Name);
        const code = toStringSafe(row.code ?? row.Code);
        const symbol = toStringSafe(row.symbol ?? row.Symbol);
        const isDefault = parseBoolish(row.isDefault) || ["ksh", "kes"].includes(symbol.trim().toLowerCase()) || ["kes"].includes(code.trim().toLowerCase());
        return { id, name, code, symbol, isDefault };
    });
    return list.sort((a: { isDefault: any; name: string; }, b: { isDefault: any; name: any; }) => (Number(b.isDefault) - Number(a.isDefault)) || a.name.localeCompare(b.name, undefined, { sensitivity: "base" }));
}

export async function GET() {
    try {
        if (!API_BASE) {
            return NextResponse.json({ data: [] }, { status: 200 });
        }
        const res = await fetch(`${API_BASE}/api/v1/currencies`, {
            headers: { "Accept": "application/json" },
            next: { revalidate: 60 },
        });
        const ct = res.headers.get("content-type") ?? "";
        const body = ct.includes("application/json") ? await res.json() : await res.text();
        if (!res.ok) {
            const payload = typeof body === "string" ? { message: body } : body;
            return NextResponse.json(payload, { status: res.status });
        }
        const normalized = normalizeCurrencies(body);
        return NextResponse.json({ data: normalized }, { status: 200 });
    } catch (err) {
        return NextResponse.json({ message: "Internal server error while fetching currencies." }, { status: 500 });
    }
}
