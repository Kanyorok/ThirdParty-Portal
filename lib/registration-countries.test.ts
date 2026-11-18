import { beforeEach, describe, expect, it, vi } from "vitest"
import { normalizeRegistrationCountries } from "./registration-countries"
import { normalizeCountryDialCode, buildInternationalPhoneNumber } from "./register-shared"
import { fetchFirstAvailableJson } from "./portal-auth-api"
import { fetchUpstreamJson } from "@/app/api/_shared/upstream-json"
import { GET as metadataGET } from "@/app/api/portal/auth/metadata/countries/route"
import { GET as fallbackGET } from "@/app/api/countries/route"

vi.mock("./portal-auth-api", () => ({ fetchFirstAvailableJson: vi.fn() }))
vi.mock("@/app/api/_shared/upstream-json", () => ({
    fetchUpstreamJson: vi.fn(), toServerErrorResponse: vi.fn(), toUpstreamErrorResponse: vi.fn(),
}))

const payload = { data: [
    { id: "1", name: "Kenya", code: "KE", phoneCode: "254", flag: null },
    { Id: 2, Name: "Uganda", CountryCode: "UG", PhoneCode: 256 },
    { id: 3, name: "Unknown calling code", code: "ZZ", phoneCode: null },
] }

describe("registration country metadata", () => {
    beforeEach(() => vi.clearAllMocks())

    it("preserves prefix aliases, numeric values and nullable metadata", () => {
        const countries = normalizeRegistrationCountries(payload)
        expect(countries).toHaveLength(3)
        expect(countries.find(country => country.code === "KE")?.phoneCode).toBe("254")
        expect(countries.find(country => country.code === "UG")?.phoneCode).toBe("256")
        expect(countries.find(country => country.code === "ZZ")?.phoneCode).toBeNull()
    })

    it("does not discard every country because one row is incomplete", () => {
        expect(normalizeRegistrationCountries({ data: [...payload.data, null, { id: "bad" }] })).toHaveLength(3)
    })

    it("supports older database aliases without fabricating country codes", () => {
        expect(normalizeRegistrationCountries({ Data: [{ ID: 4, Name: "Tanzania", CountryCode: "tz", phone_code: "255" }] })[0]).toMatchObject({ code: "TZ", phoneCode: "255" })
        expect(normalizeRegistrationCountries([{ id: 5, name: "No country code" }])).toEqual([])
    })

    it("main endpoint retains phone prefix for country selection", async () => {
        vi.mocked(fetchFirstAvailableJson).mockResolvedValue({ apiBase: "http://backend", ok: true, status: 200, body: payload })
        const response = await metadataGET()
        const result = await response.json()
        expect(result.data.find((country: { code: string }) => country.code === "KE").phoneCode).toBe("254")
    })

    it("fallback endpoint retains phone prefix for country selection", async () => {
        vi.mocked(fetchUpstreamJson).mockResolvedValue({ missingBase: false, ok: true, status: 200, body: payload })
        const response = await fallbackGET()
        const result = await response.json()
        const selected = result.data.find((country: { code: string }) => country.code === "UG")
        const prefix = normalizeCountryDialCode(selected.phoneCode)
        expect(prefix).toBe("+256")
        expect(buildInternationalPhoneNumber(prefix, "0700123456")).toBe("+256700123456")
    })
})