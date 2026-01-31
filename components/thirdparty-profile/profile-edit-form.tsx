"use client"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import * as z from "zod"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/common/form"
import { useProfile } from "@/hooks/use-profile"
import { Building2, MapPin, Globe, Mail, Phone, Hash, CreditCard, CheckCircle2, Save, X } from "lucide-react"
import { toast } from "sonner"
import { motion, type Variants } from "framer-motion"
import { Spinner } from "@/components/common/spinner"

const profileFormSchema = z.object({
  thirdPartyName: z.string().min(2, "Company name must be at least 2 characters"),
  tradingName: z.string().optional().nullable(),
  email: z.string().email("Invalid email address").optional().or(z.literal("")).nullable(),
  phone: z.string().optional().nullable(),
  website: z.string().url("Invalid URL format").optional().or(z.literal("")).nullable(),
  physicalAddress: z.string().optional().nullable(),
  registrationNumber: z.string().optional().nullable(),
  taxPIN: z.string().optional().nullable(),
  businessType: z.string().optional().nullable(),
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
    transition: { staggerChildren: 0.06 },
  },
}

const itemVariants: Variants = {
  hidden: { opacity: 0, y: 8 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { type: "spring", stiffness: 450, damping: 32 },
  },
}

export function ProfileEditForm({ onCancel, onSuccess }: ProfileEditFormProps) {
  const { profile, thirdPartyDetails, isLoading, updateProfile, isUpdating } = useProfile()

  const form = useForm<ProfileFormValues>({
    resolver: zodResolver(profileFormSchema),
    defaultValues: {
      thirdPartyName: thirdPartyDetails?.thirdPartyName || "",
      tradingName: thirdPartyDetails?.tradingName || "",
      email: profile?.email || "",
      phone: profile?.phone || "",
      website: thirdPartyDetails?.website || "",
      physicalAddress: thirdPartyDetails?.physicalAddress || "",
      registrationNumber: thirdPartyDetails?.registrationNumber || "",
      taxPIN: thirdPartyDetails?.taxPIN || "",
      businessType: thirdPartyDetails?.businessType || "",
    },
  })

  const onSubmit = async (data: ProfileFormValues) => {
    const cleanedData = Object.fromEntries(Object.entries(data).map(([k, v]) => [k, v === "" ? null : v]))

    toast.promise(
      (async () => {
        await updateProfile(cleanedData as any)
        onSuccess?.()
        return "Profile updated successfully"
      })(),
      {
        loading: "Updating profile...",
        success: (msg) => msg,
        error: (e) => e?.message || "Failed to update profile",
      },
    )
  }

  if (isLoading) {
    return (
      <div className="flex justify-center items-center h-96">
        <div className="text-center space-y-4">
          <Spinner className="size-8 animate-spin text-primary mx-auto" />
          <p className="text-sm text-muted-foreground">Loading profile data...</p>
        </div>
      </div>
    )
  }

  return (
    <motion.div variants={containerVariants} initial="hidden" animate="visible" className="w-full space-y-6">
      <Form {...form}>
        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
          <motion.div
            variants={itemVariants}
            className="flex items-center justify-between pb-6 border-b border-border/30"
          >
            <div className="space-y-1">
              <h2 className="text-2xl font-black uppercase tracking-tight">Edit Profile</h2>
              <p className="text-sm text-muted-foreground">Update your business information and contact details</p>
            </div>
            <div className="flex items-center gap-2">
              <Button type="button" variant="outline" size="sm" onClick={onCancel} disabled={isUpdating}>
                <X className="size-4 mr-1.5" />
                Cancel
              </Button>
              <Button type="submit" size="sm" disabled={isUpdating}>
                {isUpdating ? <Spinner className="size-4 mr-1.5 animate-spin" /> : <Save className="size-4 mr-1.5" />}
                Save Changes
              </Button>
            </div>
          </motion.div>

          <motion.div variants={itemVariants} className="p-6 border border-border/30 rounded-xl bg-card/50">
            <div className="flex items-center gap-3 mb-6 pb-4 border-b border-border/20">
              <div className="p-2 rounded-lg bg-primary/10">
                <Building2 className="size-4 text-primary" />
              </div>
              <h3 className="text-sm font-bold uppercase tracking-wider">Business Information</h3>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
              <FormField
                control={form.control}
                name="thirdPartyName"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-xs font-bold uppercase tracking-wider">Legal Company Name</FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        placeholder="Enter legal company name"
                        className="h-10 text-sm"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="tradingName"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-xs font-bold uppercase tracking-wider">Trading Name</FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        placeholder="Trading name (optional)"
                        className="h-10 text-sm"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="registrationNumber"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                      <Hash className="size-3" />
                      Registration Number
                    </FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        placeholder="e.g., PKI-131687"
                        className="h-10 text-sm font-mono"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="taxPIN"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                      <CreditCard className="size-3" />
                      Tax PIN / VAT Number
                    </FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        placeholder="e.g., P8J3234593F"
                        className="h-10 text-sm font-mono"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="businessType"
                render={({ field }) => (
                  <FormItem className="md:col-span-2">
                    <FormLabel className="text-xs font-bold uppercase tracking-wider">Business Type</FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        placeholder="e.g., Limited Company, Sole Proprietorship"
                        className="h-10 text-sm"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />
            </div>
          </motion.div>

          <motion.div variants={itemVariants} className="p-6 border border-border/30 rounded-xl bg-card/50">
            <div className="flex items-center gap-3 mb-6 pb-4 border-b border-border/20">
              <div className="p-2 rounded-lg bg-primary/10">
                <Mail className="size-4 text-primary" />
              </div>
              <h3 className="text-sm font-bold uppercase tracking-wider">Contact Information</h3>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
              <FormField
                control={form.control}
                name="email"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-xs font-bold uppercase tracking-wider">Corporate Email</FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        type="email"
                        placeholder="contact@company.com"
                        className="h-10 text-sm"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="phone"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                      <Phone className="size-3" />
                      Phone Number
                    </FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        type="tel"
                        placeholder="+254 700 000 000"
                        className="h-10 text-sm"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="website"
                render={({ field }) => (
                  <FormItem className="md:col-span-2">
                    <FormLabel className="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                      <Globe className="size-3" />
                      Website URL
                    </FormLabel>
                    <FormControl>
                      <Input
                        {...field}
                        value={field.value || ""}
                        type="url"
                        placeholder="https://www.yourcompany.com"
                        className="h-10 text-sm"
                      />
                    </FormControl>
                    <FormMessage className="text-xs" />
                  </FormItem>
                )}
              />
            </div>
          </motion.div>

          <motion.div variants={itemVariants} className="p-6 border border-border/30 rounded-xl bg-card/50">
            <div className="flex items-center gap-3 mb-6 pb-4 border-b border-border/20">
              <div className="p-2 rounded-lg bg-primary/10">
                <MapPin className="size-4 text-primary" />
              </div>
              <h3 className="text-sm font-bold uppercase tracking-wider">Physical Location</h3>
            </div>

            <FormField
              control={form.control}
              name="physicalAddress"
              render={({ field }) => (
                <FormItem>
                  <FormLabel className="text-xs font-bold uppercase tracking-wider">Full Physical Address</FormLabel>
                  <FormControl>
                    <Input
                      {...field}
                      value={field.value || ""}
                      placeholder="123 King Chain Road, Nairobi"
                      className="h-10 text-sm"
                    />
                  </FormControl>
                  <FormMessage className="text-xs" />
                </FormItem>
              )}
            />
          </motion.div>

          <motion.div variants={itemVariants} className="flex justify-end gap-2 pt-6 border-t border-border/30">
            <Button type="button" variant="outline" size="sm" onClick={onCancel} disabled={isUpdating}>
              <X className="size-4 mr-1.5" />
              Discard
            </Button>
            <Button type="submit" size="sm" disabled={isUpdating}>
              {isUpdating ? (
                <Spinner className="size-4 mr-1.5 animate-spin" />
              ) : (
                <CheckCircle2 className="size-4 mr-1.5" />
              )}
              Save Profile
            </Button>
          </motion.div>
        </form>
      </Form>
    </motion.div>
  )
}
