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
  FormDescription,
} from "@/components/common/form"
import { useProfile } from "@/hooks/use-profile"
import {
  Loader2,
  Building2,
  MapPin,
  Globe,
  Mail,
  Phone,
  Hash,
  CreditCard,
  CheckCircle2,
  Save,
} from "lucide-react"
import { toast } from "sonner"

const profileFormSchema = z.object({
  ThirdPartyName: z.string().min(2, "Company name must be at least 2 characters"),
  TradingName: z.string().optional().nullable(),
  Email: z.string().email("Invalid email address").optional().or(z.literal("")).nullable(),
  Phone: z.string().optional().nullable(),
  Website: z.string().url("Invalid URL format").optional().or(z.literal("")).nullable(),
  PhysicalAddress: z.string().optional().nullable(),
  RegistrationNumber: z.string().optional().nullable(),
  TaxPIN: z.string().optional().nullable(),
})

type ProfileFormValues = z.infer<typeof profileFormSchema>

interface ProfileEditFormProps {
  onCancel?: () => void
  onSuccess?: () => void
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
    },
  })

  const onSubmit = async (data: ProfileFormValues) => {
    try {
      const cleanedData = Object.fromEntries(
        Object.entries(data).map(([k, v]) => [k, v === "" ? null : v])
      )
      await updateProfile(cleanedData)
      toast.success("Profile updated successfully")
      onSuccess?.()
    } catch (error: any) {
      toast.error(error?.message || "Failed to update profile")
    }
  }

  if (isLoading) {
    return (
      <div className="min-h-[500px] flex items-center justify-center">
        <div className="text-center space-y-4">
          <Loader2 className="h-10 w-10 animate-spin text-primary mx-auto" />
          <p className="text-sm text-muted-foreground">Loading profile data...</p>
        </div>
      </div>
    )
  }

  return (
    <Form {...form}>
      <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-8">
        {/* Header */}
        <div className="bg-gradient-to-br from-primary/5 via-primary/3 to-transparent border border-border/50 rounded-2xl p-8">
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div className="space-y-2">
              <h1 className="text-3xl font-bold tracking-tight">Edit Profile</h1>
              <p className="text-muted-foreground">
                Update your business information and contact details
              </p>
            </div>
            <div className="flex items-center gap-3 w-full md:w-auto">
              <Button
                type="button"
                variant="outline"
                onClick={onCancel}
                disabled={isUpdating}
                className="flex-1 md:flex-none rounded-xl px-6 h-11 font-semibold border-2"
              >
                Cancel
              </Button>
              <Button
                type="submit"
                disabled={isUpdating}
                className="flex-1 md:flex-none rounded-xl px-8 h-11 font-semibold"
              >
                {isUpdating ? (
                  <>
                    <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                    Saving...
                  </>
                ) : (
                  <>
                    <Save className="h-4 w-4 mr-2" />
                    Save Changes
                  </>
                )}
              </Button>
            </div>
          </div>
        </div>

        <div className="border border-border/50 rounded-2xl p-8 bg-card space-y-8">
          <div className="flex items-center gap-3 pb-4 border-b border-border/50">
            <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
              <Building2 className="w-5 h-5 text-primary" />
            </div>
            <div>
              <h2 className="text-lg font-bold">Business Information</h2>
              <p className="text-sm text-muted-foreground">
                Official company registration details
              </p>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormField
              control={form.control}
              name="ThirdPartyName"
              render={({ field }) => (
                <FormItem>
                  <FormLabel className="text-sm font-semibold flex items-center gap-2">
                    <Building2 className="w-4 h-4 text-primary/60" />
                    Legal Company Name
                  </FormLabel>
                  <FormControl>
                    <Input
                      {...field}
                      value={field.value || ""}
                      placeholder="Enter legal company name"
                      className="h-11 rounded-xl border-2 focus-visible:ring-1"
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="TradingName"
              render={({ field }) => (
                <FormItem>
                  <FormLabel className="text-sm font-semibold">
                    Trading Name
                  </FormLabel>
                  <FormControl>
                    <Input
                      {...field}
                      value={field.value || ""}
                      placeholder="Enter trading name (optional)"
                      className="h-11 rounded-xl border-2 focus-visible:ring-1"
                    />
                  </FormControl>
                  <FormDescription className="text-xs">
                    The name your business operates under
                  </FormDescription>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="RegistrationNumber"
              render={({ field }) => (
                <FormItem>
                  <FormLabel className="text-sm font-semibold flex items-center gap-2">
                    <Hash className="w-4 h-4 text-primary/60" />
                    Registration Number
                  </FormLabel>
                  <FormControl>
                    <Input
                      {...field}
                      value={field.value || ""}
                      placeholder="Enter registration number"
                      className="h-11 rounded-xl border-2 focus-visible:ring-1"
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="TaxPIN"
              render={({ field }) => (
                <FormItem>
                  <FormLabel className="text-sm font-semibold flex items-center gap-2">
                    <CreditCard className="w-4 h-4 text-primary/60" />
                    Tax PIN / VAT Number
                  </FormLabel>
                  <FormControl>
                    <Input
                      {...field}
                      value={field.value || ""}
                      placeholder="Enter tax PIN or VAT number"
                      className="h-11 rounded-xl border-2 focus-visible:ring-1"
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          </div>
        </div>

        {/* Contact Information Section */}
        <div className="border border-border/50 rounded-2xl p-8 bg-card space-y-8">
          <div className="flex items-center gap-3 pb-4 border-b border-border/50">
            <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
              <Mail className="w-5 h-5 text-primary" />
            </div>
            <div>
              <h2 className="text-lg font-bold">Contact Information</h2>
              <p className="text-sm text-muted-foreground">
                Primary communication channels
              </p>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormField
              control={form.control}
              name="Email"
              render={({ field }) => (
                <FormItem>
                  <FormLabel className="text-sm font-semibold flex items-center gap-2">
                    <Mail className="w-4 h-4 text-primary/60" />
                    Corporate Email
                  </FormLabel>
                  <FormControl>
                    <Input
                      {...field}
                      value={field.value || ""}
                      type="email"
                      placeholder="contact@company.com"
                      className="h-11 rounded-xl border-2 focus-visible:ring-1"
                    />
                  </FormControl>
                  <FormDescription className="text-xs">
                    Primary email for business communications
                  </FormDescription>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="Phone"
              render={({ field }) => (
                <FormItem>
                  <FormLabel className="text-sm font-semibold flex items-center gap-2">
                    <Phone className="w-4 h-4 text-primary/60" />
                    Phone Number
                  </FormLabel>
                  <FormControl>
                    <Input
                      {...field}
                      value={field.value || ""}
                      type="tel"
                      placeholder="+254 700 000 000"
                      className="h-11 rounded-xl border-2 focus-visible:ring-1"
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="Website"
              render={({ field }) => (
                <FormItem className="md:col-span-2">
                  <FormLabel className="text-sm font-semibold flex items-center gap-2">
                    <Globe className="w-4 h-4 text-primary/60" />
                    Website URL
                  </FormLabel>
                  <FormControl>
                    <Input
                      {...field}
                      value={field.value || ""}
                      type="url"
                      placeholder="https://www.yourcompany.com"
                      className="h-11 rounded-xl border-2 focus-visible:ring-1"
                    />
                  </FormControl>
                  <FormDescription className="text-xs">
                    Your company's official website
                  </FormDescription>
                  <FormMessage />
                </FormItem>
              )}
            />
          </div>
        </div>

        <div className="border border-border/50 rounded-2xl p-8 bg-card space-y-8">
          <div className="flex items-center gap-3 pb-4 border-b border-border/50">
            <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
              <MapPin className="w-5 h-5 text-primary" />
            </div>
            <div>
              <h2 className="text-lg font-bold">Physical Location</h2>
              <p className="text-sm text-muted-foreground">
                Your business headquarters address
              </p>
            </div>
          </div>

          <FormField
            control={form.control}
            name="PhysicalAddress"
            render={({ field }) => (
              <FormItem>
                <FormLabel className="text-sm font-semibold flex items-center gap-2">
                  <MapPin className="w-4 h-4 text-primary/60" />
                  Full Physical Address
                </FormLabel>
                <FormControl>
                  <Input
                    {...field}
                    value={field.value || ""}
                    placeholder="Building, Street, City, Country"
                    className="h-11 rounded-xl border-2 focus-visible:ring-1"
                  />
                </FormControl>
                <FormDescription className="text-xs">
                  Complete address including building, street, city, and country
                </FormDescription>
                <FormMessage />
              </FormItem>
            )}
          />
        </div>

        <div className="flex items-center justify-between p-6 bg-muted/30 rounded-2xl border border-border/50">
          <div className="flex items-center gap-3">
            <Button
              type="button"
              variant="ghost"
              onClick={onCancel}
              disabled={isUpdating}
              className="rounded-xl px-6 h-11 font-semibold"
            >
              Discard Changes
            </Button>
            <Button
              type="submit"
              disabled={isUpdating}
              size="lg"
              className="rounded-xl px-10 h-11 font-semibold"
            >
              {isUpdating ? (
                <>
                  <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                  Updating...
                </>
              ) : (
                <>
                  <CheckCircle2 className="h-4 w-4 mr-2" />
                  Save Profile
                </>
              )}
            </Button>
          </div>
        </div>
      </form>
    </Form>
  )
}
