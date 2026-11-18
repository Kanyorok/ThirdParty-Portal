import { afterEach, beforeEach, describe, expect, it, vi } from "vitest"
import { normalizeVerifyEmailPath } from "./email-verification"
import { GET } from "@/app/api/portal/auth/email/verify/[user]/[hash]/route"
import { resolvePortalAuthApiBaseUrl } from "./portal-auth-api"

vi.mock("./portal-auth-api", () => ({ resolvePortalAuthApiBaseUrl: vi.fn(() => "http://backend:8001") }))
const hash = "a".repeat(40)
const path = `/api/portal/auth/email/verify/17/${hash}?token=example-token`
const context = { params: Promise.resolve({ user: "17", hash }) }

describe("email verification forwarding", () => {
    beforeEach(() => { vi.mocked(resolvePortalAuthApiBaseUrl).mockReturnValue("http://backend:8001") })
    afterEach(() => { vi.unstubAllGlobals(); vi.clearAllMocks() })

    it("keeps generated verification paths on the portal origin", () => {
        expect(normalizeVerifyEmailPath(path)).toBe(path)
    })
    it("routes older absolute backend links through the portal", () => {
        expect(normalizeVerifyEmailPath(`http://old-backend:8000/api/v1/portal/auth/email/verify/17/${hash}?token=example-token`)).toBe(path)
    })
    it("rejects missing tokens and unrelated paths", () => {
        expect(normalizeVerifyEmailPath(`/api/portal/auth/email/verify/17/${hash}`)).toBeNull()
        expect(normalizeVerifyEmailPath("https://example.com/arbitrary?token=example-token")).toBeNull()
        expect(normalizeVerifyEmailPath("javascript:alert(1)")).toBeNull()
    })
    it.each([200, 403, 404, 409, 410])("preserves the backend verification result %s", async status => {
        const body = { success: status === 200, error: status === 200 ? undefined : "INVALID_LINK", message: "Verification result" }
        const fetchMock = vi.fn().mockResolvedValue(Response.json(body, { status }))
        vi.stubGlobal("fetch", fetchMock)
        const response = await GET(new Request(`http://portal${path}`), context)
        expect(response.status).toBe(status)
        expect((await response.json()).success).toBe(status === 200)
        expect(fetchMock).toHaveBeenCalledWith(`http://backend:8001/api/v1/portal/auth/email/verify/17/${hash}?token=example-token`, expect.objectContaining({ cache: "no-store", redirect: "manual" }))
    })
    it("returns unavailable for an unreachable backend without leaking its error", async () => {
        vi.stubGlobal("fetch", vi.fn().mockRejectedValue(new Error("Private backend details")))
        const response = await GET(new Request(`http://portal${path}`), context)
        expect(response.status).toBe(503)
        expect(await response.text()).not.toContain("Private backend details")
    })
    it("does not accept an HTML login page as successful verification", async () => {
        vi.stubGlobal("fetch", vi.fn().mockResolvedValue(new Response("<html>Sign in</html>")))
        expect((await GET(new Request(`http://portal${path}`), context)).status).toBe(503)
    })
    it("rejects malformed requests before contacting the backend", async () => {
        const fetchMock = vi.fn()
        vi.stubGlobal("fetch", fetchMock)
        expect((await GET(new Request(`http://portal${path.split('?')[0]}`), context)).status).toBe(403)
        expect(fetchMock).not.toHaveBeenCalled()
    })
})