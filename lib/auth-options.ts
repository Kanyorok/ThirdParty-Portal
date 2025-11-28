import CredentialsProvider from "next-auth/providers/credentials"
import type { NextAuthOptions, User, Session } from "next-auth"
import type { JWT } from "next-auth/jwt"
import type { BaseUser, ThirdParty, ThirdPartyTypeEntry } from "@/types/next-auth"

type BackendRes = {
  id: number
  thirdPartyUser: {
    firstName: string
    lastName: string
    fullName: string
    email: string
    phone: string | null
  }
  thirdPartyDetails: {
    thirdPartyName: string | null
    tradingName: string | null
    businessType: string | null
    registrationNumber: string | null
    taxPIN: string | null
    vatNumber: string | null
    physicalAddress: string | null
    website: string | null
    countryId: number | null
    countryInfo?: any
  }
  approvalStatus: string
  approvalStatusCode: string
  status: string
  statusCode: string
  isPrequalified: boolean
  thirdPartyTypeCode: string | null
  types?: Array<{
    id: number
    code: string
    typeCategoryId: number | null
    label: string
  }>
  categories?: any[]
  createdOn: string | null
  modifiedOn: string | null
  createdBy: number | null
}

type AuthResponse = {
  thirdParty: BackendRes
  token: string
  token_type?: string
  message?: string
  errors?: Record<string, string[]>
}

const baseUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL || process.env.API_BASE_URL || ""
const NEXTAUTH_SECRET = process.env.NEXTAUTH_SECRET || ""

