/**
 * Authentication API Service
 * Handles all authentication-related API calls
 */

const BASE_URL = process.env.NEXT_PUBLIC_API_URL || "";

interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
  user?: T;
}

interface LoginRequest {
  email: string;
  password: string;
}

interface RegisterRequest {
  FirstName: string;
  LastName: string;
  Email: string;
  Phone: string;
  Password: string;
  Password_confirmation: string;
}

interface CompleteProfileRequest {
  ThirdPartyName: string;
  TradingName: string;
  RegistrationNumber: string;
  TaxPIN: string;
  BusinessType: number;
  CountryId: number;
  PhysicalAddress: string;
  Website?: string | null;
  accountType: 'supplier' | 'tenant' | 'customer' | 'both';
  supplierCategories?: number[];
  tenantCategories?: number[];
}

interface UserData {
  id: number;
  userId: string;
  firstName: string;
  lastName: string;
  fullName: string;
  email: string;
  phone: string | null;
  thirdPartyId: string | null;
  isActive: boolean;
  hasProfile: boolean;
  emailVerified: boolean;
  isSupplier: boolean;
  isTenant: boolean;
  isCustomer: boolean;
  approvalStatus: string;
  thirdParty?: any;
}

const request = async <T = any>(
  endpoint: string,
  options: RequestInit = {},
  token?: string
): Promise<T> => {
  const url = `${BASE_URL}${endpoint}`;
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...options.headers,
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(url, {
    ...options,
    headers,
  });

  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.message || `Request failed with status ${response.status}`);
  }

  return data;
};

export const authService = {
  /**
   * Login user with email and password
   */
  login: async (credentials: LoginRequest): Promise<{ user: UserData; token: string }> => {
    const response = await request<ApiResponse<UserData>>('/api/v1/portal/auth/login', {
      method: 'POST',
      body: JSON.stringify(credentials),
    });

    return {
      user: response.user!,
      token: response.data as any || '',
    };
  },

  /**
   * Register new user account (Step 1)
   */
  register: async (data: RegisterRequest): Promise<{ user: UserData; token: string }> => {
    const response = await request<ApiResponse<UserData>>('/api/v1/portal/auth/register', {
      method: 'POST',
      body: JSON.stringify(data),
    });

    return {
      user: response.user!,
      token: response.data as any || '',
    };
  },

  /**
   * Complete user profile (Step 2)
   */
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
    );

    return {
      user: response.data!,
    };
  },

  /**
   * Get current user data
   */
  me: async (token: string): Promise<UserData> => {
    const response = await request<ApiResponse<UserData>>(
      '/api/v1/portal/auth/me',
      {
        method: 'POST',
      },
      token
    );

    return response.user!;
  },

  /**
   * Logout user
   */
  logout: async (token: string): Promise<void> => {
    await request(
      '/api/v1/portal/auth/logout',
      {
        method: 'POST',
      },
      token
    );
  },

  /**
   * Resend email verification
   */
  resendVerificationEmail: async (token: string): Promise<{ message: string }> => {
    return await request(
      '/api/v1/portal/auth/email/verification-notification',
      {
        method: 'POST',
      },
      token
    );
  },

  /**
   * Request password reset
   */
  forgotPassword: async (email: string): Promise<{ message: string }> => {
    return await request('/api/v1/portal/auth/password/forgot', {
      method: 'POST',
      body: JSON.stringify({ email }),
    });
  },

  /**
   * Reset password with token
   */
  resetPassword: async (data: {
    email: string;
    password: string;
    password_confirmation: string;
    token: string;
  }): Promise<{ message: string }> => {
    return await request('/api/v1/portal/auth/password/reset', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  /**
   * Validate token
   */
  validateToken: async (token: string): Promise<{ valid: boolean; user: UserData }> => {
    return await request(
      '/api/v1/portal/auth/validate-token',
      {
        method: 'GET',
      },
      token
    );
  },
};

export default authService;
