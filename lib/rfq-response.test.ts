import { describe, expect, it } from "vitest"

import {
    buildSubmitResponseItems,
    calculateRfqTaxBreakdown,
    getRfqTaxTreatment,
} from "./rfq-response"

const lines = [{
    id: "32",
    raw: { id: 32, quantity: 2 },
    unitPrice: "380000",
}]

describe("RFQ tax treatment", () => {
    it("adds VAT to a VAT-exclusive quotation", () => {
        expect(calculateRfqTaxBreakdown(760000, "vat_exclusive")).toEqual({
            enteredTotal: 760000,
            netAmount: 760000,
            taxAmount: 121600,
            grossAmount: 881600,
        })
    })

    it("extracts VAT from a VAT-inclusive quotation", () => {
        const totals = calculateRfqTaxBreakdown(760000, "vat_inclusive")
        expect(totals.grossAmount).toBe(760000)
        expect(totals.netAmount).toBeCloseTo(655172.4138, 4)
        expect(totals.taxAmount).toBeCloseTo(104827.5862, 4)
    })

    it("submits an explicit tax declaration on every line", () => {
        expect(buildSubmitResponseItems(lines, "vat_inclusive")).toEqual([{
            rfqLineId: 32,
            quotedPrice: 380000,
            totalPayable: 760000,
            taxType: "VAT",
            taxRate: 16,
            isTaxInclusive: true,
        }])
        expect(getRfqTaxTreatment({ taxType: "Exempt", taxRate: 0 })).toBe("no_vat")
    })

    it("sends the VAT-inclusive payable amount for exclusive prices", () => {
        expect(buildSubmitResponseItems(lines, "vat_exclusive")[0].totalPayable).toBe(881600)
    })
})
