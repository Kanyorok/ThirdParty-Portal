export interface SupplierContractSummary {
    id: number | null
    reference: string | null
    status: string | null
    stage: string
    stage_label: string
    value: string | number | null
    start_date: string | null
    end_date: string | null
    approved_on: string | null
    payment_terms: string | number | null
    payment_terms_label: string | null
    delivery_terms: string | null
}

export interface SupplierAwardContract {
    award_id: number
    tender_id: number | null
    tender_reference: string | null
    tender_title: string | null
    award_status: string
    award_date: string | null
    awarded_amount: string | number | null
    currency: string
    contract: SupplierContractSummary
}

export interface SupplierContractsResponse {
    success: boolean
    data: SupplierAwardContract[]
    meta?: { total?: number }
    message?: string
}
