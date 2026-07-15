"use client"

import { useCallback, useEffect, useRef, useState } from "react"
import { useForm, useWatch } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"

import {
    canonicalizeBusinessTypeValue,
    emptyRegistrationMetadata,
    fetchLocalJson,
    isCompanyLikeBusinessType,
    normalizePhoneNumber,
    normalizeLookupItems,
    pickLookupItems,
    type CountryItem,
    type LocalityItem,
    type RegistrationMetadata,
    type SupplierCategoryItem,
    type SupplierDocumentRequirement,
} from "@/lib/register-shared"

const ROLE_VALUES = ["SU", "TN", "CU"] as const
type RoleValue = (typeof ROLE_VALUES)[number]

const PHONE_REGEX = /^\+?[0-9]{8,15}$/
const TAX_IDENTIFIER_REGEX = /^[A-Z][0-9]{9}[A-Z]$/
const GENERIC_REGISTRATION_ERROR = "We couldn't complete registration. Please correct the highlighted fields and try again."
const SENSITIVE_ERROR_PATTERN = /(exception|stack|trace|sql|syntax|internal server|undefined|vendor|route|line\s+\d+)/i
const DOCUMENT_KEY_PATTERN = /^registration_documents\.(\d+)$/
const DOCUMENT_NOTE_KEY_PATTERN = /^registration_document_notes\.(\d+)$/
const VERIFY_EMAIL_LINK_REGEX = /https?:\/\/[^"'<>\s]+\/(?:email\/verify|verify-email)[^"'<>\s]*/i

const SERVER_FIELD_FALLBACK_MESSAGES: Record<string, string> = {
    Name: "Please enter a valid legal company name.",
    TradingName: "Please enter a valid trading name.",
    BusinessType: "Please select a valid business type.",
    RegistrationNumber: "Please enter a valid registration number.",
    TaxPIN: "Please enter a valid tax PIN.",
    VATNumber: "Please enter a valid VAT number.",
    legalForm: "Please select a valid business type.",
    contactPersonName: "Please enter the contact person's name.",
    contactPersonEmail: "Please enter a valid contact person email address.",
    contactPersonPhone: "Please enter a valid contact person phone number.",
    Country: "Please select a valid country.",
    Location: "Please select a valid location.",
    Email: "Please enter a valid business email address.",
    Phone: "Please enter a valid business phone number.",
    PhysicalAddress: "Please enter a valid physical address.",
    Website: "Please enter a valid website URL.",
    types: "Please select at least one business role.",
    category_ids: "Please select at least one supplier category.",
    user_Remarks: "Please provide tenant remarks.",
    user_DateOfBirth: "Please provide a valid date of birth.",
    user_MaritalStatus: "Please select a valid marital status.",
    user_Occupation: "Please select a valid occupation.",
    user_FirstName: "Please enter a valid first name.",
    user_LastName: "Please enter a valid last name.",
    user_Email: "Please enter a valid admin email address.",
    user_Phone: "Please enter a valid admin phone number.",
    user_Gender: "Please select a valid gender.",
    user_Password: "Please enter a valid password.",
    user_Password_confirmation: "Please confirm your password.",
    logo: "Please attach a valid logo image.",
}

const SERVER_ERROR_FIELD_ALIASES: Record<string, string> = {
    name: "Name",
    tradingName: "TradingName",
    businessType: "BusinessType",
    legalForm: "BusinessType",
    registrationNumber: "RegistrationNumber",
    taxPin: "TaxPIN",
    vatNumber: "VATNumber",
    email: "Email",
    phone: "Phone",
    physicalAddress: "PhysicalAddress",
    website: "Website",
    country: "Country",
    location: "Location",
    supplierCategoryId: "category_ids",
    supplier_category_id: "category_ids",
    primary_category_id: "category_ids",
    category_ids: "category_ids",
    categoryIds: "category_ids",
    contact_person_name: "contactPersonName",
    contact_person_email: "contactPersonEmail",
    contact_person_phone: "contactPersonPhone",
    tenant_Remarks: "user_Remarks",
    customer_DateOfBirth: "user_DateOfBirth",
    customer_MaritalStatus: "user_MaritalStatus",
    customer_Occupation: "user_Occupation",
    customer_Gender: "user_Gender",
    userFirstName: "user_FirstName",
    userLastName: "user_LastName",
    userEmail: "user_Email",
    userPhone: "user_Phone",
    userGender: "user_Gender",
    userPassword: "user_Password",
    userPasswordConfirmation: "user_Password_confirmation",
}

const NORMALIZED_SERVER_ERROR_FIELD_ALIASES: Record<string, string> = {
    physicaladdress: "PhysicalAddress",
    physical_address: "PhysicalAddress",
    taxpin: "TaxPIN",
    vatnumber: "VATNumber",
    registrationnumber: "RegistrationNumber",
    tradingname: "TradingName",
    businesstype: "BusinessType",
    legalform: "BusinessType",
    categoryids: "category_ids",
    suppliercategoryid: "category_ids",
    country: "Country",
    location: "Location",
    email: "Email",
    phone: "Phone",
    website: "Website",
    name: "Name",
    userfirstname: "user_FirstName",
    userlastname: "user_LastName",
    useremail: "user_Email",
    userphone: "user_Phone",
    usergender: "user_Gender",
    userpassword: "user_Password",
    userpasswordconfirmation: "user_Password_confirmation",
    userremarks: "user_Remarks",
    userdateofbirth: "user_DateOfBirth",
    usermaritalstatus: "user_MaritalStatus",
    useroccupation: "user_Occupation",
}

const resolveServerErrorFieldKey = (rawKey: string) => {
    const directAlias = SERVER_ERROR_FIELD_ALIASES[rawKey]
    if (directAlias) return directAlias

    const trimmedKey = rawKey.trim()
    if (!trimmedKey) return rawKey

    const normalizedKey = trimmedKey.toLowerCase().replace(/[^a-z0-9_]+/g, "")
    return NORMALIZED_SERVER_ERROR_FIELD_ALIASES[normalizedKey] ?? trimmedKey
}

const emptyToUndefined = (value: unknown) => {
    if (typeof value !== "string") return value
    const trimmed = value.trim()
    return trimmed.length > 0 ? trimmed : undefined
}

const normalizeText = (value: unknown) => {
    if (typeof value !== "string") return undefined
    const trimmed = value.trim()
    return trimmed.length > 0 ? trimmed : undefined
}

const normalizeTaxIdentifier = (value: unknown) => {
    const normalized = normalizeText(value)
    return normalized ? normalized.toUpperCase() : undefined
}

const joinContactPersonName = (firstName?: string, lastName?: string) => {
    const parts = [normalizeText(firstName), normalizeText(lastName)].filter(Boolean)
    return parts.length > 0 ? parts.join(" ") : undefined
}

const validateDocumentFile = (requirement: SupplierDocumentRequirement, file: File | null | undefined) => {
    if (!(file instanceof File)) {
        return requirement.isRequired ? `${requirement.name} is required.` : null
    }

    const allowedExtensions = requirement.allowedExtensions
        .map((extension) => extension.trim().replace(/^\./, "").toLowerCase())
        .filter(Boolean)
    const fileExtension = file.name.includes(".")
        ? file.name.slice(file.name.lastIndexOf(".") + 1).toLowerCase()
        : ""

    if (allowedExtensions.length > 0 && (!fileExtension || !allowedExtensions.includes(fileExtension))) {
        return `${requirement.name} must be one of: ${allowedExtensions.map((extension) => `.${extension}`).join(", ")}.`
    }

    if (requirement.maxFileSizeKb && file.size > requirement.maxFileSizeKb * 1024) {
        return `${requirement.name} must be ${requirement.maxFileSizeKb} KB or smaller.`
    }

    return null
}

const appendRequestValue = (target: FormData, key: string, value: unknown) => {
    if (value == null || value === "") return

    if (value instanceof File) {
        target.append(key, value, value.name)
        return
    }

    if (Array.isArray(value)) {
        value.forEach((entry) => appendRequestValue(target, `${key}[]`, entry))
        return
    }

    target.append(key, String(value))
}

const optionalTextField = (label: string, maxLength: number) =>
    z.preprocess(
        emptyToUndefined,
        z.string().trim().max(maxLength, `${label} must be ${maxLength} characters or fewer`),
    ).optional()

const optionalEmailField = (label: string) =>
    z.preprocess(
        emptyToUndefined,
        z
            .string()
            .trim()
            .email(`Please enter a valid ${label.toLowerCase()}`)
            .max(254, `${label} must be 254 characters or fewer`),
    ).optional()

const optionalNameField = (label: string) =>
    z.preprocess(
        emptyToUndefined,
        z.string().trim().min(2, `${label} must be at least 2 characters`).max(50, `${label} must be 50 characters or fewer`),
    ).optional()

const optionalPasswordField = (label: string) =>
    z.preprocess(
        emptyToUndefined,
        z.string().min(8, `${label} must be at least 8 characters`).max(128, `${label} must be 128 characters or fewer`),
    ).optional()

const optionalHttpsUrlField = (label: string, maxLength: number) =>
    z.preprocess(
        emptyToUndefined,
        z
            .string()
            .trim()
            .max(maxLength, `${label} must be ${maxLength} characters or fewer`)
            .url(`Please enter a valid ${label.toLowerCase()}`)
            .refine((value) => value.startsWith("https://"), `${label} must start with https://`),
    ).optional()

const optionalLookupField = (label: string, maxLength: number) =>
    z.preprocess(
        emptyToUndefined,
        z.string().trim().max(maxLength, `${label} must be ${maxLength} characters or fewer`),
    ).optional()

const phoneField = (requiredMessage: string) =>
    z.preprocess(
        (value) => normalizePhoneNumber(value) ?? value,
        z.string().trim().min(1, requiredMessage).regex(PHONE_REGEX, "Phone number must be 8 to 15 digits and may start with +"),
    )

const optionalPhoneField = (requiredMessage: string) => z.preprocess(emptyToUndefined, phoneField(requiredMessage)).optional()

const hasRole = (types: RoleValue[] | undefined, flag: RoleValue) => types?.includes(flag)

const getSafeServerFieldMessage = (field: string, candidate: unknown): string => {
    let fallback = SERVER_FIELD_FALLBACK_MESSAGES[field] ?? "Please provide a valid value."
    if (DOCUMENT_KEY_PATTERN.test(field)) fallback = "Please attach a valid document file."
    if (DOCUMENT_NOTE_KEY_PATTERN.test(field)) fallback = "Please provide a valid document note."
    if (typeof candidate !== "string") return fallback

    const normalized = candidate.replace(/\s+/g, " ").trim()
    if (!normalized || normalized.length > 160 || SENSITIVE_ERROR_PATTERN.test(normalized)) {
        return fallback
    }

    return normalized
}

const extractVerifyEmailUrl = (payload: Record<string, any> | null | undefined): string | null => {
    if (!payload) return null

    const candidates = [
        payload.verification_url,
        payload.verify_url,
        payload.verifyUrl,
        payload.verificationUrl,
        payload.data?.verification_url,
        payload.data?.verify_url,
        payload.data?.verifyUrl,
        payload.data?.verificationUrl,
    ]

    for (const candidate of candidates) {
        if (typeof candidate === "string" && candidate.trim()) return candidate
    }

    const message = payload.message ?? payload.data?.message
    if (typeof message === "string") {
        const match = message.match(VERIFY_EMAIL_LINK_REGEX)
        if (match?.[0]) return match[0]
    }

    return null
}

type LegacyEnumOption = {
    value?: string | number | null
    label?: string | null
    name?: string | null
}

type LegacyCountryItem = {
    id?: number | string | null
    name?: string | null
    code?: string | null
}

function normalizeLegacyCountries(rows: unknown): CountryItem[] {
    if (!Array.isArray(rows)) return []

    return rows
        .map((row) => {
            if (!row || typeof row !== "object") return null

            const item = row as Record<string, unknown>
            const id = Number(item.id ?? item.Id ?? 0)
            const name = String(item.name ?? item.Name ?? "").trim()
            const code = String(item.code ?? item.Code ?? "").trim()

            if (!Number.isFinite(id) || id <= 0 || !name) return null

            return {
                id,
                name,
                code: code || name.slice(0, 3).toUpperCase(),
            }
        })
        .filter((item): item is CountryItem => item !== null)
}

function normalizeLegacyEnumOptions(rows: unknown) {
    if (!Array.isArray(rows)) return []

    return normalizeLookupItems(
        rows.map((row) => {
            const item = row as LegacyEnumOption
            return {
                value: item?.value,
                label: item?.label ?? item?.name,
                description: item?.label ?? item?.name,
            }
        }),
    )
}

const registerSchema = z
    .object({
        Name: z.string().trim().min(2, "Company name is required").max(100, "Company name must be 100 characters or fewer"),
        TradingName: optionalTextField("Trading name", 100),
        BusinessType: z.string().trim().min(1, "Business type is required"),
        RegistrationNumber: z.string().trim().min(1, "Registration number is required").max(50, "Registration number must be 50 characters or fewer"),
        TaxPIN: z.preprocess(
            normalizeTaxIdentifier,
            z.string().regex(TAX_IDENTIFIER_REGEX, "Tax PIN must be exactly 11 characters, like P123456789X.").max(50, "Tax PIN must be 50 characters or fewer"),
        ),
        VATNumber: z.preprocess(
            normalizeTaxIdentifier,
            z.string().regex(TAX_IDENTIFIER_REGEX, "Enter VAT number e.g. P123456789X.").max(50, "VAT number must be 50 characters or fewer").optional(),
        ),
        Country: z.string().trim().min(2, "Country is required").max(3, "Please select a valid country code"),
        Location: z.coerce.number().int("Please select a valid location").min(1, "Location is required"),
        Email: optionalEmailField("Business email"),
        Phone: optionalPhoneField("Phone number is required"),
        PhysicalAddress: optionalTextField("Physical address", 200),
        Website: optionalHttpsUrlField("Website", 255),
        types: z.array(z.enum(ROLE_VALUES)).min(1, "Select at least one business role"),
        category_ids: z.array(z.coerce.number().int().positive()).optional(),
        contactPersonName: optionalTextField("Contact person name", 120),
        contactPersonEmail: optionalEmailField("Contact person email"),
        contactPersonPhone: optionalPhoneField("Contact person phone is required"),
        user_Remarks: optionalTextField("Tenant remarks", 500),
        user_DateOfBirth: optionalTextField("Date of birth", 25),
        user_MaritalStatus: optionalLookupField("Marital status", 100),
        user_Occupation: optionalLookupField("Occupation", 100),
        createUser: z.boolean(),
        user_FirstName: optionalNameField("First name"),
        user_LastName: optionalNameField("Last name"),
        user_Email: optionalEmailField("Admin email"),
        user_Phone: optionalPhoneField("Admin phone is required"),
        user_Gender: optionalLookupField("Gender", 50),
        user_Password: optionalPasswordField("Password"),
        user_Password_confirmation: optionalPasswordField("Confirm password"),
    })
    .superRefine((data, ctx) => {
        const supplierFlow = hasRole(data.types, "SU")
        const tenantFlow = hasRole(data.types, "TN")
        const customerFlow = hasRole(data.types, "CU")

        if (!data.Phone) {
            ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["Phone"], message: "Phone number is required." })
        }

        if (data.TaxPIN && data.VATNumber && data.TaxPIN !== data.VATNumber) {
            ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["VATNumber"], message: "VAT number must match Tax PIN." })
        }

        if (supplierFlow && (!data.category_ids || data.category_ids.length === 0)) {
            ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["category_ids"], message: "Select at least one supplier category." })
        }

        if (supplierFlow && isCompanyLikeBusinessType(data.BusinessType)) {
            const derivedContactName = normalizeText(data.contactPersonName) ?? (data.createUser
                ? joinContactPersonName(data.user_FirstName, data.user_LastName)
                : normalizeText(data.Name))
            const derivedContactEmail = normalizeText(data.contactPersonEmail) ?? (data.createUser
                ? normalizeText(data.user_Email)
                : normalizeText(data.Email))
            const derivedContactPhone = normalizeText(data.contactPersonPhone) ?? (data.createUser
                ? normalizeText(data.user_Phone)
                : normalizeText(data.Phone))

            if (!derivedContactName) {
                ctx.addIssue({ code: z.ZodIssueCode.custom, path: [data.createUser ? "user_FirstName" : "Name"], message: "A contact name is required." })
            }
            if (!derivedContactEmail) {
                ctx.addIssue({ code: z.ZodIssueCode.custom, path: [data.createUser ? "user_Email" : "Email"], message: "A contact email is required." })
            }
            if (!derivedContactPhone) {
                ctx.addIssue({ code: z.ZodIssueCode.custom, path: [data.createUser ? "user_Phone" : "Phone"], message: "A contact phone is required." })
            }
        }

        if (tenantFlow && !(data.user_Remarks ?? "").trim()) {
            ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Remarks"], message: "Tenant remarks are required." })
        }

        if (customerFlow) {
            if (!data.user_DateOfBirth) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_DateOfBirth"], message: "Date of birth is required for customers." })
            if (!data.user_MaritalStatus) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_MaritalStatus"], message: "Marital status is required for customers." })
            if (!data.user_Occupation) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Occupation"], message: "Occupation is required for customers." })
            if (!data.user_Gender) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Gender"], message: "Gender is required for customers." })
        }

        if (!data.createUser) return

        if (!data.user_FirstName) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_FirstName"], message: "First name is required." })
        if (!data.user_LastName) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_LastName"], message: "Last name is required." })
        if (!data.user_Email) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Email"], message: "Admin email is required." })
        if (!data.user_Phone) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Phone"], message: "Admin phone is required." })
        if (!data.user_Gender) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Gender"], message: "Gender is required." })
        if (!data.user_Password) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Password"], message: "Password is required." })
        if (!data.user_Password_confirmation) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Password_confirmation"], message: "Confirm your password." })
        if (data.user_Password && data.user_Password_confirmation && data.user_Password !== data.user_Password_confirmation) {
            ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Password_confirmation"], message: "Passwords do not match." })
        }
    })

