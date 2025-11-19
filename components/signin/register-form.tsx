"use client"

import { useMemo } from "react"
import { AlertCircle, CheckCircle, Eye, EyeOff, Loader2, ArrowLeft, ArrowRight } from "lucide-react"
import { useRegisterForm } from "@/hooks/use-register"
import { UserTypeValue } from "@/types/types"
import { RegisterFormInputs } from "@/lib/validation"
import Link from "next/link"
import { AuthHeader } from "@/components/layout/auth-header"

interface Props {
  userType: UserTypeValue
  onBack: () => void
  onSubmit: (data: RegisterFormInputs) => Promise<void> | void
  onLoginRedirect?: () => void
}

const InputField = ({ id, label, register, error, type = "text", placeholder, handlers = {}, isPassword = false, toggle, isShown, match }: any) => {
  const base = "w-full p-3 rounded-xl border transition duration-150 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2"
  const errorStyle = "border-red-600 bg-red-50 focus:border-red-600 focus:ring-red-300"
  const defaultStyle = "border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 dark:text-gray-100 dark:border-zinc-700 dark:placeholder-gray-500 dark:focus:ring-indigo-400"
  const inputClasses = error ? `${base} ${errorStyle}` : `${base} ${defaultStyle}`

  return (
    <div>
      <label htmlFor={id} className="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{label} <span className="text-red-500">*</span></label>
      <div className="relative">
        <input
          id={id}
          {...register(id)}
          type={isPassword ? (isShown ? "text" : "password") : type}
          className={inputClasses}
          placeholder={placeholder}
          inputMode={type === 'tel' ? 'numeric' : undefined}
          {...handlers}
        />
        {isPassword && (
          <button type="button" onClick={toggle} className="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white transition">
            {isShown ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
          </button>
        )}
        {id === "confirmPassword" && isPassword && register("confirmPassword").name && (
          <div className="absolute right-12 top-1/2 -translate-y-1/2 pr-1 pointer-events-none">
            {match ? <CheckCircle className="w-5 h-5 text-green-500" /> : <AlertCircle className="w-5 h-5 text-gray-300" />}
          </div>
        )}
      </div>
      {error && <p className="mt-1 text-xs text-red-600 flex items-center gap-1 font-medium"><AlertCircle className="w-4 h-4" />{error.message}</p>}
    </div>
  )
}

export default function RegistrationFormStep({ userType, onBack, onSubmit, onLoginRedirect }: Props) {
  const { form, errors, pwdShown, confirmShown, togglePwd, toggleConfirm, isSubmitting, isValid, phoneHandlers } = useRegisterForm(userType)
  const { register, handleSubmit, watch, formState } = form
  const watchedPassword = watch("password")
  const watchedConfirm = watch("confirmPassword")
  const match = useMemo(() => watchedConfirm === watchedPassword && watchedConfirm !== "", [watchedPassword, watchedConfirm])
  const label = userType.value === "supplier" ? "Supplier" : "Tenant"
  const passwordError = formState.touchedFields.confirmPassword && !match && watchedConfirm !== "" ? { message: "Passwords do not match" } : undefined

  const submitHandler = async (data: RegisterFormInputs) => {
    if (!match) return
    await onSubmit(data)
  }

  return (
    <div className="w-full max-w-4xl mx-auto p-4">
      <div className="text-center mb-8">
        <AuthHeader />
        <p className="text-gray-500 dark:text-gray-400 mt-2">Create {label} Account in a few steps. All fields are required!</p>
      </div>

      <form onSubmit={handleSubmit(submitHandler)} className="space-y-6  dark:bg-gray-800 p-6 rounded-2xl">
        <div className="grid gap-5 md:grid-cols-2">
          <InputField id="firstName" label="First Name" register={register} error={errors.firstName} placeholder="John" />
          <InputField id="lastName" label="Last Name" register={register} error={errors.lastName} placeholder="Doe" />
          <InputField id="email" label="Email" register={register} error={errors.email} type="email" placeholder="john.doe@example.com" />
          <InputField id="phone" label="Phone" register={register} error={errors.phone} type="tel" placeholder="e.g., 254712345678" handlers={phoneHandlers} />
          <InputField id="password" label="Password" register={register} error={errors.password} placeholder="Enter password" isPassword isShown={pwdShown} toggle={togglePwd} />
          <InputField id="confirmPassword" label="Confirm Password" register={register} error={errors.confirmPassword || passwordError} placeholder="Confirm password" isPassword isShown={confirmShown} toggle={toggleConfirm} match={match} />
        </div>

        {errors.root?.message && (
          <div role="alert" className="p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-200 flex gap-3 items-start shadow-sm">
            <AlertCircle className="w-5 h-5 mt-0.5" /> <span className="font-semibold">Submission Error:</span> {errors.root.message}
          </div>
        )}

        <div className="flex gap-4 pt-4">
          <button type="button" onClick={onBack} className="flex-1 flex items-center justify-center p-3 border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-200 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-800 transition">
            <ArrowLeft className="w-5 h-5 mr-2" /> Back
          </button>

          <button
            type="submit"
            disabled={!isValid || !match || isSubmitting}
            className="flex-1 p-3 bg-indigo-600 text-white rounded-xl font-semibold shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/50 disabled:bg-gray-400 disabled:shadow-none transition flex items-center justify-center gap-2"
          >
            {isSubmitting ? (
              <><Loader2 className="w-5 h-5 animate-spin" /> Creating Account…</>
            ) : (
              <>Complete {label} Registration <ArrowRight className="w-4 h-4 ml-1" /></>
            )}
          </button>
        </div>
      </form>

      <div className="mt-8 text-center text-sm text-gray-600 dark:text-gray-400">
        Already have an account?{" "}
        <Link href="/signin" onClick={onLoginRedirect} className="text-indigo-600 font-semibold hover:underline">Login</Link>
      </div>
    </div>
  )
}
