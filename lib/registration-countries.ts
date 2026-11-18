import type { CountryItem } from "./register-shared"

type RegistrationCountry = CountryItem & { iso2: string | null; flag: string | null }

// Keep country metadata consistent across the main and legacy API routes.
// Normalize rows separately so one incomplete country cannot discard the list.
export function normalizeRegistrationCountries(payload: unknown): RegistrationCountry[] {
    const envelope = payload && typeof payload === "object" ? payload as Record<string, unknown> : {}
    const rows = Array.isArray(payload) ? payload : envelope.data ?? envelope.Data
    if (!Array.isArray(rows)) return []

    return rows.flatMap((entry): RegistrationCountry[] => {
        if (!entry || typeof entry !== "object") return []
        const row = entry as Record<string, unknown>
        const id = Number(row.id ?? row.Id ?? row.ID ?? 0)
        const name = String(row.name ?? row.Name ?? "").trim()
        const code = String(row.code ?? row.Code ?? row.CountryCode ?? row.country_code ?? row.iso2 ?? row.Iso2 ?? "").trim().toUpperCase()
        if (!Number.isInteger(id) || id <= 0 || !name || !code) return []
        const rawPhone = row.phoneCode ?? row.PhoneCode ?? row.phone_code ?? row.dialCode ?? row.dial_code
        const phoneCode = rawPhone == null ? null : String(rawPhone).trim() || null
        return [{ id, name, code, phoneCode,
            iso2: String(row.iso2 ?? row.Iso2 ?? "").trim() || null,
            flag: String(row.flag ?? row.Flag ?? "").trim() || null }]
    }).sort((left, right) => left.name.localeCompare(right.name, undefined, { sensitivity: "base" }))
}