export type RegisterFormInputs = z.infer<typeof registerSchema>
export type RegisterRole = RoleValue
export type RegisterThirdPartyResult = {
    success: boolean
    payload: Record<string, any>
    verifyEmailUrl: string | null
}

type RegisterStepValidationResult = {
    valid: boolean
    message?: string
}

export const useRegisterForm = () => {
    const [metadata, setMetadata] = useState<RegistrationMetadata>(emptyRegistrationMetadata)
    const [isLoadingMetadata, setIsLoadingMetadata] = useState(true)
    const [isLoadingLocalities, setIsLoadingLocalities] = useState(false)
    const [metadataError, setMetadataError] = useState<string | null>(null)
    const [verifyEmailUrl, setVerifyEmailUrl] = useState<string | null>(null)
    const [logoFile, setLogoFile] = useState<File | null>(null)
    const [logoError, setLogoError] = useState<string | null>(null)
    const [documentFiles, setDocumentFiles] = useState<Record<number, File | null>>({})
    const [documentNotes, setDocumentNotes] = useState<Record<number, string>>({})
    const [documentErrors, setDocumentErrors] = useState<Record<number, string>>({})
    const lastVerifyEmailUrlRef = useRef<string | null>(null)
    const lastRegisterResponseRef = useRef<RegisterThirdPartyResult | null>(null)

    const form = useForm<RegisterFormInputs>({
        resolver: zodResolver(registerSchema) as any,
        mode: "onBlur",
        defaultValues: {
            Name: "",
            TradingName: "",
            BusinessType: "",
            RegistrationNumber: "",
            TaxPIN: "",
            VATNumber: "",
            Country: "KE",
            Location: undefined,
            Email: "",
            Phone: "",
            PhysicalAddress: "",
            Website: "",
            types: [],
            category_ids: [],
            contactPersonName: "",
            contactPersonEmail: "",
            contactPersonPhone: "",
            user_Remarks: "",
            user_DateOfBirth: "",
            user_MaritalStatus: "",
            user_Occupation: "",
            createUser: true,
            user_FirstName: "",
            user_LastName: "",
            user_Email: "",
            user_Phone: "",
            user_Gender: "",
            user_Password: "",
            user_Password_confirmation: "",
        },
    })

    const selectedCountryCode = useWatch({ control: form.control, name: "Country" })
    const selectedTypes = useWatch({ control: form.control, name: "types" })

    const resetVerifyEmailUrl = useCallback(() => {
        lastVerifyEmailUrlRef.current = null
        setVerifyEmailUrl(null)
    }, [])

    const clearUploadErrors = useCallback(() => {
        setLogoError(null)
        setDocumentErrors({})
    }, [])

    const loadFallbackCountries = useCallback(async () => {
        const result = await fetchLocalJson<{ data?: LegacyCountryItem[] }>("/api/countries")
        return normalizeLegacyCountries(result.data ?? [])
    }, [])

    const loadFallbackEnumOptions = useCallback(async (endpoint: string) => {
        const result = await fetchLocalJson<LegacyEnumOption[]>(`/api/enums/${encodeURIComponent(endpoint)}`)
        return normalizeLegacyEnumOptions(result)
    }, [])

    const fetchInitialMetadata = useCallback(async () => {
        setIsLoadingMetadata(true)
        setMetadataError(null)

        try {
            const [countriesResult, businessTypesResult, supplierCategoriesResult, documentRequirementsResult, lookupsResult] =
                await Promise.allSettled([
                    fetchLocalJson<{ data?: CountryItem[] }>("/api/portal/auth/metadata/countries"),
                    fetchLocalJson<{ data?: unknown }>("/api/portal/auth/metadata/business-types"),
                    fetchLocalJson<{ data?: SupplierCategoryItem[] }>("/api/portal/auth/metadata/supplier-categories"),
                    fetchLocalJson<{ data?: SupplierDocumentRequirement[] }>("/api/portal/auth/metadata/supplier-registration-document-requirements"),
                    fetchLocalJson<Record<string, any>>("/api/portal/auth/lookups/bulk?codes=Gender,MaritalStatus,Occupation"),
                ])

            let countries = countriesResult.status === "fulfilled" ? countriesResult.value.data ?? [] : []
            let businessTypes = businessTypesResult.status === "fulfilled" ? normalizeLookupItems(businessTypesResult.value.data) : []
            const supplierCategories = supplierCategoriesResult.status === "fulfilled" ? supplierCategoriesResult.value.data ?? [] : []
            const supplierDocumentRequirements = documentRequirementsResult.status === "fulfilled" ? documentRequirementsResult.value.data ?? [] : []
            const lookupsPayload = lookupsResult.status === "fulfilled" ? lookupsResult.value : {}
            const lookupGroups = lookupsPayload?.data ?? lookupsPayload?.Data ?? {}

            if (countries.length === 0) {
                countries = await loadFallbackCountries().catch(() => [])
            }

            if (businessTypes.length === 0) {
                businessTypes = await loadFallbackEnumOptions("BusinessType").catch(() => [])
            }

            let genders = pickLookupItems(lookupGroups, "Gender")
            let maritalStatuses = pickLookupItems(lookupGroups, "MaritalStatus")
            let occupations = pickLookupItems(lookupGroups, "Occupation")

            if (genders.length === 0) {
                genders = await loadFallbackEnumOptions("Gender").catch(() => [])
            }

            if (maritalStatuses.length === 0) {
                maritalStatuses = await loadFallbackEnumOptions("MaritalStatus").catch(() => [])
            }

            if (occupations.length === 0) {
                occupations = await loadFallbackEnumOptions("Occupation").catch(() => [])
            }

            if (countries.length === 0 || businessTypes.length === 0) {
                setMetadataError("We couldn't load registration metadata right now. Please refresh and try again.")
            }

            setMetadata({
                countries,
                supplierCategories,
                localities: [],
                businessTypes,
                genders,
                maritalStatuses,
                occupations,
                supplierDocumentRequirements,
            })
        } catch {
            setMetadata(emptyRegistrationMetadata)
            setMetadataError("We couldn't load registration metadata right now. Please refresh and try again.")
        } finally {
            setIsLoadingMetadata(false)
        }
    }, [loadFallbackCountries, loadFallbackEnumOptions])

    const fetchLocalities = useCallback(async (countryId: number) => {
        if (!countryId) return
        setIsLoadingLocalities(true)

        try {
            const result = await fetchLocalJson<{ data?: LocalityItem[] }>(`/api/portal/auth/metadata/localities/${countryId}`)
            const localities = result.data ?? []

            if (localities.length > 0) {
                setMetadata((prev) => ({ ...prev, localities }))
                return
            }

            const selectedCountryCode = form.getValues("Country")
            if (!selectedCountryCode) {
                setMetadata((prev) => ({ ...prev, localities: [] }))
                return
            }

            const fallback = await fetchLocalJson<{ data?: LocalityItem[] }>(`/api/countries/${encodeURIComponent(selectedCountryCode)}/localities`)
            setMetadata((prev) => ({ ...prev, localities: fallback.data ?? [] }))
        } catch {
            const selectedCountryCode = form.getValues("Country")
            if (!selectedCountryCode) {
                setMetadata((prev) => ({ ...prev, localities: [] }))
                return
            }

            try {
                const fallback = await fetchLocalJson<{ data?: LocalityItem[] }>(`/api/countries/${encodeURIComponent(selectedCountryCode)}/localities`)
                setMetadata((prev) => ({ ...prev, localities: fallback.data ?? [] }))
            } catch {
                setMetadata((prev) => ({ ...prev, localities: [] }))
            }
        } finally {
            setIsLoadingLocalities(false)
        }
    }, [form])

    useEffect(() => {
        fetchInitialMetadata()
    }, [fetchInitialMetadata])

    useEffect(() => {
        const selectedCountry = metadata.countries.find((country) => country.code === selectedCountryCode)
        if (!selectedCountry) {
            setMetadata((prev) => ({ ...prev, localities: [] }))
            form.setValue("Location", undefined as never)
            return
        }

        fetchLocalities(selectedCountry.id)
        form.setValue("Location", undefined as never)
    }, [fetchLocalities, form, metadata.countries, selectedCountryCode])

    const setRegistrationDocumentFile = useCallback((requirementId: number, file: File | null) => {
        setDocumentFiles((prev) => ({ ...prev, [requirementId]: file }))
        setDocumentErrors((prev) => {
            const next = { ...prev }
            const requirement = metadata.supplierDocumentRequirements.find((entry) => entry.id === requirementId)
            const validationMessage = requirement ? validateDocumentFile(requirement, file) : null

            if (validationMessage) {
                next[requirementId] = validationMessage
            } else {
                delete next[requirementId]
            }

            return next
        })
    }, [metadata.supplierDocumentRequirements])

    const setRegistrationDocumentNote = useCallback((requirementId: number, note: string) => {
        setDocumentNotes((prev) => ({ ...prev, [requirementId]: note }))
    }, [])

    const setLogoUpload = useCallback((file: File | null) => {
        setLogoFile(file)
        setLogoError(null)
    }, [])

    const validateSupplierDocuments = useCallback(() => {
        if (!selectedTypes?.includes("SU")) {
            setDocumentErrors({})
            return true
        }

        const nextDocumentErrors: Record<number, string> = {}
        metadata.supplierDocumentRequirements.forEach((requirement) => {
            const validationMessage = validateDocumentFile(requirement, documentFiles[requirement.id])
            if (validationMessage) {
                nextDocumentErrors[requirement.id] = validationMessage
            }
        })

        setDocumentErrors(nextDocumentErrors)
        return Object.keys(nextDocumentErrors).length === 0
    }, [documentFiles, metadata.supplierDocumentRequirements, selectedTypes])

    const buildPayload = useCallback((values: RegisterFormInputs) => {
        const isSupplier = values.types.includes("SU")
        const isTenant = values.types.includes("TN")
        const isCustomer = values.types.includes("CU")
        const businessType = canonicalizeBusinessTypeValue(values.BusinessType)
        const taxPin = normalizeTaxIdentifier(values.TaxPIN)
        const vatNumber = normalizeTaxIdentifier(values.VATNumber)
        const payload: Record<string, unknown> = {
            Name: normalizeText(values.Name),
            TradingName: normalizeText(values.TradingName),
            BusinessType: businessType,
            RegistrationNumber: normalizeText(values.RegistrationNumber),
            TaxPIN: taxPin,
            VATNumber: vatNumber,
            PhysicalAddress: normalizeText(values.PhysicalAddress),
            Website: normalizeText(values.Website),
            Country: normalizeText(values.Country),
            Location: values.Location,
            types: values.types,
            createUser: values.createUser,
        }

        if (!values.createUser) {
            payload.Phone = normalizePhoneNumber(values.Phone) ?? ""
            const orgEmail = normalizeText(values.Email)
            if (orgEmail) payload.Email = orgEmail
        } else {
            const orgPhone = normalizePhoneNumber(values.Phone) ?? normalizePhoneNumber(values.user_Phone)
            const orgEmail = normalizeText(values.Email) ?? normalizeText(values.user_Email)
            if (orgPhone) payload.Phone = orgPhone
            if (orgEmail) payload.Email = orgEmail
        }

        if (isSupplier) {
            const categoryIds = values.category_ids ?? []
            const primaryCategoryId = categoryIds[0]

            payload.category_ids = categoryIds
            if (primaryCategoryId != null) {
                payload.supplier_category_id = primaryCategoryId
                payload.primary_category_id = primaryCategoryId
            }
            payload.legalForm = businessType
        }

        if (isTenant) {
            payload.user_Remarks = normalizeText(values.user_Remarks)
        }

        if (isCustomer) {
            payload.user_DateOfBirth = normalizeText(values.user_DateOfBirth)
            payload.user_MaritalStatus = normalizeText(values.user_MaritalStatus)
            payload.user_Occupation = normalizeText(values.user_Occupation)
            payload.user_Gender = normalizeText(values.user_Gender)
        }

        if (values.createUser) {
            payload.user_FirstName = normalizeText(values.user_FirstName)
            payload.user_LastName = normalizeText(values.user_LastName)
            payload.user_Email = normalizeText(values.user_Email)
            payload.user_Phone = normalizePhoneNumber(values.user_Phone)
            payload.user_Gender = normalizeText(values.user_Gender)
            payload.user_Password = values.user_Password
            payload.user_Password_confirmation = values.user_Password_confirmation
        }

        Object.keys(payload).forEach((key) => {
            if (key === "Phone" && !values.createUser) return
            const value = payload[key]
            if (value === undefined || value === null || value === "") {
                delete payload[key]
            }
        })

        return payload
    }, [])

    const applyServerValidationErrors = useCallback((errors: unknown) => {
        let hasFieldErrors = false

        if (!errors || typeof errors !== "object" || Array.isArray(errors)) {
            return hasFieldErrors
        }

        Object.entries(errors as Record<string, unknown>).forEach(([key, value]) => {
            const first = Array.isArray(value) ? value[0] : value

            const documentMatch = key.match(DOCUMENT_KEY_PATTERN) ?? key.match(DOCUMENT_NOTE_KEY_PATTERN)
            if (documentMatch?.[1]) {
                const requirementId = Number(documentMatch[1])
                if (Number.isFinite(requirementId)) {
                    setDocumentErrors((prev) => ({ ...prev, [requirementId]: getSafeServerFieldMessage(key, first) }))
                    hasFieldErrors = true
                }
                return
            }

            if (key === "logo") {
                setLogoError(getSafeServerFieldMessage(key, first))
                hasFieldErrors = true
                return
            }

            const fieldKey = resolveServerErrorFieldKey(key)
            form.setError(fieldKey as never, { message: getSafeServerFieldMessage(fieldKey, first) })
            hasFieldErrors = true
        })

        return hasFieldErrors
    }, [form])

    const validateRegistrationStep = useCallback(async (
        step: string,
        values: RegisterFormInputs,
        fields: string[],
    ): Promise<RegisterStepValidationResult> => {
        clearUploadErrors()
        form.clearErrors(fields as never)

        const payload = buildPayload(values)
        const stepPayload = Object.fromEntries(
            Object.entries(payload).filter(([key]) => fields.includes(key))
        )

        if (fields.includes("category_ids")) {
            if (payload.supplier_category_id != null) {
                stepPayload.supplier_category_id = payload.supplier_category_id
            }

            if (payload.primary_category_id != null) {
                stepPayload.primary_category_id = payload.primary_category_id
            }
        }

        const isSupplierMultipartStep = step === "supplier" && values.types.includes("SU")
        const headers: HeadersInit = isSupplierMultipartStep
            ? { Accept: "application/json" }
            : { "Content-Type": "application/json", Accept: "application/json" }
        const body: BodyInit = isSupplierMultipartStep
            ? (() => {
                const requestBody = new FormData()
                appendRequestValue(requestBody, "step", step)
                fields.forEach((field) => requestBody.append("fields", field))
                Object.entries(stepPayload).forEach(([key, value]) => appendRequestValue(requestBody, key, value))
                Object.entries(documentFiles).forEach(([key, file]) => {
                    if (!(file instanceof File)) return
                    requestBody.append(`registration_documents[${key}]`, file)
                })
                Object.entries(documentNotes).forEach(([key, note]) => {
                    const normalizedNote = normalizeText(note)
                    if (!normalizedNote) return
                    requestBody.append(`registration_document_notes[${key}]`, normalizedNote)
                })
                return requestBody
            })()
            : JSON.stringify({
                step,
                fields,
                ...stepPayload,
            })

        const response = await fetch("/api/portal/auth/register/validate-step", {
            method: "POST",
            headers,
            body,
        })

        const result = await response.json().catch(() => null)
        if (response.ok) {
            return { valid: true }
        }

        const hasFieldErrors = applyServerValidationErrors(result?.errors)
        const message = typeof result?.message === "string" && result.message.trim()
            ? result.message.trim()
            : hasFieldErrors
                ? GENERIC_REGISTRATION_ERROR
                : "We couldn't validate this step right now. Please try again."

        return {
            valid: false,
            message,
        }
    }, [applyServerValidationErrors, buildPayload, clearUploadErrors, documentFiles, documentNotes, form])

    const registerThirdParty = useCallback(async (values: RegisterFormInputs): Promise<RegisterThirdPartyResult> => {
        lastRegisterResponseRef.current = null
        clearUploadErrors()

        if (!validateSupplierDocuments()) {
            throw new Error(GENERIC_REGISTRATION_ERROR)
        }

        const payload = buildPayload(values)
        const hasFiles = Boolean(logoFile) || Object.values(documentFiles).some((file) => file instanceof File)
        const requestBody = hasFiles ? new FormData() : payload

        if (requestBody instanceof FormData) {
            Object.entries(payload).forEach(([key, value]) => {
                if (Array.isArray(value)) {
                    value.forEach((entry) => requestBody.append(`${key}[]`, String(entry)))
                    return
                }

                requestBody.append(key, String(value))
            })

            if (logoFile) {
                requestBody.append("logo", logoFile)
            }

            Object.entries(documentFiles).forEach(([key, file]) => {
                if (!(file instanceof File)) return
                requestBody.append(`registration_documents[${key}]`, file)
            })

            Object.entries(documentNotes).forEach(([key, note]) => {
                const normalizedNote = normalizeText(note)
                if (!normalizedNote) return
                requestBody.append(`registration_document_notes[${key}]`, normalizedNote)
            })
        }

        const response = await fetch("/api/register", {
            method: "POST",
            headers: requestBody instanceof FormData ? { Accept: "application/json" } : { "Content-Type": "application/json", Accept: "application/json" },
            body: requestBody instanceof FormData ? requestBody : JSON.stringify(requestBody),
        })

        const result = await response.json().catch(() => null)
        if (!response.ok) {
            const hasFieldErrors = applyServerValidationErrors(result?.errors)

            throw new Error(hasFieldErrors ? GENERIC_REGISTRATION_ERROR : "We couldn't submit your registration right now. Please try again.")
        }

        const verifyLink = extractVerifyEmailUrl(result)
        lastVerifyEmailUrlRef.current = verifyLink
        setVerifyEmailUrl(verifyLink)

        const responsePayload: RegisterThirdPartyResult = {
            success: true,
            payload: typeof result === "object" && result !== null ? result : { message: String(result) },
            verifyEmailUrl: verifyLink,
        }

        lastRegisterResponseRef.current = responsePayload
        return responsePayload
    }, [applyServerValidationErrors, buildPayload, clearUploadErrors, documentFiles, documentNotes, logoFile, validateSupplierDocuments])

    return {
        form,
        errors: form.formState.errors,
        metadata,
        isLoadingMetadata,
        isLoadingLocalities,
        metadataError,
        registerThirdParty,
        verifyEmailUrl,
        resetVerifyEmailUrl,
        getLastVerifyEmailUrl: useCallback(() => lastVerifyEmailUrlRef.current, []),
        getLastRegisterResponse: useCallback(() => lastRegisterResponseRef.current, []),
        isSubmitting: form.formState.isSubmitting,
        toggleType: (type: RoleValue) => {
            const current = form.getValues("types") || []
            const updated = current.includes(type) ? current.filter((entry) => entry !== type) : [...current, type]
            form.setValue("types", updated, { shouldDirty: true, shouldValidate: true })
        },
        selectedTypes,
        isSupplier: selectedTypes?.includes("SU"),
        isTenant: selectedTypes?.includes("TN"),
        isCustomer: selectedTypes?.includes("CU"),
        logoFile,
        setLogoFile: setLogoUpload,
        logoError,
        documentFiles,
        documentNotes,
        documentErrors,
        setRegistrationDocumentFile,
        setRegistrationDocumentNote,
        validateSupplierDocuments,
        validateRegistrationStep,
    }
}
