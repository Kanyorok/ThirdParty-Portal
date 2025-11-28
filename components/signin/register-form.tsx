"use client"

import { useState, useCallback, Suspense } from "react"
import { useRouter } from "next/navigation"
import Link from "next/link"
import { useForm, Controller } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import { motion, AnimatePresence } from "framer-motion"
import {
  Eye,
  EyeOff,
  Loader2,
  Mail,
  Lock,
  User,
  Phone,
  Clock,
  AlertCircle,
  X,
  Check,
} from "lucide-react"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Field, FieldLabel, FieldError, FieldGroup } from "@/components/common/field"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { cn } from "@/lib/utils"
import { AuthHeader } from "../layout/auth-header"
import { Spinner } from "../common/spinner"

const USER_TYPES = [
  { value: "Customer", id: 1, label: "Customer" },
  { value: "Tenant", id: 2, label: "Tenant" },
  { value: "Supplier", id: 3, label: "Supplier" },
] as const

type UserType = (typeof USER_TYPES)[number]["value"]

const registerSchema = z
  .object({
    profileType: z.enum(USER_TYPES.map(t => t.value) as [string, ...string[]]),
    firstName: z.string().min(2),
    lastName: z.string().min(2),
    email: z.string().email(),
    phone: z.string().regex(/^\+?254\d{9}$/),
    password: z
      .string()
      .min(8)
      .regex(/[A-Z]/)
      .regex(/[a-z]/)
      .regex(/[0-9]/),
    confirmPassword: z.string(),
  })
  .refine(data => data.password === data.confirmPassword, {
    message: "Passwords do not match",
    path: ["confirmPassword"],
  })

type RegisterFormInputs = z.infer<typeof registerSchema>

const passwordRules = [
  { regex: /.{8,}/, label: "8+ chars" },
  { regex: /[A-Z]/, label: "Uppercase" },
  { regex: /[a-z]/, label: "Lowercase" },
  { regex: /[0-9]/, label: "Number" },
]

function PasswordStrength({ password }: { password: string }) {
  const score = passwordRules.filter(r => r.regex.test(password)).length

  return (
    <div className="max-w-md space-y-3">
      <div className="flex gap-1">
        {[1, 2, 3, 4].map(l => (
          <div
            key={l}
            className={cn(
              "h-1 flex-1 transition-colors",
              l <= score
                ? score <= 2
                  ? "bg-red-500"
                  : score === 3
                    ? "bg-yellow-500"
                    : "bg-blue-600"
                : "bg-gray-300"
            )}
          />
        ))}
      </div>

      <div className="flex flex-wrap gap-3">
        {passwordRules.map(rule => {
          const ok = rule.regex.test(password)
          return (
            <div
              key={rule.label}
              className={cn("flex items-center gap-1.5 text-xs transition-colors", ok ? "text-blue-600" : "text-gray-500")}
            >
              {ok ? <Check className="h-3 w-3" /> : <div className="h-3 w-3 border border-current" />}
              {rule.label}
            </div>
          )
        })}
      </div>
    </div>
  )
}

function SuccessState() {
  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.9 }}
      animate={{ opacity: 1, scale: 1 }}
      className="flex flex-col items-center justify-center py-16"
    >
      <div className="mb-6 flex h-20 w-20 items-center justify-center bg-blue-600/10">
        <Clock className="h-10 w-10 text-blue-600" />
      </div>

      <h2 className="text-2xl font-semibold mb-2">Account Pending Approval</h2>
      <p className="text-gray-500 max-w-sm text-center mb-6">
        Your account has been created. Admin approval is required before you can sign in.
      </p>

      <div className="flex items-center gap-2 text-sm text-gray-600">
        <Spinner className="h-4 w-4 animate-spin" />
        Redirecting…
      </div>
    </motion.div>
  )
}

