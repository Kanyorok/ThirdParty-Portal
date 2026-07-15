import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

import { UseFormSetError, FieldValues, Path } from "react-hook-form"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export const getInitials = (firstName?: string, lastName?: string) => {
  return `${firstName?.[0] || ''}${lastName?.[0] || ''}`.toUpperCase();
}

export function handleApiErrors<T extends FieldValues>(
  errors: Record<string, string[]>,
  setError: UseFormSetError<T>
) {
  const fieldMapping: Record<string, Path<T>> = {
    FirstName: "firstName" as Path<T>,
    LastName: "lastName" as Path<T>,
    Email: "email" as Path<T>,
    Phone: "phone" as Path<T>,
    Password: "password" as Path<T>,
    ThirdPartyName: "thirdPartyName" as Path<T>,
    TaxPIN: "taxPIN" as Path<T>,
    RegistrationNumber: "registrationNumber" as Path<T>,
    BusinessType: "businessType" as Path<T>,
    CountryId: "countryId" as Path<T>,
  }

  Object.keys(errors).forEach((backendKey) => {
    const frontendKey = fieldMapping[backendKey]
    if (frontendKey) {
      setError(frontendKey, {
        type: "server",
        message: errors[backendKey][0],
      })
    }
  })
}
