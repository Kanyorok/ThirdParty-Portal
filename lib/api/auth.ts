const BASE_URL = process.env.NEXT_PUBLIC_API_URL

if (!BASE_URL) {
  throw new Error("NEXT_PUBLIC_API_URL is not defined")
}

interface ApiResponse<T = any> {
  success: boolean
  message?: string
  error?: string
  user?: T
  token?: string
}

interface LoginRequest {
  email: string
  password: string
}

interface RegisterRequest {
  FirstName: string
  LastName: string
  Email: string
  Phone: string
  Password: string
  Password_confirmation: string
}

interface UserData {
  id: number
  userId: number
  firstName: string
  lastName: string
  fullName: string
  email: string
  phone: string | null
  gender?: unknown
  imageId?: number | null
  thirdPartyId: string | null
  isActive: boolean
  isSupplier: boolean
  isTenant: boolean
  isCustomer: boolean
  approvalStatus?: string
  emailVerifiedOn?: string | null
  createdOn?: string | null
  modifiedOn?: string | null
  thirdParty?: unknown
}

class ApiError extends Error {
  code?: string
  constructor(message: string, code?: string) {
    super(message)
    this.code = code
  }
}

const request = async <T = any>(
  endpoint: string,
  options: RequestInit = {},
  token?: string
): Promise<T> => {
  const response = await fetch(`${BASE_URL}${endpoint}`, {
    ...options,
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(options.headers || {}),
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
  })

  const text = await response.text()
  const data = text ? JSON.parse(text) : {}

  if (!response.ok || data?.success === false) {
    throw new ApiError(data?.message || "Request failed", data?.error)
  }

  return data
}

export const authService = {
  register: async (data: RegisterRequest): Promise<{ userId: string }> => {
    const res = await request<ApiResponse>(
      "/api/v1/portal/auth/register",
      {
        method: "POST",
        body: JSON.stringify(data),
      }
    )

    return { userId: (res as any).userId }
  },

  login: async (
    credentials: LoginRequest
  ): Promise<{ user: UserData; token: string }> => {
    const res = await request<ApiResponse<UserData>>(
      "/api/v1/portal/auth/login",
      {
        method: "POST",
        body: JSON.stringify(credentials),
      }
    )

    return {
      user: res.user!,
      token: res.token!,
    }
  },

  me: async (token: string): Promise<UserData> => {
    const res = await request<ApiResponse<UserData>>(
      "/api/v1/portal/auth/me",
      { method: "GET" },
      token
    )

    return res.user!
  },

  logout: async (token: string): Promise<void> => {
    await request(
      "/api/v1/portal/auth/logout",
      { method: "POST" },
      token
    )
  },

  resendVerificationEmail: async (email: string): Promise<void> => {
    await request(
      "/api/v1/portal/auth/email/resend",
      {
        method: "POST",
        body: JSON.stringify({ email }),
      }
    )
  },

  forgotPassword: async (email: string): Promise<void> => {
    await request(
      "/api/v1/portal/auth/password/forgot",
      {
        method: "POST",
        body: JSON.stringify({ email }),
      }
    )
  },

  resetPassword: async (data: {
    email: string
    password: string
    password_confirmation: string
    token: string
  }): Promise<void> => {
    await request(
      "/api/v1/portal/auth/password/reset",
      {
        method: "POST",
        body: JSON.stringify(data),
      }
    )
  },

  validateToken: async (
    token: string
  ): Promise<{ valid: boolean; user: UserData }> => {
    return await request(
      "/api/v1/portal/auth/validate-token",
      { method: "GET" },
      token
    )
  },
}

export { ApiError }
export default authService
