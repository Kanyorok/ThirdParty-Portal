"use client"

import { useEffect, useState } from "react"
import { useSession } from "next-auth/react"
import { Building2, Home, Loader2, Plus, ShoppingCart } from "lucide-react"
import { toast } from "sonner"

import { Button } from "@/components/common/button"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/common/dialog"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { activateProfile, type ProfileActivationPayload } from "@/lib/api/profile-management"

type ProfileKind = ProfileActivationPayload["type"]
type Option = { id?: number; value?: string; name?: string; Description?: string; Value?: string }
type DocumentRequirement = {
  id: number
  name: string
  description?: string | null
  isRequired: boolean
  allowedExtensions?: string[]
  maxFileSizeKb?: number | null
}

type Props = {
  hasSupplier: boolean
  hasTenant: boolean
  hasCustomer: boolean
  onActivated: (type: ProfileKind, status: "active" | "pending_approval") => Promise<void> | void
}

const profileOptions = [
  { type: "supplier" as const, label: "Supplier", icon: Building2 },
  { type: "tenant" as const, label: "Tenant", icon: Home },
  { type: "customer" as const, label: "Customer", icon: ShoppingCart },
]

async function loadOptions(url: string, group?: string): Promise<Option[]> {
  const response = await fetch(url, { cache: "no-store", headers: { Accept: "application/json" } })
  const payload = await response.json().catch(() => null)
  if (!response.ok) throw new Error(payload?.message || "Could not load profile options.")
  if (Array.isArray(payload?.data)) return payload.data
  return group && Array.isArray(payload?.data?.[group]) ? payload.data[group] : []
}

