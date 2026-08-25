// Mirrors lib/rfq-response.ts's tax-treatment model (calculateRfqTaxBreakdown / getRfqTaxTreatment)
// so tender bidding uses the same supplier-facing vocabulary as RFQ quoting: pick a treatment
// (VAT exclusive / VAT inclusive / Exempt) rather than a specific finance tax-rule ID. The ERP
// resolves the matching t_FinanceTaxRuleConfiguration row server-side from the declared
// tax_type + tax_rate (see TenderBidTaxService::resolveTaxRule), same as SupplierRFQController does.

export type TenderTaxTreatment = "vat_exclusive" | "vat_inclusive" | "no_vat"

export const TENDER_DEFAULT_VAT_RATE = 16

export type TenderBidLineState = {
  tenderItemId: number
  itemName: string
  uom?: string | null
  quantity: number
  unitPrice: string
  taxTreatment: TenderTaxTreatment
  discountPercentage: string
  withholdingTaxRate: string
}

export type TenderBidLineComputed = {
  discountAmount: number
  netAmount: number
  taxAmount: number
  grossAmount: number
  withholdingTaxAmount: number
}

function toNumber(value: unknown): number {
  const parsed = Number(String(value ?? "").replace(/,/g, ""))
  return Number.isFinite(parsed) ? parsed : 0
}

type AnyRecord = Record<string, any>

/**
 * A global Laravel middleware (TransformApiResponse) recursively camelCases every API JSON
 * response's keys before it reaches the browser — so a PHP resource field like `QtyToTender`
 * or `tender_item_id` actually arrives as `qtyToTender` / `tenderItemId`. Some Next.js API
 * routes additionally re-normalize a handful of top-level fields back to snake_case, but nested
 * arrays (like a tender's `items`) pass through untouched. Read defensively across all the
 * casings we might realistically see instead of assuming one.
 */
function pick(source: unknown, keys: string[]): any {
  if (!source || typeof source !== "object") return undefined
  const record = source as AnyRecord
  for (const key of keys) {
    if (record[key] !== undefined && record[key] !== null) return record[key]
  }
  return undefined
}

export type RawTenderLineItem = AnyRecord

export function parseTenderLineItem(raw: RawTenderLineItem) {
  const itemNode = pick(raw, ["item", "Item"])
  return {
    id: Number(pick(raw, ["id", "Id"])),
    manualItemDescription: pick(raw, ["manualItemDescription", "ManualItemDescription"]) ?? null,
    qtyToTender: toNumber(pick(raw, ["qtyToTender", "QtyToTender"])),
    itemName: pick(itemNode, ["itemName", "ItemName"]) ?? null,
    uom: pick(itemNode, ["uOM", "uom", "UOM"]) ?? null,
  }
}

export type RawBidLineItem = AnyRecord

export function parseBidLineItem(raw: RawBidLineItem) {
  return {
    tenderItemId: Number(pick(raw, ["tenderItemId", "tender_item_id", "TenderItemID"])),
    quotedPrice: pick(raw, ["quotedPrice", "quoted_price"]),
    taxType: pick(raw, ["taxType", "tax_type"]),
    isTaxInclusive: pick(raw, ["isTaxInclusive", "is_tax_inclusive"]),
    discountPercentage: pick(raw, ["discountPercentage", "discount_percentage"]),
    withholdingTaxRate: pick(raw, ["withholdingTaxRate", "withholding_tax_rate"]),
  }
}

/**
 * Client-side preview mirroring App\Services\Procurement\ProcurementTaxService::computeLineTax.
 * The server recomputes and is the source of truth — this is display-only.
 */
export function calculateTenderLineTax(
  unitPrice: number,
  quantity: number,
  taxTreatment: TenderTaxTreatment,
  withholdingTaxRate: number,
  discountPercentage: number,
  vatRate: number = TENDER_DEFAULT_VAT_RATE
): TenderBidLineComputed {
  const isExempt = taxTreatment === "no_vat"
  const isInclusive = taxTreatment === "vat_inclusive"
  const rate = isExempt ? 0 : Math.max(0, vatRate)
  const effectiveWht = isExempt ? 0 : Math.max(0, withholdingTaxRate)

  const lineTotal = Math.max(0, unitPrice) * Math.max(0, quantity)
  const discountAmount = lineTotal * (Math.max(0, Math.min(100, discountPercentage)) / 100)
  const discountedTotal = Math.max(0, lineTotal - discountAmount)

  let netAmount = discountedTotal
  let taxAmount = 0
  let grossAmount = discountedTotal

  if (!isExempt && rate > 0) {
    if (isInclusive) {
      grossAmount = discountedTotal
      netAmount = grossAmount / (1 + rate / 100)
      taxAmount = grossAmount - netAmount
    } else {
      netAmount = discountedTotal
      taxAmount = netAmount * (rate / 100)
      grossAmount = netAmount + taxAmount
    }
  }

  const withholdingTaxAmount = isExempt || effectiveWht <= 0 ? 0 : netAmount * (effectiveWht / 100)

  return { discountAmount, netAmount, taxAmount, grossAmount, withholdingTaxAmount }
}

export function sumTenderLineTotals(lines: TenderBidLineComputed[]) {
  return lines.reduce(
    (acc, line) => ({
      discountAmount: acc.discountAmount + line.discountAmount,
      netAmount: acc.netAmount + line.netAmount,
      taxAmount: acc.taxAmount + line.taxAmount,
      grossAmount: acc.grossAmount + line.grossAmount,
      withholdingTaxAmount: acc.withholdingTaxAmount + line.withholdingTaxAmount,
    }),
    { discountAmount: 0, netAmount: 0, taxAmount: 0, grossAmount: 0, withholdingTaxAmount: 0 }
  )
}

export function isTenderLineQuoted(line: TenderBidLineState) {
  return toNumber(line.unitPrice) > 0
}

/**
 * Builds the bracket-notation FormData entries the ERP endpoint expects
 * (multipart/form-data, so items can't be sent as a single JSON blob).
 */
export function appendTenderBidItemsToFormData(
  formData: FormData,
  lines: TenderBidLineState[],
  vatRate: number = TENDER_DEFAULT_VAT_RATE
) {
  let index = 0
  lines.forEach((line) => {
    if (!isTenderLineQuoted(line)) return // skip untouched/blank rows (allowed while drafting)

    const isExempt = line.taxTreatment === "no_vat"
    formData.append(`items[${index}][tender_item_id]`, String(line.tenderItemId))
    formData.append(`items[${index}][quoted_price]`, line.unitPrice)
    formData.append(`items[${index}][tax_type]`, isExempt ? "Exempt" : "VAT")
    formData.append(`items[${index}][tax_rate]`, isExempt ? "0" : String(vatRate))
    formData.append(`items[${index}][is_tax_inclusive]`, line.taxTreatment === "vat_inclusive" ? "1" : "0")
    formData.append(`items[${index}][discount_percentage]`, line.discountPercentage || "0")
    if (!isExempt && line.withholdingTaxRate) {
      formData.append(`items[${index}][withholding_tax_rate]`, line.withholdingTaxRate)
    }
    index += 1
  })
}
