import { describe, expect, it } from "vitest"

import {
    buildInternationalPhoneNumber,
    extractNationalPhoneNumber,
    normalizeCountryDialCode,
} from "./register-shared"

describe("registration phone helpers", () => {
    it("normalizes a country phone code", () => {
        expect(normalizeCountryDialCode("254")).toBe("+254")
        expect(normalizeCountryDialCode("+254")).toBe("+254")
    })

    it("builds an international number and removes the local leading zero", () => {
        expect(buildInternationalPhoneNumber("+254", "0712 345 678")).toBe("+254712345678")
    })

    it("extracts national digits from the selected country prefix", () => {
        expect(extractNationalPhoneNumber("+254712345678", "+254")).toBe("712345678")
    })

    it("does not carry a foreign international prefix into another country", () => {
        expect(extractNationalPhoneNumber("+1633411575", "+254")).toBe("")
    })
})
