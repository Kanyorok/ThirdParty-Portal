"use client"

import { useForm, useWatch } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { motion } from "framer-motion"
import { User } from "lucide-react"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/common/dialog"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Spinner } from "@/components/common/spinner"
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
    setValue,
  } = useForm<CustomerFormData>({
    resolver: zodResolver(customerProfileSchema),
    defaultValues: {
      third_party_name: "",
      phone_number: "",
      alternative_phone: "",
      shipping_address: "",
      billing_address: "",
      country: "",
      city: "",
    },
  })

  const onSubmit = async (data: CustomerFormData) => {
    const profile = await createProfile("customer", data)
    if (profile) {
      onOpenChange(false)
      onSuccess?.()
    }
  }

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
                <Label htmlFor="phone_number">Phone Number</Label>
                <Input
                  id="phone_number"
                  {...register("phone_number")}
                  placeholder="+254..."
                />
                {errors.phone_number && (
                  <p className="text-sm text-destructive">{errors.phone_number.message}</p>
                )}
              </div>

              <div className="space-y-2">
                <Label htmlFor="alternative_phone">Alternative Phone</Label>
                <Input
                  id="alternative_phone"
                  {...register("alternative_phone")}
                  placeholder="+254..."
                />
                {errors.alternative_phone && (
                  <p className="text-sm text-destructive">{errors.alternative_phone.message}</p>
                )}
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="country">Country</Label>
                <Select onValueChange={(value) => setValue("country", value)}>
                  <SelectTrigger>
                    <SelectValue placeholder="Select country" />
                  </SelectTrigger>
                  <SelectContent>
                    {countries.map((c) => (
                      <SelectItem key={c.id} value={c.label}>
                        {c.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.country && (
                  <p className="text-sm text-destructive">{errors.country.message}</p>
                )}
              </div>

              <div className="space-y-2">
                <Label htmlFor="city">City</Label>
                <Input
                  id="city"
                  {...register("city")}
                  placeholder="Enter city"
                />
                {errors.city && (
                  <p className="text-sm text-destructive">{errors.city.message}</p>
                )}
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="shipping_address">Shipping Address</Label>
              <Textarea
                id="shipping_address"
                {...register("shipping_address")}
                placeholder="Enter shipping address"
                rows={3}
              />
              {errors.shipping_address && (
                <p className="text-sm text-destructive">{errors.shipping_address.message}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="billing_address">Billing Address</Label>
              <Textarea
                id="billing_address"
                {...register("billing_address")}
                placeholder="Enter billing address"
                rows={3}
              />
              {errors.billing_address && (
                <p className="text-sm text-destructive">{errors.billing_address.message}</p>
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
                  <Spinner className="mr-2 h-4 w-4" />
                  Saving profile
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
