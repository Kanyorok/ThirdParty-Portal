export interface SupplierPurchaseOrderTotals {
    discount: number
    excludingTax: number
    tax: number
    includingTax: number
}

export interface SupplierPurchaseOrderSummary {
    id: string
    orderNumber: string | null
    orderDate: string | null
    deliveryDate: string | null
    rfqReference: string | null
    description: string | null
    status: string
    currency: string
    lineCount: number
    totals: SupplierPurchaseOrderTotals
}

export interface SupplierPurchaseOrderLine {
    id: string
    itemCode: string | null
    itemName: string | null
    description: string | null
    quantity: number
    unitPriceExcludingTax: number
    discountPercentage: number
    discountAmount: number
    taxRate: number
    taxAmount: number
    netAmount: number
    grossAmount: number
    withholdingTaxRate: number
    withholdingTaxAmount: number
}

export interface SupplierPurchaseOrderDetail extends SupplierPurchaseOrderSummary {
    supplierName: string | null
    supplierAddress: string | null
    branchName: string | null
    paymentTerms: string | null
    lines: SupplierPurchaseOrderLine[]
}

export interface SupplierPurchaseOrderListResponse {
    data: SupplierPurchaseOrderSummary[]
    total: number
}

export interface SupplierPurchaseOrderDetailResponse {
    data: SupplierPurchaseOrderDetail
}
