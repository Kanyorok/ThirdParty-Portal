import { z } from "zod"

export const supplierProfileSchema = z.object({
  third_party_name: z.string().min(2, "Company name must be at least 2 characters").max(255),
  trading_name: z.string().min(2, "Trading name must be at least 2 characters").max(255),
  registration_number: z.string().min(1, "Registration number is required").max(50),
  tax_pin: z.string().max(50).optional().or(z.literal("")),
  kra_pin: z.string().max(50).optional().or(z.literal("")),
  physical_address: z.string().max(500).optional().or(z.literal("")),
  country: z.string().max(100).optional().or(z.literal("")),
  city: z.string().max(100).optional().or(z.literal("")),
  website: z.string().url("Invalid website URL").optional().or(z.literal("")),
  business_type: z.string().max(100).optional().or(z.literal("")),
  supplier_categories: z.array(z.number()).min(1, "Select at least one category"),
})

export const tenantProfileSchema = z.object({
  third_party_name: z.string().min(2, "Company name must be at least 2 characters").max(255),
  trading_name: z.string().max(255).optional().or(z.literal("")),
  registration_number: z.string().max(50).optional().or(z.literal("")),
  physical_address: z.string().max(500).optional().or(z.literal("")),
  country: z.string().max(100).optional().or(z.literal("")),
  city: z.string().max(100).optional().or(z.literal("")),
  tenant_type: z.number().min(1, "Please select a tenant type"),
})

export const customerProfileSchema = z.object({
  third_party_name: z.string().min(2, "Name must be at least 2 characters").max(255),
  phone_number: z.string().max(20).optional().or(z.literal("")),
  alternative_phone: z.string().max(20).optional().or(z.literal("")),
  shipping_address: z.string().max(500).optional().or(z.literal("")),
  billing_address: z.string().max(500).optional().or(z.literal("")),
  country: z.string().max(100).optional().or(z.literal("")),
  city: z.string().max(100).optional().or(z.literal("")),
})

export type SupplierProfileFormData = z.infer<typeof supplierProfileSchema>
export type TenantProfileFormData = z.infer<typeof tenantProfileSchema>
export type CustomerProfileFormData = z.infer<typeof customerProfileSchema>
