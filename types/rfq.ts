/* ── Backend API types for supplier RFQ endpoints ── */

/* ---------- Shared ---------- */

export type RfqStatus = string

/* ---------- Documents ---------- */

export interface RfqDocument {
    id: string
    name: string | null
    mimeType: string | null
    createdOn: string | null
    downloadUrl: string
}

/* ---------- GET /supplier/rfqs  (list) ---------- */
/* ---------- GET /supplier/rfqs/{rfq}  (detail) ---------- */

export interface RfqCategory {
    id: number | null
    name: string | null
}

export interface RfqRequisition {
    id: string
    description: string | null
}

export interface RfqLine {
    id: string
    rfqLineNo: string | null
    itemId: number | null
    itemName: string | null
    quantity: number | null
    uom: string | null
    category: RfqCategory | null
}

export interface RfqSection {
    id: number | null
    name: string | null
    weight: number | null
}

export interface RfqCriterion {
    id: number | null
    sectionId: number | null
    criteriaId: number | null
    name: string | null
    maxScore: number | null
}

export interface RfqDetail {
    id: string
    rfqNumber: string | null
    comments: string | null
    status: string | null
    statusDescription: string | null
    submissionDeadline: string | null
    remarks: string | null
    documents: RfqDocument[]
    requisition: RfqRequisition | null
    rfqLines: RfqLine[]
    sections: RfqSection[]
    criteria: RfqCriterion[]
}

export interface RfqResponseItem {
    id: string
    rfqLineId: string | null
    itemName: string | null
    quantity: number | null
    uom: string | null
    quotedPrice: number | null
    totalPayable: number | null
    taxType?: "VAT" | "Exempt" | "WithholdingTax" | string | null
    taxRate?: number | null
    isTaxInclusive?: boolean | null
    netAmount?: number | null
    taxAmount?: number | null
    grossAmount?: number | null
}

export interface RfqMyResponse {
    id: string
    rfqId: string
    supplierId: string
    rfqResponseNumber: string | null
    currency: string | null
    durationDays: number | null
    status: "DRAFT" | "FINAL" | string
    totalPayable: number | null
    submittedOn: string | null
    canUploadDocuments: boolean
    canDeleteDocuments: boolean
    documents: RfqDocument[]
    items: RfqResponseItem[]
}

export interface RfqSupplierOption {
    supplierId: string
    supplierLabel?: string | null
    invitationStatus: string | null
    invitedOn?: string | null
    invitationUpdatedOn?: string | null
    myResponse: RfqMyResponse | null
}

/** A single item in the `data` array from `GET /supplier/rfqs` or the
 *  top-level object from `GET /supplier/rfqs/{rfq}`. */
export interface RfqInvitation {
    rfqId: string
    rfqNumber: string | null
    comments: string | null
    status: string | null
    submissionDeadline: string | null
    supplierId: string
    invitationStatus: string | null
    invitedOn?: string | null
    invitationUpdatedOn?: string | null
    rfq: RfqDetail | null
    myResponse: RfqMyResponse | null
    supplierOptions?: RfqSupplierOption[]
}

export interface RfqListResponse {
    data: RfqInvitation[]
}

/* ---------- GET /supplier/rfqs/{rfq}/clarifications ---------- */

export interface RfqClarification {
    Id: number
    RFQId: number
    SupplierId: number
    RFQLineId: number | null
    Question: string
    Answer: string | null
    IsPublic?: boolean | number
    CreatedOn: string
}

export interface RfqClarificationsResponse {
    data: RfqClarification[]
}

/* ---------- POST /supplier/rfqs/clarifications ---------- */

export interface CreateClarificationPayload {
    rfqId: number
    supplierId: number
    question: string
    rfqLineId?: number
    isPublic?: boolean
}

/* ---------- POST /supplier/rfqs/responses ---------- */

export interface SubmitResponseLineItem {
    rfqLineId: number
    quotedPrice: number
    totalPayable: number
    taxType: "VAT" | "Exempt"
    taxRate: number
    isTaxInclusive: boolean
}

export interface SubmitRfqResponsePayload {
    rfqId: number
    supplierId: number
    currency: string
    durationDays: number
    isDraft?: boolean
    items: SubmitResponseLineItem[]
}

export interface SubmitRfqResponseResult {
    message: string
    data: {
        rfqResponseId: number
        status: string
    }
}

/* ---------- Document endpoint responses ---------- */

export interface RfqDocumentListResponse {
    data: RfqDocument[]
}

export interface RfqDocumentUploadResponse {
    message: string
    data: RfqDocument
}

export interface RfqDocumentPermissions {
    view: boolean
    upload: boolean
    download: boolean
    delete: boolean
}

export interface PortalDocumentPermissionsResponse {
    success?: boolean
    permissions?: {
        rfq?: Partial<RfqDocumentPermissions>
    }
    defaults?: {
        rfq?: Partial<RfqDocumentPermissions>
    }
}
