"use client"

import { useState, useCallback } from "react"
import { ThemeToggle } from "@/app/dashboard/theme-toggle"
import UserTypeStep from "@/components/signin/usertype-step"
import RegistrationFormStep from "@/components/signin/register-form"
import { UserTypeValue } from "@/types/types"
import { RegisterFormInputs } from "@/lib/validation"

export default function UserRegister() {
  const [userType, setUserType] = useState<UserTypeValue | null>(null)

  const handleBack = useCallback(() => {
    setUserType(null)
  }, [])

  const handleSubmit = useCallback((data: RegisterFormInputs) => {
    console.log("Form Submitted", data)
  }, [])

  return (
    <div className="w-full max-w-xl">
      {!userType ? (
        <UserTypeStep onSelect={setUserType} />
      ) : (
        <RegistrationFormStep userType={userType} onBack={handleBack} onSubmit={handleSubmit} />
      )}
    </div>
  )
}
