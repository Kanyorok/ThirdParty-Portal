// Normalized Tender Portal types for supplier portal

export type TenderDocument = {
    Id: number | null;
    DocumentId: string | null;
    Name: string | null;
    downloadUrl: string | null;
    [key: string]: unknown;
};

export type PortalTender = {
    Id: number;
    TenderNo: string | null;
    Title: string | null;
    TenderType: string | null;
    TenderCategory: string | null;
    ScopeOfWork: string | null;
    Instructions: string | null;
    SubmissionDeadline: string | null;
    OpeningDate: string | null;
    Status: string | null;
    ProcurementModeId: number | null;
    StartDate: string | null;
    CurrencyId: number | null;
    currency_code: string | null;
    ApprovalRemarks: string | null;
    ApprovalStatus: string | null;
    ItemCategoryId: number | null;
    CreatedBy: number | null;
    CreatedOn: string | null;
    ModifiedBy: number | null;
    ModifiedOn: string | null;
    DeletedBy: number | null;
    DeletedOn: string | null;
    procurementMode?: unknown;
    tenderCategoryRelation?: unknown;
    itemCategoryRelation?: unknown;
    documents: TenderDocument[];
    items: unknown[];
};

export type TenderInvitation = {
    InvitationID: number | null;
    TenderId: number | null;
    SupplierId: number | null;
    ResponseStatus: 'Pending' | 'Accepted' | 'Declined' | null;
    ResponseDate: string | null;
    DeclineReason: string | null;
    InvitationDate: string | null;
    tender: {
        Id: number | null;
        TenderNo: string | null;
        Title: string | null;
        TenderType: string | null;
        TenderCategory: string | null;
        SubmissionDeadline: string | null;
        OpeningDate: string | null;
        Status: string | null;
        CurrencyId: number | null;
        currency_code: string | null;
    };
};

export type TenderClarification = {
    clarificationId: number | null;
    tenderId: number | null;
    vendorId: number | null;
    question: string | null;
    questionDate: string | null;
    answer: string | null;
    answerDate: string | null;
    isPublic: boolean;
    isOwnQuestion: boolean | null;
    status: 'pending' | 'answered' | null;
    createdBy: number | null;
    createdOn: string | null;
};

export type SupplierBidSubmission = {
    id: number;
    tender_id: number | null;
    tender_ref: string | null;
    tender_no: string | null;
    tender_title: string | null;
    supplier_id: number | null;
    supplier_name: string | null;
    bid_amount: number | null;
    currency: string | null;
    validity_period: number | null;
    delivery_period: number | null;
    payment_terms: string | null;
    status: string | null;
    bid_status: string | null;
    access_status: string | null;
    submitted_at: string | null;
    received_at: string | null;
    submission_source: string | null;
    documents_count: number;
    can_access_documents: boolean;
    bid_opening_date: string | null;
    remarks: string | null;
    envelope_status: string | null;
    is_complete: number | null;
    submission_reference: string | null;
};
