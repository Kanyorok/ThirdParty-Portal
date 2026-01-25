const BASE_URL = process.env.NEXT_PUBLIC_API_URL

interface ApiResponse<T = any> {
  success: boolean
  message: string
  data?: T
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

interface CompleteProfileRequest {
  ThirdPartyName: string
  TradingName: string
  RegistrationNumber: string
  TaxPIN: string
  BusinessType: number
  CountryId: number
  PhysicalAddress: string
  Website?: string | null
  accountType: 'supplier' | 'tenant' | 'customer'
  supplierCategories?: number[]
  tenantCategories?: number[]
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
  hasProfile: boolean
  emailVerified: boolean
  emailVerifiedOn?: string | null
  createdOn?: string | null
  modifiedOn?: string | null
  isSupplier: boolean
  isTenant: boolean
  isCustomer: boolean
  approvalStatus: string
  thirdParty?: unknown
  supplier?: unknown
  tenant?: unknown
  customer?: unknown
}

const request = async <T = any>(
  endpoint: string,
  options: RequestInit = {},
  token?: string
): Promise<T> => {
  const url = `${BASE_URL}${endpoint}`

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...(options.headers as Record<string, string>),
  }

  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }

  const response = await fetch(url, {
    ...options,
    headers,
  })

  const text = await response.text()
  const data = text ? JSON.parse(text) : {}

  if (!response.ok) {
    throw new Error(data.message || `Request failed with status ${response.status}`)
  }

  return data
}

export const authService = {
  login: async (credentials: LoginRequest): Promise<{ user: UserData; token: string }> => {
    const response = await request<ApiResponse<UserData>>('/api/v1/portal/auth/login', {
      method: 'POST',
      body: JSON.stringify(credentials),
    })

    return {
      user: response.user!,
      token: response.token || (response.data as any)?.token || '',
    }
  },

  register: async (data: RegisterRequest): Promise<{ user: UserData; token: string }> => {
    const response = await request<ApiResponse<UserData>>('/api/v1/portal/auth/register', {
      method: 'POST',
      body: JSON.stringify(data),
    })

    return {
      user: response.user!,
      token: response.token || (response.data as any)?.token || '',
    }
  },

  completeProfile: async (
    data: CompleteProfileRequest,
    token: string
  ): Promise<{ user: UserData }> => {
    const response = await request<ApiResponse<UserData>>(
      '/api/v1/portal/auth/complete-profile',
      {
        method: 'POST',
        body: JSON.stringify(data),
      },
      token
    )

    return {
      user: response.data || response.user!,
    }
  },

  me: async (token: string): Promise<UserData> => {
    const response = await request<ApiResponse<UserData>>(
      '/api/v1/portal/auth/me',
      {
        method: 'POST',
      },
      token
    )

    return response.user || response.data!
  },

  logout: async (token: string): Promise<void> => {
    await request(
      '/api/v1/portal/auth/logout',
      {
        method: 'POST',
      },
      token
    )
  },

  resendVerificationEmail: async (token: string): Promise<{ message: string }> => {
    return await request(
      '/api/v1/portal/auth/email/verification-notification',
      {
        method: 'POST',
      },
      token
    )
  },

  forgotPassword: async (email: string): Promise<{ message: string }> => {
    return await request('/api/v1/portal/auth/password/forgot', {
      method: 'POST',
      body: JSON.stringify({ email }),
    })
  },

  resetPassword: async (data: {
    email: string
    password: string
    password_confirmation: string
    token: string
  }): Promise<{ message: string }> => {
    return await request('/api/v1/portal/auth/password/reset', {
      method: 'POST',
      body: JSON.stringify(data),
    })
  },

  validateToken: async (token: string): Promise<{ valid: boolean; user: UserData }> => {
    return await request(
      '/api/v1/portal/auth/validate-token',
      {
        method: 'GET',
      },
      token
    )
  },
}

export default authService
