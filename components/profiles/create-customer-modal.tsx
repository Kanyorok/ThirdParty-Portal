"use client"

import { useForm, useWatch } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { motion } from "framer-motion"
import { User, Loader2 } from "lucide-react"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/common/dialog"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Textarea } from "@/components/common/textarea"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { useProfileManagement } from "@/hooks/use-profile-management";
import { customerProfileSchema } from "@/lib/validations/profile-schemas"
import { CustomerFormData } from "@/types/profile-management"

interface CreateCustomerModalProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  onSuccess?: () => void
}

const countries = [
  { id: 1, label: "Kenya" },
  { id: 2, label: "Uganda" },
  { id: 3, label: "Tanzania" },
  { id: 4, label: "Rwanda" },
]

export function CreateCustomerModal({ open, onOpenChange, onSuccess }: CreateCustomerModalProps) {
  const { createProfile, isSubmitting } = useProfileManagement()

  const {
    register,
    handleSubmit,
    formState: { errors },
    control,
    setValue,
  } = useForm<CustomerFormData>({
    resolver: zodResolver(customerProfileSchema),
    defaultValues: {
      third_party_name: "",
      email: "",
      phone: "",
      physical_address: "",
      registration_number: "",
    },
  })

  const onSubmit = async (data: CustomerFormData) => {
    const profile = await createProfile("customer", data)
    if (profile) {
      onOpenChange(false)
      onSuccess?.()
    }
  }

  const countryIdValue = useWatch({ control, name: "country_id" })

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <div className="flex items-center gap-2">
            <div className="p-2 bg-primary/10 rounded-lg">
              <User className="h-5 w-5 text-primary" />
            </div>
            <div>
              <DialogTitle>Create Customer Profile</DialogTitle>
              <DialogDescription>
                Please provide your business or personal details
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-4"
          >
            <div className="space-y-2">
              <Label htmlFor="third_party_name">Full Name / Business Name *</Label>
              <Input
                id="third_party_name"
                {...register("third_party_name")}
                placeholder="Enter name"
              />
              {errors.third_party_name && (
                <p className="text-sm text-destructive">{errors.third_party_name.message}</p>
              )}
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="email">Email Address *</Label>
                <Input
                  id="email"
                  type="email"
                  {...register("email")}
                  placeholder="customer@example.com"
                />
                {errors.email && (
                  <p className="text-sm text-destructive">{errors.email.message}</p>
                )}
              </div>

              <div className="space-y-2">
                <Label htmlFor="phone">Phone Number *</Label>
                <Input
                  id="phone"
                  {...register("phone")}
                  placeholder="+254..."
                />
                {errors.phone && (
                  <p className="text-sm text-destructive">{errors.phone.message}</p>
                )}
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="registration_number">ID / Reg Number (Optional)</Label>
                <Input
                  id="registration_number"
                  {...register("registration_number")}
                  placeholder="e.g. 12345678"
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="country_id">Country *</Label>
                <Select
                  onValueChange={(v) => setValue("country_id", parseInt(v))}
                  defaultValue={countryIdValue?.toString()}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select country" />
                  </SelectTrigger>
                  <SelectContent>
                    {countries.map((c) => (
                      <SelectItem key={c.id} value={c.id.toString()}>
                        {c.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.country_id && (
                  <p className="text-sm text-destructive">{errors.country_id.message}</p>
                )}
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="physical_address">Physical Address *</Label>
              <Textarea
                id="physical_address"
                {...register("physical_address")}
                placeholder="Enter your location details"
                rows={3}
              />
              {errors.physical_address && (
                <p className="text-sm text-destructive">{errors.physical_address.message}</p>
              )}
            </div>
          </motion.div>

          <div className="flex justify-end pt-4 border-t gap-3">
            <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
              Cancel
            </Button>
            <Button type="submit" disabled={isSubmitting}>
              {isSubmitting ? (
                <>
                  <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                  Saving...
                </>
              ) : (
                "Create Profile"
              )}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  )
}
