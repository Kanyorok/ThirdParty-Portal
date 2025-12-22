"use client"

import { useState } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { motion, AnimatePresence } from "framer-motion"
import { Home, Loader2 } from "lucide-react"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/common/dialog"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Textarea } from "@/components/common/textarea"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { useProfileManagement } from "@/hooks/use-profile-management"
import { tenantProfileSchema, type TenantProfileFormData } from "@/lib/validations/profile-schemas"

interface CreateTenantModalProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  onSuccess?: () => void
}

const countries = [
  { value: "KE", label: "Kenya" },
  { value: "UG", label: "Uganda" },
  { value: "TZ", label: "Tanzania" },
  { value: "RW", label: "Rwanda" },
]

const propertyTypes = [
  "Residential",
  "Commercial",
  "Industrial",
  "Office Space",
  "Retail Space",
  "Warehouse",
]

const leasePreferences = [
  "Short Term (< 1 year)",
  "Medium Term (1-3 years)",
  "Long Term (> 3 years)",
  "Flexible",
]

export function CreateTenantModal({ open, onOpenChange, onSuccess }: CreateTenantModalProps) {
  const [step, setStep] = useState(1)
  const [selectedPropertyTypes, setSelectedPropertyTypes] = useState<string[]>([])
  const [selectedLeasePreferences, setSelectedLeasePreferences] = useState<string[]>([])
  const { createProfile, isSubmitting } = useProfileManagement()

  const {
    register,
    handleSubmit,
    formState: { errors },
    setValue,
    watch,
  } = useForm<TenantProfileFormData>({
    resolver: zodResolver(tenantProfileSchema),
    defaultValues: {},
  })

  const onSubmit = async (data: TenantProfileFormData) => {
    const profile = await createProfile("tenant", {
      ...data,
      propertyTypes: selectedPropertyTypes,
      leasePreferences: selectedLeasePreferences,
    } as any)
    if (profile) {
      onOpenChange(false)
      setStep(1)
      onSuccess?.()
    }
  }

  const nextStep = () => setStep((prev) => Math.min(prev + 1, 2))
  const prevStep = () => setStep((prev) => Math.max(prev - 1, 1))

  const togglePropertyType = (type: string) => {
    setSelectedPropertyTypes((prev) =>
      prev.includes(type) ? prev.filter((t) => t !== type) : [...prev, type]
    )
  }

  const toggleLeasePreference = (pref: string) => {
    setSelectedLeasePreferences((prev) =>
      prev.includes(pref) ? prev.filter((p) => p !== pref) : [...prev, pref]
    )
  }

  const stepVariants = {
    initial: { opacity: 0, x: 20 },
    animate: { opacity: 1, x: 0 },
    exit: { opacity: 0, x: -20 },
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <div className="flex items-center gap-2">
            <div className="p-2 bg-primary/10 rounded-lg">
              <Home className="h-5 w-5 text-primary" />
            </div>
            <div>
              <DialogTitle>Create Tenant Profile</DialogTitle>
              <DialogDescription>
                Step {step} of 2 - Fill in your tenant information
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <div className="flex gap-2 mb-4">
          {[1, 2].map((s) => (
            <motion.div
              key={s}
              className={`h-1 flex-1 rounded-full ${
                s <= step ? "bg-primary" : "bg-muted"
              }`}
              initial={{ scaleX: 0 }}
              animate={{ scaleX: 1 }}
              transition={{ delay: s * 0.1 }}
            />
          ))}
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <AnimatePresence mode="wait">
            {step === 1 && (
              <motion.div
                key="step1"
                variants={stepVariants}
                initial="initial"
                animate="animate"
                exit="exit"
                className="space-y-4"
              >
                <div className="space-y-2">
                  <Label htmlFor="companyName">Company Name *</Label>
                  <Input
                    id="companyName"
                    {...register("third_party_name")}
                    placeholder="Enter company name"
                  />
                  {errors.third_party_name && (
                    <p className="text-sm text-destructive">{errors.third_party_name.message}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="tradingName">Trading Name *</Label>
                  <Input
                    id="tradingName"
                    {...register("trading_name")}
                    placeholder="Enter trading name"
                  />
                  {errors.trading_name && (
                    <p className="text-sm text-destructive">{errors.trading_name.message}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="registrationNumber">Registration Number *</Label>
                  <Input
                    id="registrationNumber"
                    {...register("registration_number")}
                    placeholder="e.g., PVT-123456"
                  />
                  {errors.registration_number && (
                    <p className="text-sm text-destructive">{errors.registration_number.message}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="physicalAddress">Physical Address *</Label>
                  <Textarea
                    id="physicalAddress"
                    {...register("physical_address")}
                    placeholder="Enter full physical address"
                    rows={3}
                  />
                  {errors.physical_address && (
                    <p className="text-sm text-destructive">{errors.physical_address.message}</p>
                  )}
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="country">Country *</Label>
                    <Select
                      onValueChange={(value) => setValue("country", value)}
                      defaultValue={watch("country")}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select country" />
                      </SelectTrigger>
                      <SelectContent>
                        {countries.map((country) => (
                          <SelectItem key={country.value} value={country.value}>
                            {country.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {errors.country && (
                      <p className="text-sm text-destructive">{errors.country.message}</p>
                    )}
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="city">City (Optional)</Label>
                    <Input
                      id="city"
                      {...register("city")}
                      placeholder="Enter city"
                    />
                  </div>
                </div>
              </motion.div>
            )}

            {step === 2 && (
              <motion.div
                key="step2"
                variants={stepVariants}
                initial="initial"
                animate="animate"
                exit="exit"
                className="space-y-4"
              >
                <div className="space-y-2">
                  <Label>Property Types * (Select at least one)</Label>
                  <div className="grid grid-cols-2 gap-2">
                    {propertyTypes.map((type) => (
                      <motion.button
                        key={type}
                        type="button"
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.98 }}
                        onClick={() => togglePropertyType(type)}
                        className={`p-3 rounded-lg border text-sm text-left transition-colors ${
                          selectedPropertyTypes.includes(type)
                            ? "bg-primary text-primary-foreground border-primary"
                            : "bg-muted/20 hover:bg-muted/40"
                        }`}
                      >
                        {type}
                      </motion.button>
                    ))}
                  </div>
                </div>

                <div className="space-y-2">
                  <Label>Lease Preferences * (Select at least one)</Label>
                  <div className="grid grid-cols-2 gap-2">
                    {leasePreferences.map((pref) => (
                      <motion.button
                        key={pref}
                        type="button"
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.98 }}
                        onClick={() => toggleLeasePreference(pref)}
                        className={`p-3 rounded-lg border text-sm text-left transition-colors ${
                          selectedLeasePreferences.includes(pref)
                            ? "bg-primary text-primary-foreground border-primary"
                            : "bg-muted/20 hover:bg-muted/40"
                        }`}
                      >
                        {pref}
                      </motion.button>
                    ))}
                  </div>
                </div>
              </motion.div>
            )}
          </AnimatePresence>

          <div className="flex justify-between pt-4 border-t">
            <Button
              type="button"
              variant="outline"
              onClick={prevStep}
              disabled={step === 1 || isSubmitting}
            >
              Previous
            </Button>

            {step < 2 ? (
              <Button type="button" onClick={nextStep}>
                Next
              </Button>
            ) : (
              <Button type="submit" disabled={isSubmitting}>
                {isSubmitting ? (
                  <>
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    Creating...
                  </>
                ) : (
                  "Create Profile"
                )}
              </Button>
            )}
          </div>
        </form>
      </DialogContent>
    </Dialog>
  )
}
