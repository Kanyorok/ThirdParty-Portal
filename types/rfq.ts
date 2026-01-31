export type RfqStatus =
    | "DRAFT"
    | "OPEN"
    | "SUBMITTED"
    | "PARTIAL"
    | "CLOSED"
    | "CANCELLED"
    | "APPROVED"
    | "REJECTED";

export type ActorRole = "SUPPLIER" | "ADMIN";

export type CurrencyCode = string;

export interface RfqId {
    readonly value: string;
}

export interface SupplierId {
    readonly value: string | number;
}

export interface RfqInvitationSummary {
    id: RfqId["value"];
    referenceNumber: string;
    title: string;
    closingDate: string;
    status: RfqStatus;
}

export interface RfqDocumentAttachment {
    id: string;
    fileName: string;
    mimeType: string;
    url: string;
}

export interface RfqHeader {
    id: RfqId["value"];
    referenceNumber: string;
    title: string;
    buyerName?: string;
    description?: string | null;
    closingDate: string;
    status: RfqStatus;
    currency: CurrencyCode;
    attachments: RfqDocumentAttachment[];
}

export interface RfqLineItem {
    id: string;
    lineNumber: number;
    description: string;
    quantity: number;
    unitOfMeasure: string;
    specification?: string | null;
}

export interface SupplierLineResponseInput {
    lineItemId: string;
    unitPrice?: number | null;
    totalPrice?: number | null;
    leadTimeDays?: number | null;
    comments?: string | null;
}

export interface SupplierRfqResponsePayload {
    rfqId: RfqId["value"];
    supplierId: SupplierId["value"];
    status: "DRAFT" | "SUBMITTED" | "PARTIAL";
    lines: SupplierLineResponseInput[];
}

export interface SupplierRfqResponseResult {
    success: boolean;
    responseId?: string;
    message?: string;
}

export interface RfqClarificationThread {
    id: string;
    rfqId: RfqId["value"];
    lineItemId?: string | null;
    subject: string;
    createdBy: ActorRole;
    createdOn: string;
    messages: RfqClarificationMessage[];
}

export interface RfqClarificationMessage {
    id: string;
    threadId: string;
    sender: ActorRole;
    body: string;
    createdOn: string;
}

export interface CreateClarificationPayload {
    rfqId: RfqId["value"];
    lineItemId?: string | null;
    subject: string;
    message: string;
}
