import { describe, expect, it } from "vitest"
import { identityIssues, isPersonalBusinessType } from "./party-identity"

describe("party registration identity", () => {
    it("requires an explicit identity kind", () => {
        expect(identityIssues({})[0].field).toBe("IdentityKind")
    })
    it("accepts an individual with passport and issuing country", () => {
        expect(identityIssues({ IdentityKind: "individual", IdentityDocumentType: "passport", IdentityIssuingCountry: "KE", BusinessType: "I" })).toEqual([])
    })
    it("does not accept company registration as a personal document", () => {
        expect(identityIssues({ IdentityKind: "individual", IdentityDocumentType: "registration", IdentityIssuingCountry: "KE", BusinessType: "Corporation" }).map(issue => issue.field)).toEqual(["IdentityDocumentType", "BusinessType"])
    })
    it("accepts an organisation without personal identity fields", () => {
        expect(identityIssues({ IdentityKind: "organisation", IdentityIssuingCountry: "KE", BusinessType: "NGO" })).toEqual([])
    })
    it("keeps sole proprietors under individual identity", () => {
        expect(isPersonalBusinessType("Sole")).toBe(true)
        expect(identityIssues({ IdentityKind: "organisation", IdentityIssuingCountry: "KE", BusinessType: "Sole" }).map(issue => issue.field)).toContain("BusinessType")
    })
})