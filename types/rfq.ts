export type RfqInvitationStatus = "OPEN" | "CLOSED" | "DRAFT" | "CANCELLED" | "SUBMITTED" | "PARTIAL";

export interface RfqInvitationSummary {
    id: string;
    title: string;
    referenceNumber: string;
    closingDate: string; // ISO string
    status: RfqInvitationStatus;
}

export interface RfqDocumentAttachment {
    id: string;
    fileName: string;
    url: string;
}

export interface RfqHeader {
    id: string;
    title: string;
    referenceNumber: string;
    buyerName?: string;
    closingDate: string; // ISO string
    description?: string;
    attachments?: RfqDocumentAttachment[];
}

export interface RfqLineItem {
    id: string;
    lineNumber?: number;
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
    rfqId: string;
    supplierId: string | number;
    isDraft?: boolean;
    lines: SupplierLineResponseInput[];
}

export interface SupplierRfqResponseResult {
    success: boolean;
    message?: string;
    responseId?: string;
}

export interface RfqClarificationThread {
    id: string;
    rfqId: string;
    lineItemId?: string | null;
    subject: string;
    createdBy: "SUPPLIER" | "ADMIN";
    createdOn: string; // ISO
    messages: RfqClarificationMessage[];
}

export interface RfqClarificationMessage {
    id: string;
    threadId: string;
    sender: "SUPPLIER" | "ADMIN";
    body: string;
    createdOn: string; // ISO
}

export interface CreateClarificationPayload {
    rfqId: string;
    lineItemId?: string | null;
    subject: string;
    message: string;
}