export const authOptions: NextAuthOptions = {
  providers: [
    CredentialsProvider({
      name: "credentials",
      credentials: {
        email: { label: "Email", type: "email" },
        password: { label: "Password", type: "password" },
        profile_type: { label: "Profile Type", type: "text" },
      },
      async authorize(credentials): Promise<User | null> {
        if (!credentials?.email || !credentials?.password) {
          throw new Error("MISSING_FIELDS: Email and password are required")
        }
        if (!credentials?.profile_type) {
          throw new Error("MISSING_FIELDS: Profile type is required")
        }
        if (!baseUrl) {
          throw new Error("CONFIG: API URL not set")
        }

        const payload = {
          email: credentials.email,
          password: credentials.password,
          profile_type: credentials.profile_type,
        }

        console.log("Auth Request:", {
          url: `${baseUrl}/api/third-party-auth/login`,
          payload: { ...payload, password: "***" },
        })

        let res: Response
        let text = ""
        try {
          res = await fetch(`${baseUrl}/api/third-party-auth/login`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json"
            },
            body: JSON.stringify(payload),
          })
          text = await res.text()

          console.log("📥 Auth Response:", {
            status: res.status,
            statusText: res.statusText,
            headers: Object.fromEntries(res.headers.entries()),
            bodyPreview: text.substring(0, 500),
          })
        } catch (error) {
          console.error("Network Error:", error)
          throw new Error("NETWORK: Unable to reach authentication service")
        }

        let data: Partial<AuthResponse> | null = null
        try {
          data = text ? JSON.parse(text) : null
          console.log("Parsed Data:", JSON.stringify(data, null, 2))
        } catch (parseError) {
          console.error("JSON Parse Error:", parseError, "Raw text:", text)
          throw new Error("SERVER_ERROR: Invalid response from server")
        }

        const message = data?.message

        if (res.status === 401 || res.status === 422) {
          const errorMsg = data?.errors ? JSON.stringify(data.errors) : (message || "Invalid email or password.")
          console.error("Invalid Credentials:", { status: res.status, message, errors: data?.errors })
          throw new Error(`INVALID_CREDENTIALS: ${errorMsg}`)
        }

        if (res.status === 403) {
          console.error(" Account Not Approved:", { message })
          throw new Error(`ACCOUNT_NOT_APPROVED: ${message || "Account pending approval or inactive."}`)
        }

        if (!res.ok || !data?.thirdParty || !data?.token) {
          console.error(" Auth Failed:", {
            status: res.status,
            hasThirdParty: !!data?.thirdParty,
            hasToken: !!data?.token,
            message,
            data
          })
          throw new Error(`SERVER_ERROR: ${message || `Login failed (${res.status}).`}`)
        }

        const rawUser = data.thirdParty
        const userInfo = rawUser.thirdPartyUser
        const thirdPartyDetails = rawUser.thirdPartyDetails

        const types: ThirdPartyTypeEntry[] = Array.isArray(rawUser.types)
          ? rawUser.types.map((t) => ({
            id: t.id,
            code: t.code,
            categoryId: t.typeCategoryId ?? null
          }))
          : []

        const isSupplier = types.some((t) => t.code?.toUpperCase().startsWith("SU-"))
        const isTenant = types.some((t) => t.code?.toUpperCase().startsWith("TE-"))
        const isCustomer = types.some((t) => t.code?.toUpperCase().startsWith("CU-"))

        const loggedIn: User = {
          id: String(rawUser.id),
          userId: String(rawUser.id),
          firstName: userInfo.firstName,
          lastName: userInfo.lastName,
          fullName: userInfo.fullName,
          email: userInfo.email,
          phone: userInfo.phone ?? null,
          imageId: null,
          gender: null,
          thirdPartyId: rawUser.id,
          isActive: rawUser.status === "Active",
          isApproved: rawUser.approvalStatus === "Approved",
          isSupplier,
          isTenant,
          isCustomer,
          types,
          emailVerifiedOn: null,
          createdOn: rawUser.createdOn || new Date().toISOString(),
          modifiedOn: rawUser.modifiedOn || new Date().toISOString(),
          thirdParty: {
            id: rawUser.id,
            thirdPartyName: thirdPartyDetails.thirdPartyName,
            tradingName: thirdPartyDetails.tradingName,
            label: thirdPartyDetails.tradingName || thirdPartyDetails.thirdPartyName || userInfo.fullName,
            businessType: thirdPartyDetails.businessType || "",
            registrationNumber: thirdPartyDetails.registrationNumber,
            taxPin: thirdPartyDetails.taxPIN,
            vatNumber: thirdPartyDetails.vatNumber,
            kraNo: null,
            idNumber: null,
            passportNo: null,
            country: null,
            physicalAddress: thirdPartyDetails.physicalAddress,
            email: userInfo.email,
            phone: userInfo.phone,
            website: thirdPartyDetails.website,
            approvalStatus: rawUser.approvalStatus,
            status: rawUser.status,
            thirdPartyType: rawUser.thirdPartyTypeCode,
            isPrequalified: rawUser.isPrequalified,
            createdOn: rawUser.createdOn || "",
            modifiedOn: rawUser.modifiedOn || "",
            createdBy: rawUser.createdBy,
            deletedOn: null,
          } as ThirdParty,
          accessToken: data.token,
        } as User

        console.log("Auth Success:", {
          userId: loggedIn.userId,
          email: loggedIn.email,
          fullName: loggedIn.fullName,
          isSupplier: loggedIn.isSupplier,
          isTenant: loggedIn.isTenant,
          isCustomer: loggedIn.isCustomer,
          types: loggedIn.types
        })

        return loggedIn
      },
    }),
  ],
  session: { strategy: "jwt", maxAge: 7 * 24 * 60 * 60 },
  jwt: { secret: NEXTAUTH_SECRET },
  callbacks: {
    async jwt({ token, user }): Promise<JWT> {
      if (user) return { ...token, ...user } as JWT
      return token as JWT
    },
    async session({ session, token }): Promise<Session> {
      const t = token as unknown as BaseUser & { accessToken?: string }
      session.user = {
        id: t.id,
        userId: t.userId,
        firstName: t.firstName,
        lastName: t.lastName,
        fullName: t.fullName,
        email: t.email ?? "",
        phone: t.phone ?? null,
        imageId: t.imageId ?? null,
        gender: t.gender ?? null,
        thirdPartyId: t.thirdPartyId,
        isActive: t.isActive,
        isApproved: t.isApproved,
        isSupplier: t.isSupplier,
        isTenant: t.isTenant ?? false,
        isCustomer: t.isCustomer ?? false,
        types: t.types ?? [],
        emailVerifiedOn: t.emailVerifiedOn ?? null,
        createdOn: t.createdOn,
        modifiedOn: t.modifiedOn,
        thirdParty: t.thirdParty ?? null,
        isDeleted: t.isDeleted ?? false,
      }

      session.accessToken = t.accessToken
      return session
    },
  },
  pages: { signIn: "/signin", error: "/signin" },
  secret: NEXTAUTH_SECRET,
  debug: true,
}