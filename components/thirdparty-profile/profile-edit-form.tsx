"use client"

import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import * as z from "zod"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/common/form"
import { useProfile } from "@/hooks/use-profile"
import {
  Building2,
  MapPin,
  Globe,
  Mail,
  Phone,
  Hash,
  CreditCard,
  CheckCircle2,
  Save,
  X,
} from "lucide-react"
import { toast } from "sonner"
import { motion, Variants } from "framer-motion"
import { Spinner } from "@/components/common/spinner"

const profileFormSchema = z.object({
  ThirdPartyName: z.string().min(2, "Company name must be at least 2 characters"),
  TradingName: z.string().optional().nullable(),
  Email: z.string().email("Invalid email address").optional().or(z.literal("")).nullable(),
  Phone: z.string().optional().nullable(),
  Website: z.string().url("Invalid URL format").optional().or(z.literal("")).nullable(),
  PhysicalAddress: z.string().optional().nullable(),
  RegistrationNumber: z.string().optional().nullable(),
  TaxPIN: z.string().optional().nullable(),
  BusinessType: z.string().optional().nullable(),
})

type ProfileFormValues = z.infer<typeof profileFormSchema>

interface ProfileEditFormProps {
  onCancel?: () => void
  onSuccess?: () => void
}

const containerVariants: Variants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: { staggerChildren: 0.06 }
  }
}

const itemVariants: Variants = {
  hidden: { opacity: 0, y: 8 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { type: 'spring', stiffness: 450, damping: 32 }
  }
}

export function ProfileEditForm({ onCancel, onSuccess }: ProfileEditFormProps) {
  const { profile, thirdPartyDetails, isLoading, updateProfile, isUpdating } = useProfile()

  const form = useForm<ProfileFormValues>({
    resolver: zodResolver(profileFormSchema),
    defaultValues: {
      ThirdPartyName: thirdPartyDetails?.thirdPartyName || "",
      TradingName: thirdPartyDetails?.tradingName || "",
      Email: profile?.email || "",
      Phone: profile?.phone || "",
      Website: thirdPartyDetails?.website || "",
      PhysicalAddress: thirdPartyDetails?.physicalAddress || "",
      RegistrationNumber: thirdPartyDetails?.registrationNumber || "",
      TaxPIN: thirdPartyDetails?.taxPIN || "",
      BusinessType: thirdPartyDetails?.businessType || "",
    },
  })

  const onSubmit = async (data: ProfileFormValues) => {
    const cleanedData = Object.fromEntries(
      Object.entries(data).map(([k, v]) => [k, v === "" ? null : v])
    )

    toast.promise(
      (async () => {
        await updateProfile(cleanedData)
        onSuccess?.()
        return 'Profile updated successfully'
      })(),
      {
        loading: 'Updating profile...',
        success: (msg) => msg,
        error: (e) => e?.message || 'Failed to update profile',
      }
    )
  }

  if (isLoading) {
    return (
      <div className="flex justify-center items-center h-96">
        <div className="text-center space-y-4">
          <Spinner className="animate-spin h-8 w-8 text-primary mx-auto" />
          <p className="text-sm text-muted-foreground">Loading profile data...</p>
        </div>
      </div>
    )
  }

  return (
    <motion.div
      variants={containerVariants}
      initial="hidden"
      animate="visible"
      className="w-full space-y-4"
    >
      <Form {...form}>
        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
          <motion.div variants={itemVariants} className="flex items-center justify-between pb-2 border-b">
            <div className="space-y-1">
              <h2 className="text-xl font-bold">Edit Profile</h2>
              <p className="text-sm text-muted-foreground">Update your business information and contact details</p>
            </div>
            <div className="flex items-center gap-2">
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={onCancel}
                disabled={isUpdating}
              >
                <X className="h-3.5 w-3.5 mr-1" />
                Cancel
              </Button>
              <Button
                type="submit"
                size="sm"
                disabled={isUpdating}
              >
                {isUpdating ? (
                  <Spinner className="animate-spin h-3.5 w-3.5 mr-1" />
                ) : (
                  <Save className="h-3.5 w-3.5 mr-1" />
                )}
                Save Changes
              </Button>
            </div>
          </motion.div>

          <motion.div variants={itemVariants} className="p-4 border rounded-lg bg-card">
            <div className="flex items-center gap-2 mb-4 pb-2 border-b">
              <Building2 className="h-4 w-4 text-primary" />
              <h3 className="text-sm font-semibold">Business Information</h3>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="ThirdPartyName"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-xs">Legal Company Name</FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        placeholder="Enter legal company name"
                        className="h-9 text-sm"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="TradingName"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-xs">Trading Name</FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        placeholder="Trading name (optional)"
                        className="h-9 text-sm"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="RegistrationNumber"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-xs">Registration Number</FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        placeholder="e.g., CR123456"
                        className="h-9 text-sm font-mono"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="TaxPIN"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-xs">Tax PIN / VAT Number</FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        placeholder="e.g., P051234567X"
                        className="h-9 text-sm font-mono"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="BusinessType"
                render={({ field }) => (
                  <FormItem className="md:col-span-2">
                    <FormLabel className="text-xs">Business Type</FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        placeholder="e.g., Limited Company, Sole Proprietorship"
                        className="h-9 text-sm"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />
            </div>
          </motion.div>

          <motion.div variants={itemVariants} className="p-4 border rounded-lg bg-card">
            <div className="flex items-center gap-2 mb-4 pb-2 border-b">
              <Mail className="h-4 w-4 text-primary" />
              <h3 className="text-sm font-semibold">Contact Information</h3>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="Email"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-xs">Corporate Email</FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        type="email"
                        placeholder="contact@company.com"
                        className="h-9 text-sm"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="Phone"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-xs">Phone Number</FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        type="tel"
                        placeholder="+254 700 000 000"
                        className="h-9 text-sm"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="Website"
                render={({ field }) => (
                  <FormItem className="md:col-span-2">
                    <FormLabel className="text-xs">Website URL</FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        type="url"
                        placeholder="https://www.yourcompany.com"
                        className="h-9 text-sm"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />
            </div>
          </motion.div>

          <motion.div variants={itemVariants} className="p-4 border rounded-lg bg-card">
            <div className="flex items-center gap-2 mb-4 pb-2 border-b">
              <MapPin className="h-4 w-4 text-primary" />
              <h3 className="text-sm font-semibold">Physical Location</h3>
            </div>

            <FormField
              control={form.control}
              name="PhysicalAddress"
              render={({ field }) => (
                <FormItem>
                  <FormLabel className="text-xs">Full Physical Address</FormLabel>
                  <FormControl>
                    <Input
                      {...field}
                      value={field.value || ""}
                      placeholder="Building, Street, City, Country"
                      className="h-9 text-sm"
                    />
                  </FormControl>
                  <FormMessage className="text-xs" />
                </FormItem>
              )}
            />
          </motion.div>

          <motion.div variants={itemVariants} className="flex justify-end gap-2 pt-2 border-t">
            <Button
              type="button"
              variant="outline"
              size="sm"
              onClick={onCancel}
              disabled={isUpdating}
            >
              <X className="h-3.5 w-3.5 mr-1" />
              Discard
            </Button>
            <Button
              type="submit"
              size="sm"
              disabled={isUpdating}
            >
              {isUpdating ? (
                <Spinner className="animate-spin h-3.5 w-3.5 mr-1" />
              ) : (
                <CheckCircle2 className="h-3.5 w-3.5 mr-1" />
              )}
              Save Profile
            </Button>
          </motion.div>
        </form>
      </Form>
    </motion.div>
  )
}