export default function ProfileActivationCard({ hasSupplier, hasTenant, hasCustomer, onActivated }: Props) {
  const { data: session, update } = useSession()
  const [selectedType, setSelectedType] = useState<ProfileKind | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [isLoadingOptions, setIsLoadingOptions] = useState(false)
  const [options, setOptions] = useState<Record<string, Option[]>>({})
  const [documentRequirements, setDocumentRequirements] = useState<DocumentRequirement[]>([])
  const [documentFiles, setDocumentFiles] = useState<Record<number, File>>({})
  const [documentNotes, setDocumentNotes] = useState<Record<number, string>>({})
  const [form, setForm] = useState({
    categoryId: "",
    tenantType: "",
    remarks: "",
    dateOfBirth: "",
    gender: "",
    maritalStatus: "",
    occupation: "",
  })

  const enabled = { supplier: hasSupplier, tenant: hasTenant, customer: hasCustomer }
  const remaining = profileOptions.filter((profile) => !enabled[profile.type])

  useEffect(() => {
    if (!selectedType) return
    setIsLoadingOptions(true)

    const requests = selectedType === "supplier"
      ? [
          ["categories", "/api/portal/auth/metadata/supplier-categories", undefined],
          ["documentRequirements", "/api/portal/auth/metadata/supplier-registration-document-requirements", undefined],
        ] as const
      : selectedType === "tenant"
        ? [["tenantTypes", "/api/portal/auth/lookups/bulk?codes=TenantType", "TenantType"]] as const
        : [
            ["genders", "/api/portal/auth/lookups/bulk?codes=Gender", "Gender"],
            ["maritalStatuses", "/api/portal/auth/lookups/bulk?codes=MaritalStatus", "MaritalStatus"],
            ["occupations", "/api/portal/auth/lookups/bulk?codes=Occupation", "Occupation"],
          ] as const

    Promise.all(requests.map(async ([key, url, group]) => [key, await loadOptions(url, group)] as const))
      .then((entries) => {
        const loaded = Object.fromEntries(entries)
        setOptions((current) => ({ ...current, ...loaded }))
        if (selectedType === "supplier") {
          setDocumentRequirements((loaded.documentRequirements ?? []) as DocumentRequirement[])
        }
      })
      .catch((error) => toast.error(error instanceof Error ? error.message : "Could not load profile options."))
      .finally(() => setIsLoadingOptions(false))
  }, [selectedType])

  const optionValue = (option: Option) => String(option.value ?? option.Value ?? option.id ?? "")
  const optionLabel = (option: Option) => String(option.name ?? option.Description ?? option.value ?? option.Value ?? "Option")

  const submit = async () => {
    if (!selectedType) return

    const payload: ProfileActivationPayload = { type: selectedType }
    if (selectedType === "supplier") {
      const categoryId = Number(form.categoryId)
      if (!categoryId) return toast.error("Select a supplier category.")
      const missingDocument = documentRequirements.find((requirement) => requirement.isRequired && !documentFiles[requirement.id])
      if (missingDocument) return toast.error(`${missingDocument.name} is required.`)
      payload.category_ids = [categoryId]
      payload.primary_category_id = categoryId
    } else if (selectedType === "tenant") {
      if (form.tenantType) payload.tenant_type = Number(form.tenantType)
      payload.remarks = form.remarks || "Profile enabled from the third-party portal."
    } else {
      if (!form.dateOfBirth || !form.gender || !form.maritalStatus || !form.occupation) {
        return toast.error("Complete all customer profile fields.")
      }
      Object.assign(payload, {
        date_of_birth: form.dateOfBirth,
        gender: form.gender,
        marital_status: form.maritalStatus,
        occupation: form.occupation,
      })
    }

    setIsSubmitting(true)
    try {
      const requestBody = selectedType === "supplier"
        ? (() => {
            const data = new FormData()
            data.append("type", payload.type)
            payload.category_ids?.forEach((categoryId) => data.append("category_ids[]", String(categoryId)))
            if (payload.primary_category_id) data.append("primary_category_id", String(payload.primary_category_id))
            Object.entries(documentFiles).forEach(([requirementId, file]) => data.append(`registration_documents[${requirementId}]`, file))
            Object.entries(documentNotes).forEach(([requirementId, note]) => {
              if (note.trim()) data.append(`registration_document_notes[${requirementId}]`, note.trim())
            })
            return data
          })()
        : payload
      const response = await activateProfile(requestBody)
      const status = response.data?.status ?? (selectedType === "supplier" ? "pending_approval" : "active")
      const sessionUser = (session?.user ?? {}) as Record<string, unknown>
      await update({
        user: {
          ...sessionUser,
          ...(selectedType === "tenant" ? { isTenant: true } : {}),
          ...(selectedType === "customer" ? { isCustomer: true } : {}),
          ...(selectedType === "supplier" && status === "active" ? { isSupplier: true } : {}),
        },
      })
      toast.success(response.message)
      setSelectedType(null)
      await onActivated(selectedType, status)
    } catch (error) {
      toast.error(error instanceof Error ? error.message : "Could not enable this profile.")
    } finally {
      setIsSubmitting(false)
    }
  }

  if (remaining.length === 0) return null

  return (
    <>
      <section className="rounded-2xl border border-border/60 bg-background p-5">
        <div className="flex items-center gap-2">
          <Plus className="h-4 w-4 text-primary" />
          <h2 className="text-sm font-semibold text-foreground">Add another profile</h2>
        </div>
        <p className="mt-2 text-xs leading-5 text-muted-foreground">
          Reuse these verified party details for another ERP profile.
        </p>
        <div className="mt-4 grid gap-2">
          {remaining.map(({ type, label, icon: Icon }) => (
            <Button key={type} type="button" variant="outline" className="justify-start" onClick={() => setSelectedType(type)}>
              <Icon className="mr-2 h-4 w-4" /> Enable {label}
            </Button>
          ))}
        </div>
      </section>

      <Dialog open={Boolean(selectedType)} onOpenChange={(open) => !open && !isSubmitting && setSelectedType(null)}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Enable {selectedType ? selectedType[0].toUpperCase() + selectedType.slice(1) : "profile"}</DialogTitle>
            <DialogDescription>
              Existing company and account-owner details will be reused. Supplier access requires ERP approval.
            </DialogDescription>
          </DialogHeader>

          {isLoadingOptions ? (
            <div className="flex min-h-32 items-center justify-center"><Loader2 className="h-5 w-5 animate-spin" /></div>
          ) : (
            <div className="grid gap-4 py-2">
              {selectedType === "supplier" && (
                <>
                  <div className="grid gap-2">
                    <Label htmlFor="profile-category">Primary supplier category</Label>
                    <select id="profile-category" className="h-11 rounded-md border border-input bg-background px-3 text-sm" value={form.categoryId} onChange={(event) => setForm({ ...form, categoryId: event.target.value })}>
                      <option value="">Select category</option>
                      {(options.categories ?? []).map((option) => <option key={optionValue(option)} value={optionValue(option)}>{optionLabel(option)}</option>)}
                    </select>
                  </div>

                  <div className="grid gap-3 border-t border-border/60 pt-4">
                    <div>
                      <h3 className="text-sm font-semibold text-foreground">Supplier registration documents</h3>
                      <p className="mt-1 text-xs text-muted-foreground">These are attached to the ERP supplier record for review.</p>
                    </div>
                    {documentRequirements.length === 0 ? (
                      <p className="rounded-lg border border-dashed border-border p-3 text-xs text-muted-foreground">No supplier documents are currently configured.</p>
                    ) : documentRequirements.map((requirement) => (
                      <div key={requirement.id} className="grid gap-2 rounded-xl border border-border/60 bg-muted/20 p-3">
                        <Label htmlFor={`supplier-document-${requirement.id}`}>
                          {requirement.name}{requirement.isRequired ? " *" : ""}
                        </Label>
                        {requirement.description ? <p className="text-xs text-muted-foreground">{requirement.description}</p> : null}
                        <Input
                          id={`supplier-document-${requirement.id}`}
                          type="file"
                          required={requirement.isRequired}
                          accept={(requirement.allowedExtensions ?? []).map((extension) => `.${extension}`).join(",") || undefined}
                          onChange={(event) => {
                            const file = event.target.files?.[0]
                            setDocumentFiles((current) => {
                              const next = { ...current }
                              if (file) next[requirement.id] = file
                              else delete next[requirement.id]
                              return next
                            })
                          }}
                        />
                        <Input
                          value={documentNotes[requirement.id] ?? ""}
                          onChange={(event) => setDocumentNotes((current) => ({ ...current, [requirement.id]: event.target.value }))}
                          placeholder="Optional document note"
                        />
                        <p className="text-[11px] text-muted-foreground">
                          {(requirement.allowedExtensions ?? []).length ? `Allowed: ${requirement.allowedExtensions?.join(", ")}. ` : ""}
                          {requirement.maxFileSizeKb ? `Maximum ${Math.ceil(requirement.maxFileSizeKb / 1024)} MB.` : ""}
                        </p>
                      </div>
                    ))}
                  </div>
                </>
              )}

              {selectedType === "tenant" && (
                <>
                  <div className="grid gap-2">
                    <Label htmlFor="tenant-type">Tenant type</Label>
                    <select id="tenant-type" className="h-11 rounded-md border border-input bg-background px-3 text-sm" value={form.tenantType} onChange={(event) => setForm({ ...form, tenantType: event.target.value })}>
                      <option value="">Use default tenant type</option>
                      {(options.tenantTypes ?? []).map((option) => <option key={optionValue(option)} value={optionValue(option)}>{optionLabel(option)}</option>)}
                    </select>
                  </div>
                  <div className="grid gap-2"><Label htmlFor="tenant-remarks">Remarks</Label><Input id="tenant-remarks" value={form.remarks} onChange={(event) => setForm({ ...form, remarks: event.target.value })} placeholder="Reason for enabling tenant access" /></div>
                </>
              )}

              {selectedType === "customer" && (
                <>
                  <div className="grid gap-2"><Label htmlFor="customer-dob">Date of birth</Label><Input id="customer-dob" type="date" value={form.dateOfBirth} onChange={(event) => setForm({ ...form, dateOfBirth: event.target.value })} /></div>
                  {[
                    ["gender", "Gender", "genders"],
                    ["maritalStatus", "Marital status", "maritalStatuses"],
                    ["occupation", "Occupation", "occupations"],
                  ].map(([field, label, key]) => (
                    <div className="grid gap-2" key={field}>
                      <Label htmlFor={`customer-${field}`}>{label}</Label>
                      <select id={`customer-${field}`} className="h-11 rounded-md border border-input bg-background px-3 text-sm" value={form[field as keyof typeof form]} onChange={(event) => setForm({ ...form, [field]: event.target.value })}>
                        <option value="">Select {label.toLowerCase()}</option>
                        {(options[key] ?? []).map((option) => <option key={optionValue(option)} value={optionValue(option)}>{optionLabel(option)}</option>)}
                      </select>
                    </div>
                  ))}
                </>
              )}
            </div>
          )}

          <DialogFooter>
            <Button type="button" variant="outline" disabled={isSubmitting} onClick={() => setSelectedType(null)}>Cancel</Button>
            <Button type="button" disabled={isSubmitting || isLoadingOptions} onClick={submit}>
              {isSubmitting && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              {selectedType === "supplier" ? "Submit for approval" : "Activate profile"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </>
  )
}
