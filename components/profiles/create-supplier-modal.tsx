"use client"

import { useState } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { motion, AnimatePresence } from "framer-motion"
import { Building2, Loader2, X } from "lucide-react"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/common/dialog"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Textarea } from "@/components/common/textarea"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { useProfileManagement } from "@/hooks/use-profile-management"
import { supplierProfileSchema, type SupplierProfileFormData } from "@/lib/validations/profile-schemas"

interface CreateSupplierModalProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  onSuccess?: () => void
}

const businessTypes = [
  { value: "sole_proprietor", label: "Sole Proprietor" },
  { value: "partnership", label: "Partnership" },
  { value: "corporation", label: "Corporation" },
  { value: "llc", label: "Limited Liability Company" },
  { value: "cooperative", label: "Cooperative" },
]

const countries = [
  { value: "KE", label: "Kenya" },
  { value: "UG", label: "Uganda" },
  { value: "TZ", label: "Tanzania" },
  { value: "RW", label: "Rwanda" },
]

export function CreateSupplierModal({ open, onOpenChange, onSuccess }: CreateSupplierModalProps) {
  const [step, setStep] = useState(1)
  const { createProfile, isSubmitting } = useProfileManagement()

  const {
    register,
    handleSubmit,
    formState: { errors },
    setValue,
    watch,
  } = useForm<SupplierProfileFormData>({
    resolver: zodResolver(supplierProfileSchema),
    defaultValues: {
      supplier_categories: [1],
    },
  })

  const onSubmit = async (data: SupplierProfileFormData) => {
    if (data.supplier_categories.length === 0) {
      data.supplier_categories = [1]
    }
    const profile = await createProfile("supplier", data)
    if (profile) {
      onOpenChange(false)
      setStep(1)
      onSuccess?.()
    }
  }

  const nextStep = () => setStep((prev) => Math.min(prev + 1, 3))
  const prevStep = () => setStep((prev) => Math.max(prev - 1, 1))

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
              <Building2 className="h-5 w-5 text-primary" />
            </div>
            <div>
              <DialogTitle>Create Supplier Profile</DialogTitle>
              <DialogDescription>
                Step {step} of 3 - Fill in your supplier information
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <div className="flex gap-2 mb-4">
          {[1, 2, 3].map((s) => (
            <motion.div
              key={s}
              className={`h-1 flex-1 rounded-full ${s <= step ? "bg-primary" : "bg-muted"
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
                  <Label htmlFor="third_party_name">Company Name *</Label>
                  <Input
                    id="third_party_name"
                    {...register("third_party_name")}
                    placeholder="Enter company name"
                  />
                  {errors.third_party_name && (
                    <p className="text-sm text-destructive">{errors.third_party_name.message}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="trading_name">Trading Name *</Label>
                  <Input
                    id="trading_name"
                    {...register("trading_name")}
                    placeholder="Enter trading name"
                  />
                  {errors.trading_name && (
                    <p className="text-sm text-destructive">{errors.trading_name.message}</p>
                  )}
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="registration_number">Registration Number *</Label>
                    <Input
                      id="registration_number"
                      {...register("registration_number")}
                      placeholder="e.g., PVT-123456"
                    />
                    {errors.registration_number && (
                      <p className="text-sm text-destructive">{errors.registration_number.message}</p>
                    )}
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="tax_pin">Tax PIN (Optional)</Label>
                    <Input
                      id="tax_pin"
                      {...register("tax_pin")}
                      placeholder="e.g., A001234567Z"
                    />
                    {errors.tax_pin && (
                      <p className="text-sm text-destructive">{errors.tax_pin.message}</p>
                    )}
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="kra_pin">KRA PIN (Optional)</Label>
                  <Input
                    id="kra_pin"
                    {...register("kra_pin")}
                    placeholder="Enter KRA PIN"
                  />
                  {errors.kra_pin && (
                    <p className="text-sm text-destructive">{errors.kra_pin.message}</p>
                  )}
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
                  <Label htmlFor="physical_address">Physical Address (Optional)</Label>
                  <Textarea
                    id="physical_address"
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
                    <Label htmlFor="country">Country (Optional)</Label>
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

                <div className="space-y-2">
                  <Label htmlFor="website">Website (Optional)</Label>
                  <Input
                    id="website"
                    {...register("website")}
                    placeholder="https://example.com"
                    type="url"
                  />
                  {errors.website && (
                    <p className="text-sm text-destructive">{errors.website.message}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="business_type">Business Type (Optional)</Label>
                  <Select
                    onValueChange={(value) => setValue("business_type", value)}
                    defaultValue={watch("business_type")}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select business type" />
                    </SelectTrigger>
                    <SelectContent>
                      {businessTypes.map((type) => (
                        <SelectItem key={type.value} value={type.value}>
                          {type.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {errors.business_type && (
                    <p className="text-sm text-destructive">{errors.business_type.message}</p>
                  )}
                </div>
              </motion.div>
            )}

            {step === 3 && (
              <motion.div
                key="step3"
                variants={stepVariants}
                initial="initial"
                animate="animate"
                exit="exit"
                className="space-y-4"
              >
                <div className="space-y-2">
                  <Label>Business Categories *</Label>
                  <p className="text-sm text-muted-foreground">
                    Select the categories that best describe your business offerings
                  </p>
                  <div className="border rounded-lg p-4 bg-muted/20">
                    <p className="text-sm text-muted-foreground">
                      Category selection will be available in the next update
                    </p>
                  </div>
                  {errors.supplier_categories && (
                    <p className="text-sm text-destructive">{errors.supplier_categories.message}</p>
                  )}
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

            {step < 3 ? (
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
