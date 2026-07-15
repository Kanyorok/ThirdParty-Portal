"use client"

import { useEffect, useMemo, useState } from "react"

import {
    buildSupplierOptions,
    DEFAULT_RFQ_DOCUMENT_PERMISSIONS,
    extractRfqDocumentPermissions,
    extractCurrentUserSupplierLabel,
    mergeSupplierOptionLabels,
    normalizeSupplierId,
    pickPreferredSupplierId,
    pickSupplierOption,
} from "@/lib/rfq-response"
import type { RfqDocumentPermissions, RfqInvitation, RfqSupplierOption } from "@/types/rfq"

type UseRfqPortalContextOptions = {
    rfqId: string
    initialInvitation?: RfqInvitation | null
    preferredSupplierId?: string | null
    enabled?: boolean
    refreshKey?: unknown
}

export function useRfqPortalContext({
    rfqId,
    initialInvitation = null,
    preferredSupplierId,
    enabled = true,
    refreshKey,
}: UseRfqPortalContextOptions) {
    const fallbackOptions = useMemo(
        () => buildSupplierOptions([], initialInvitation),
        [initialInvitation]
    )

    const [supplierOptions, setSupplierOptions] = useState<RfqSupplierOption[]>(fallbackOptions)
    const [supplierOptionsLoading, setSupplierOptionsLoading] = useState(false)
    const [supplierLabels, setSupplierLabels] = useState<Record<string, string>>({})
    const [permissions, setPermissions] = useState<RfqDocumentPermissions>(DEFAULT_RFQ_DOCUMENT_PERMISSIONS)
    const [permissionsLoading, setPermissionsLoading] = useState(false)
    const [selectedSupplierId, setSelectedSupplierId] = useState<string>(
        () => pickPreferredSupplierId(fallbackOptions, preferredSupplierId ?? normalizeSupplierId(initialInvitation?.supplierId)) ?? ""
    )

    useEffect(() => {
        setSupplierOptions(fallbackOptions)
    }, [fallbackOptions])

    useEffect(() => {
        if (!enabled || !rfqId) return

        let cancelled = false

        const loadSupplierOptions = async () => {
            setSupplierOptionsLoading(true)
            try {
                const res = await fetch("/api/procurement/rfqs/invitations", {
                    headers: { Accept: "application/json" },
                    cache: "no-store",
                })
                const json = await res.json().catch(() => ({}))
                if (!res.ok) {
                    throw new Error(json?.message ?? json?.error ?? `Failed to load RFQ invitations (HTTP ${res.status})`)
                }

                const list = Array.isArray(json?.data) ? (json.data as RfqInvitation[]) : []
                const filtered = list.filter((invitation) => String(invitation.rfqId).trim() === String(rfqId).trim())
                const nextOptions = buildSupplierOptions(filtered, initialInvitation)
                if (!cancelled) {
                    setSupplierOptions(nextOptions.length > 0 ? nextOptions : fallbackOptions)
                }
            } catch {
                if (!cancelled) {
                    setSupplierOptions(fallbackOptions)
                }
            } finally {
                if (!cancelled) setSupplierOptionsLoading(false)
            }
        }

        loadSupplierOptions()

        return () => {
            cancelled = true
        }
    }, [enabled, fallbackOptions, initialInvitation, refreshKey, rfqId])

    useEffect(() => {
        if (!enabled) return

        let cancelled = false

        const loadCurrentUserLabel = async () => {
            try {
                const res = await fetch("/api/thirdpartyuser", {
                    headers: { Accept: "application/json" },
                    cache: "no-store",
                })
                const json = await res.json().catch(() => ({}))
                if (!res.ok) {
                    throw new Error(json?.message ?? json?.error ?? `Failed to load current user (HTTP ${res.status})`)
                }

                const currentUserLabel = extractCurrentUserSupplierLabel(json)
                if (!cancelled && currentUserLabel) {
                    setSupplierLabels((prev) => ({
                        ...prev,
                        [currentUserLabel.supplierId]: currentUserLabel.supplierLabel,
                    }))
                }
            } catch {
                return
            }
        }

        const loadPermissions = async () => {
            setPermissionsLoading(true)
            try {
                const res = await fetch("/api/procurement/rfq-document-permissions", {
                    headers: { Accept: "application/json" },
                    cache: "no-store",
                })
                const json = await res.json().catch(() => ({}))
                if (!res.ok) {
                    throw new Error(json?.message ?? json?.error ?? `Failed to load RFQ document permissions (HTTP ${res.status})`)
                }

                if (!cancelled) {
                    setPermissions(extractRfqDocumentPermissions(json))
                }
            } catch {
                if (!cancelled) {
                    setPermissions(DEFAULT_RFQ_DOCUMENT_PERMISSIONS)
                }
            } finally {
                if (!cancelled) setPermissionsLoading(false)
            }
        }

        loadCurrentUserLabel()
        loadPermissions()

        return () => {
            cancelled = true
        }
    }, [enabled, refreshKey])

    const mergedSupplierOptions = useMemo(
        () => mergeSupplierOptionLabels(supplierOptions, supplierLabels),
        [supplierLabels, supplierOptions]
    )

    useEffect(() => {
        const nextSupplierId = pickPreferredSupplierId(
            mergedSupplierOptions,
            preferredSupplierId ?? selectedSupplierId ?? normalizeSupplierId(initialInvitation?.supplierId)
        )

        if (nextSupplierId && nextSupplierId !== selectedSupplierId) {
            setSelectedSupplierId(nextSupplierId)
        }
    }, [initialInvitation?.supplierId, mergedSupplierOptions, preferredSupplierId, selectedSupplierId])

    const selectedSupplierOption = useMemo(
        () => pickSupplierOption(mergedSupplierOptions, selectedSupplierId),
        [mergedSupplierOptions, selectedSupplierId]
    )

    return {
        permissions,
        permissionsLoading,
        selectedSupplierId,
        selectedSupplierOption,
        setSelectedSupplierId,
        supplierOptions: mergedSupplierOptions,
        supplierOptionsLoading,
    }
}