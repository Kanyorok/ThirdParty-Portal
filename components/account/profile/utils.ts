export function normalizeString(value: string | null | undefined) {
  const trimmed = (value ?? "").trim()
  return trimmed.length ? trimmed : null
}

export function resolveLogoUrl(profile: any, thirdParty: any, thirdPartyDetails: any) {
  return (
    thirdPartyDetails?.logo?.src ??
    thirdParty?.logo?.src ??
    profile?.logo?.src ??
    thirdPartyDetails?.logoUrl ??
    thirdPartyDetails?.logo_url ??
    thirdPartyDetails?.logo ??
    thirdParty?.logoUrl ??
    thirdParty?.logo_url ??
    thirdParty?.logo ??
    profile?.logoUrl ??
    profile?.logo_url ??
    null
  )
}

const BUSINESS_COMPLETION_FIELDS: Array<{ keys: string[]; label: string }> = [
  { keys: ["thirdPartyName", "ThirdPartyName", "third_party_name"], label: "Legal name" },
  { keys: ["tradingName", "TradingName", "trading_name"], label: "Trading name" },
  { keys: ["registrationNumber", "RegistrationNumber", "registration_number"], label: "Registration number" },
  { keys: ["taxPIN", "taxPin", "TaxPIN", "tax_pin"], label: "Tax PIN" },
  { keys: ["website", "Website"], label: "Website" },
  { keys: ["physicalAddress", "PhysicalAddress", "physical_address"], label: "Address" },
]

function hasValue(obj: any, keys: string[]) {
  for (const key of keys) {
    if (normalizeString(obj?.[key])) return true
  }
  return false
}

export function computeMissingBusinessFields(thirdPartyDetails: any) {
  return BUSINESS_COMPLETION_FIELDS.filter(({ keys }) => !hasValue(thirdPartyDetails, keys)).map(({ label }) => label)
}

export function computeBusinessCompletionPercent(thirdPartyDetails: any) {
  const total = BUSINESS_COMPLETION_FIELDS.length
  if (!total) return 0
  const missing = BUSINESS_COMPLETION_FIELDS.reduce((count, field) => {
    return count + (hasValue(thirdPartyDetails, field.keys) ? 0 : 1)
  }, 0)
  const filled = Math.max(0, total - missing)
  return Math.round((filled / total) * 100)
}
