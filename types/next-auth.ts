export type RegistrationStep = "form" | "details" | "verify" | "success"

export type RegistrationData = {
  types: string[]
  createUser: boolean
  [key: string]: unknown
}

export type AuthState = {
  step: RegistrationStep
  isSubmitting: boolean
  data: RegistrationData
  setStep: (step: RegistrationStep) => void
  setIsSubmitting: (loading: boolean) => void
  updateData: (newData: Partial<RegistrationData>) => void
  resetRegistration: () => void
}

export type BaseUser = {
  id?: string | number
  user_id?: number
  email?: string | null
  first_name?: string | null
  last_name?: string | null
  full_name?: string | null
  phone?: string | null
  profile?: {
    image_url?: string | null
    [key: string]: unknown
  } | null
  image_url?: string | null
  imageUrl?: string | null
  image?: string | null
  image_id?: number | null
  imageId?: number | null
  [key: string]: unknown
}

export type UserProfile = {
  firstName: string
  lastName: string
  email: string
  phone?: string
  gender?: string
  receiveSmsNotifications?: boolean
  receiveNewsletter?: boolean
  third_party_id?: number | string | null
  third_party_name?: string | null
  status?: string | null
  [key: string]: unknown
}