export default function SignUpPage() {
  const router = useRouter()
  const [step, setStep] = useState<"form" | "success">("form")
  const [showPassword, setShowPassword] = useState(false)
  const [showConfirmPassword, setShowConfirmPassword] = useState(false)
  const [passwordValue, setPasswordValue] = useState("")
  const [confirmPasswordValue, setConfirmPasswordValue] = useState("")

  const {
    register,
    handleSubmit,
    control,
    setError,
    clearErrors,
    formState: { errors, isSubmitting },
    reset,
    getValues,
  } = useForm<RegisterFormInputs>({
    resolver: zodResolver(registerSchema),
    mode: "onSubmit",
    defaultValues: { profileType: undefined as unknown as UserType },
  })

  const selectedTypeId = USER_TYPES.find(t => t.value === getValues("profileType"))?.id ?? null

  const onInputChange = useCallback(() => {
    if (errors.root) clearErrors("root")
  }, [errors.root, clearErrors])

  const onSubmit = async (data: RegisterFormInputs) => {
    clearErrors("root")
    try {
      const payload = {
        ThirdPartyType: selectedTypeId,
        FirstName: data.firstName,
        LastName: data.lastName,
        Email: data.email,
        Phone: data.phone.startsWith("+") ? data.phone : `+${data.phone}`,
        Password: data.password,
        Password_confirmation: data.confirmPassword,
      }

      const res = await fetch(`${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/third-party-auth/register`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      })

      const result = await res.json()

      if (!res.ok) {
        if (result.errors) {
          Object.entries(result.errors).forEach(([key, val]) => {
            setError(key.toLowerCase() as keyof RegisterFormInputs, { message: (val as string[])[0] })
          })
        } else {
          setError("root", { message: result.message || "Registration failed." })
        }
        return
      }

      reset()
      setPasswordValue("")
      setConfirmPasswordValue("")

      setStep("success")
      setTimeout(() => router.push("/signin?registrationSuccess=true"), 3500)
    } catch {
      setError("root", { message: "Network error. Try again." })
    }
  }

  return (
    <Suspense
      fallback={
        <div className="flex min-h-screen items-center justify-center">
          <Spinner className="h-8 w-8 animate-spin text-blue-600" />
        </div>
      }
    >
      <div className="flex w-full min-h-screen">
        <div className="hidden lg:flex w-2/5 bg-blue-700 text-white flex-col justify-between p-12">
          <h1 className="text-2xl font-semibold">Craft Silicon</h1>

          <div>
            <h2 className="text-4xl font-bold leading-tight">Join our self-service platform</h2>
            <p className="mt-4 text-white/90 text-lg">Create an account today</p>
          </div>

          <p className="text-white/60 text-sm">Quick setup · Powerful tools · Smooth experience</p>
        </div>

        <div className="flex w-full lg:w-3/5 items-center justify-center px-8 lg:px-16 py-12">
          <AnimatePresence mode="wait">
            {step === "form" && (
              <motion.div
                key="registerForm"
                initial={{ opacity: 0, y: 15 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -15 }}
                className="w-full max-w-2xl"
              >
                <div className="mb-8 lg:hidden">
                  <AuthHeader />
                </div>

                <h2 className="text-3xl font-semibold">Create an account</h2>
                <p className="mt-2 text-gray-500">Fill in your details to get started</p>

                <AnimatePresence>
                  {errors.root && (
                    <motion.div
                      initial={{ opacity: 0, y: -10 }}
                      animate={{ opacity: 1, y: 0 }}
                      exit={{ opacity: 0, y: -10 }}
                      className="mt-6 mb-6 flex items-center gap-3 bg-red-500/10 p-4 rounded-none"
                    >
                      <AlertCircle className="h-5 w-5 text-red-500" />
                      <span className="text-sm flex-1 text-gray-800">{errors.root.message}</span>
                      <button onClick={() => clearErrors("root")}>
                        <X className="h-4 w-4 text-gray-600" />
                      </button>
                    </motion.div>
                  )}
                </AnimatePresence>

                <form onSubmit={handleSubmit(onSubmit)} className="space-y-8 mt-8">
                  <FieldGroup className="gap-6">
                    <Field>
                      <FieldLabel className="text-sm font-medium">Register Profile</FieldLabel>
                      <Controller
                        name="profileType"
                        control={control}
                        render={({ field }) => (
                          <Select
                            value={field.value}
                            onValueChange={v => {
                              field.onChange(v)
                              onInputChange()
                            }}
                          >
                            <SelectTrigger
                              className={cn(
                                "h-12 border-b border-x-0 border-t-0 bg-gray-100 px-4 shadow-none rounded-none focus:ring-0 focus:border-blue-600",
                                errors.profileType && "border-red-500"
                              )}
                            >
                              <SelectValue placeholder="Select profile" />
                            </SelectTrigger>
                            <SelectContent className="rounded-none border shadow-none">
                              {USER_TYPES.map(t => (
                                <SelectItem key={t.value} value={t.value} className="rounded-none">
                                  {t.label}
                                </SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        )}
                      />
                      {errors.profileType && <FieldError>Select your account type</FieldError>}
                    </Field>

                    <div className="grid sm:grid-cols-2 gap-6">
                      <Field>
                        <FieldLabel>First Name</FieldLabel>
                        <div className="relative">
                          <User className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-500" />
                          <Input
                            placeholder="John"
                            className={cn(
                              "h-12 pl-12 rounded-none border-b border-x-0 border-t-0 bg-gray-100 focus-visible:ring-0 focus-visible:border-blue-600",
                              errors.firstName && "border-red-500"
                            )}
                            {...register("firstName", { onChange: onInputChange })}
                          />
                        </div>
                        {errors.firstName && <FieldError>{errors.firstName.message}</FieldError>}
                      </Field>

                      <Field>
                        <FieldLabel>Last Name</FieldLabel>
                        <div className="relative">
                          <User className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-500" />
                          <Input
                            placeholder="Doe"
                            className={cn(
                              "h-12 pl-12 rounded-none border-b border-x-0 border-t-0 bg-gray-100 focus-visible:ring-0 focus-visible:border-blue-600",
                              errors.lastName && "border-red-500"
                            )}
                            {...register("lastName", { onChange: onInputChange })}
                          />
                        </div>
                        {errors.lastName && <FieldError>{errors.lastName.message}</FieldError>}
                      </Field>
                    </div>

                    <div className="grid sm:grid-cols-2 gap-6">
                      <Field>
                        <FieldLabel>Email Address</FieldLabel>
                        <div className="relative">
                          <Mail className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-500" />
                          <Input
                            type="email"
                            placeholder="you@example.com"
                            className={cn(
                              "h-12 pl-12 rounded-none border-b border-x-0 border-t-0 bg-gray-100 focus-visible:ring-0 focus-visible:border-blue-600",
                              errors.email && "border-red-500"
                            )}
                            {...register("email", { onChange: onInputChange })}
                          />
                        </div>
                        {errors.email && <FieldError>{errors.email.message}</FieldError>}
                      </Field>

                      <Field>
                        <FieldLabel>Phone Number</FieldLabel>
                        <div className="relative">
                          <Phone className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-500" />
                          <Input
                            type="tel"
                            placeholder="+254712345678"
                            className={cn(
                              "h-12 pl-12 rounded-none border-b border-x-0 border-t-0 bg-gray-100 focus-visible:ring-0 focus-visible:border-blue-600",
                              errors.phone && "border-red-500"
                            )}
                            {...register("phone", { onChange: onInputChange })}
                          />
                        </div>
                        {errors.phone && <FieldError>{errors.phone.message}</FieldError>}
                      </Field>
                    </div>

                    <div className="grid sm:grid-cols-2 gap-6">
                      <Field>
                        <FieldLabel>Password</FieldLabel>
                        <div className="relative">
                          <Lock className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-500" />
                          <Input
                            type={showPassword ? "text" : "password"}
                            placeholder="Create password"
                            className={cn(
                              "h-12 pl-12 pr-10 rounded-none border-b border-x-0 border-t-0 bg-gray-100 focus-visible:ring-0 focus-visible:border-blue-600",
                              errors.password && "border-red-500"
                            )}
                            {...register("password", {
                              onChange: e => {
                                setPasswordValue(e.target.value)
                                onInputChange()
                              },
                            })}
                          />
                          <button
                            type="button"
                            onClick={() => setShowPassword(s => !s)}
                            className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500"
                          >
                            {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                          </button>
                        </div>

                        {passwordValue.length > 0 && <PasswordStrength password={passwordValue} />}
                        {errors.password && <FieldError>{errors.password.message}</FieldError>}
                      </Field>

                      <Field>
                        <FieldLabel>Confirm Password</FieldLabel>
                        <div className="relative">
                          <Lock className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-500" />
                          <Input
                            type={showConfirmPassword ? "text" : "password"}
                            placeholder="Confirm password"
                            className={cn(
                              "h-12 pl-12 pr-10 rounded-none border-b border-x-0 border-t-0 bg-gray-100 focus-visible:ring-0 focus-visible:border-blue-600",
                              errors.confirmPassword && "border-red-500"
                            )}
                            {...register("confirmPassword", {
                              onChange: e => {
                                setConfirmPasswordValue(e.target.value)
                                onInputChange()
                              },
                            })}
                          />

                          <button
                            type="button"
                            onClick={() => setShowConfirmPassword(s => !s)}
                            className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500"
                          >
                            {showConfirmPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                          </button>
                        </div>

                        {errors.confirmPassword && <FieldError>{errors.confirmPassword.message}</FieldError>}

                        {passwordValue &&
                          confirmPasswordValue &&
                          passwordValue === confirmPasswordValue &&
                          !errors.confirmPassword && (
                            <div className="flex items-center gap-2 text-xs text-blue-600 mt-1">
                              <Check className="h-4 w-4" />
                              Passwords match
                            </div>
                          )}
                      </Field>
                    </div>
                  </FieldGroup>

                  <Button
                    type="submit"
                    disabled={isSubmitting}
                    className="w-full h-12 rounded-none bg-blue-700 text-white hover:bg-blue-800"
                  >
                    {isSubmitting ? (
                      <span className="flex items-center gap-2">
                        <Loader2 className="h-5 w-5 animate-spin" />
                        Creating account…
                      </span>
                    ) : (
                      "Create Account"
                    )}
                  </Button>

                  <p className="text-sm text-gray-600 text-center mt-4">
                    Already have an account?{" "}
                    <Link href="/signin" className="text-blue-700 hover:underline">
                      Sign in
                    </Link>
                  </p>
                </form>
              </motion.div>
            )}

            {step === "success" && <SuccessState />}
          </AnimatePresence>
        </div>
      </div>
    </Suspense>
  )
}
