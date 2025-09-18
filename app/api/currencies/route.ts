import { NextResponse } from 'next/server';

const API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL

type CurrencyRow = { id?: unknown; Id?: unknown; name?: unknown; Name?: unknown; code?: unknown; Code?: unknown; symbol?: unknown; Symbol?: unknown; isDefault?: unknown }

function toStringSafe(value: unknown): string {
    if (value == null) return "";
    if (typeof value === "string") return value;
    if (typeof value === "number") return String(value);
    return "";
}

function normalizeCurrencies(input: unknown): Array<{ id: string; name: string; code: string; symbol: string; isDefault: boolean }> {
    const rows: CurrencyRow[] = Array.isArray((input as any)?.data) ? (input as any).data : Array.isArray(input) ? (input as any) : []
    const list = rows.map((row: CurrencyRow) => {
        const id = toStringSafe(row.id ?? (row as any)?.Id)
        const name = toStringSafe(row.name ?? (row as any)?.Name)
        const code = toStringSafe(row.code ?? (row as any)?.Code)
        const symbol = toStringSafe(row.symbol ?? (row as any)?.Symbol)
        const isDefault = Boolean(row.isDefault) || symbol.toLowerCase() === 'ksh'
        return { id, name, code, symbol, isDefault }
    })
    // Order with Ksh first, then by name
    return list.sort((a, b) => (Number(b.isDefault) - Number(a.isDefault)) || a.symbol.localeCompare(b.symbol))
}

export async function GET() {
    try {
        const response = await fetch(`${API_BASE}/api/v1/currencies`, {
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            next: { revalidate: 60 }
        });

        const contentType = response.headers.get('content-type') || ''
        const body = contentType.includes('application/json') ? await response.json() : await response.text()

        if (!response.ok) {
            return new NextResponse(typeof body === 'string' ? body : JSON.stringify(body), {
                status: response.status,
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
            });
        }

        const normalized = normalizeCurrencies(body)
        return NextResponse.json({ data: normalized })
    } catch (error) {
        console.error('Error fetching currencies from Laravel:', error);
        return new NextResponse(JSON.stringify({ message: 'Internal server error while fetching currencies.' }), {
            status: 500,
            headers: {
                'Content-Type': 'application/json',
            },
        });
    }
}