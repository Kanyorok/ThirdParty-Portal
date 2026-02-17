import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

interface UpdateBidRequest {
  bidId: number
  bidAmount?: number
  validityPeriod?: number
  deliveryPeriod?: number
  paymentTerms?: string
}

const EXTERNAL_API_URL = process.env.EXTERNAL_API_URL

async function getAuthSession() {
  const session = await getServerSession(authOptions)
  if (!session?.user) return null
  return session
}

function isInvalidTenderIdError(payload: any) {
  const errors = payload?.errors
  if (!errors || typeof errors !== "object") return false
  const tenderErrors = Array.isArray((errors as Record<string, unknown>).tender_id)
    ? ((errors as Record<string, unknown>).tender_id as string[])
    : []
  if (!tenderErrors.length) return false
  return tenderErrors.some((message) =>
    String(message).toLowerCase().includes("invalid") ||
    String(message).toLowerCase().includes("required")
  )
}

function hasBidDocumentValidationError(payload: any) {
  const errors = payload?.errors
  if (!errors || typeof errors !== "object") return false
  const keys = Object.keys(errors as Record<string, unknown>)
  return keys.some((key) => key === "bid_documents" || key.startsWith("bid_documents."))
}

async function resolveTenderIdByNo(
  accessToken: string,
  tenderNo: string,
  thirdPartyId?: string | number | null
) {
  if (!EXTERNAL_API_URL || !tenderNo) return null

  const params = new URLSearchParams()
  params.set("search", tenderNo)
  if (thirdPartyId) params.set("third_party_id", String(thirdPartyId))

  const response = await fetch(`${EXTERNAL_API_URL}/api/tenders?${params.toString()}`, {
    headers: {
      Authorization: `Bearer ${accessToken}`,
      Accept: "application/json",
    },
  })
  if (!response.ok) return null

  const json = await response.json().catch(() => null)
  const list = Array.isArray(json?.data) ? json.data : Array.isArray(json) ? json : []
  const normalizedTenderNo = String(tenderNo).trim().toLowerCase()
  const match = list.find((item: any) => {
    const no = String(item?.tenderNo || item?.TenderNo || item?.tender_no || "").trim().toLowerCase()
    return no === normalizedTenderNo
  })
  const resolvedId = match?.id ?? match?.Id ?? null
  return resolvedId ? String(resolvedId) : null
}

export async function GET(request: NextRequest) {
  try {
    const session = await getAuthSession()
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

    const searchParams = request.nextUrl.searchParams
    const tenderId = searchParams.get('tenderId')
    const tenderNo = searchParams.get('tenderNo')
    const checkExisting = searchParams.get('checkExisting')
    const thirdPartyId = (session.user as any)?.thirdPartyId ?? (session.user as any)?.third_party_id ?? null
    const supplierId = (session.user as any)?.supplierId ?? (session.user as any)?.supplier_id ?? null
    const upstreamParams = new URLSearchParams()
    if (thirdPartyId) upstreamParams.set("third_party_id", String(thirdPartyId))
    if (supplierId) upstreamParams.set("supplier_id", String(supplierId))
    const requestUrl = `${EXTERNAL_API_URL}/api/v1/supplier/bid-submissions${upstreamParams.toString() ? `?${upstreamParams.toString()}` : ""}`

    const response = await fetch(requestUrl, {
      headers: {
        'Authorization': `Bearer ${session.accessToken}`,
        'Accept': 'application/json',
      },
    })

    const data = await response.json().catch(() => null)
    if (!response.ok) {
      return NextResponse.json(
        {
          error: data?.error || "Fetch failed",
          message: data?.message || `Status: ${response.status}`,
          errors: data?.errors,
        },
        { status: response.status }
      )
    }

    const list = Array.isArray(data.data) ? data.data : []
    const normalizedTenderNo = String(tenderNo || "").trim().toLowerCase()
    const filtered = list.filter((bid: any) => {
      const byId = tenderId ? String(bid?.tender_id) === String(tenderId) : false
      const bidTenderNo = String(bid?.tender_no || bid?.tender_ref || "").trim().toLowerCase()
      const byNo = normalizedTenderNo ? bidTenderNo === normalizedTenderNo : false
      if (tenderId && normalizedTenderNo) return byId || byNo
      if (tenderId) return byId
      if (normalizedTenderNo) return byNo
      return true
    })

    const sortedFiltered = [...filtered].sort((a: any, b: any) => {
      const left = new Date(a?.submitted_at || a?.received_at || 0).getTime()
      const right = new Date(b?.submitted_at || b?.received_at || 0).getTime()
      return right - left
    })

    if (checkExisting === 'true' && (tenderId || tenderNo)) {
      const existing = sortedFiltered[0] ?? null
      return NextResponse.json({
        success: true,
        hasExistingBid: !!existing,
        existingBid: existing,
        message: data.message
      })
    }

    return NextResponse.json({
      success: data.success ?? true,
      message: data.message,
      data: sortedFiltered,
      total: typeof data.total === "number" && !tenderId && !tenderNo ? data.total : sortedFiltered.length,
      supplier_name: data.supplier_name
    })
  } catch (error) {
    return NextResponse.json(
      { error: "Fetch failed", message: error instanceof Error ? error.message : "Unknown error" },
      { status: 500 }
    )
  }
}

export async function POST(request: NextRequest) {
  try {
    const session = await getAuthSession()
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

    const formData = await request.formData()
    const tenderId = formData.get('tenderId') as string
    const tenderNo = (formData.get('tenderNo') as string) || ""
    const bidAmount = parseFloat(formData.get('bidAmount') as string)
    const currency = String(formData.get('currency') || "").trim().toUpperCase().slice(0, 3)
    const validityPeriod = parseInt(formData.get('validityPeriod') as string)
    const deliveryPeriod = parseInt(formData.get('deliveryPeriod') as string)
    const status = (formData.get('status') as string) || 'draft'
    const requestedStatus = status === "submitted" ? "submitted" : "draft"

    if (!tenderId || isNaN(bidAmount) || !currency || isNaN(validityPeriod) || isNaN(deliveryPeriod)) {
      return NextResponse.json({ error: "Required fields missing or invalid" }, { status: 400 })
    }

    const files = formData.getAll('documents') as File[]
    const thirdPartyId = (session.user as any)?.thirdPartyId ?? (session.user as any)?.third_party_id ?? null
    const supplierId = (session.user as any)?.supplierId ?? (session.user as any)?.supplier_id ?? null
    const paymentTerms = (formData.get('paymentTerms') as string) || ''

    const buildPayload = (resolvedTenderId: string, currentStatus: "draft" | "submitted") => {
      const payload = new FormData()
      payload.append('tender_id', resolvedTenderId)
      payload.append('bid_amount', bidAmount.toString())
      payload.append('currency', currency)
      payload.append('validity_period', validityPeriod.toString())
      payload.append('delivery_period', deliveryPeriod.toString())
      payload.append('payment_terms', paymentTerms)
      payload.append('status', currentStatus)
      if (thirdPartyId) payload.append("third_party_id", String(thirdPartyId))
      if (supplierId) payload.append("supplier_id", String(supplierId))
      files.forEach((file) => {
        payload.append('bid_documents[]', file)
      })
      return payload
    }

    const postToApi = (payload: FormData) =>
      fetch(`${EXTERNAL_API_URL}/api/v1/supplier/bid-submissions`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${session.accessToken}`,
          Accept: "application/json",
        },
        body: payload,
        signal: AbortSignal.timeout(30000),
      })

    if (EXTERNAL_API_URL) {
      let resolvedTenderId = tenderId
      let effectiveStatus: "draft" | "submitted" = requestedStatus
      let response = await postToApi(buildPayload(resolvedTenderId, effectiveStatus))
      let result = await response.json().catch(() => null)

      if (!response.ok && response.status === 422 && isInvalidTenderIdError(result) && tenderNo) {
        const fallbackTenderId = await resolveTenderIdByNo(session.accessToken as string, tenderNo, thirdPartyId)
        if (fallbackTenderId && fallbackTenderId !== resolvedTenderId) {
          resolvedTenderId = fallbackTenderId
          response = await postToApi(buildPayload(resolvedTenderId, effectiveStatus))
          result = await response.json().catch(() => null)
        }
      }

      // Backend may still require at least one document for final submit.
      // Gracefully save as draft when no files were provided.
      if (
        !response.ok &&
        response.status === 422 &&
        effectiveStatus === "submitted" &&
        files.length === 0 &&
        hasBidDocumentValidationError(result)
      ) {
        effectiveStatus = "draft"
        response = await postToApi(buildPayload(resolvedTenderId, effectiveStatus))
        result = await response.json().catch(() => null)
      }

      if (response.ok) {
        const responseStatus = String(result?.data?.status || result?.data?.bid_status || effectiveStatus || "").toLowerCase()
        const fallbackToDraft = requestedStatus === "submitted" && responseStatus === "draft"
        return NextResponse.json({
          success: result?.success ?? true,
          message: result?.message || "Success",
          data: result?.data ?? result,
          requested_status: requestedStatus,
          effective_status: responseStatus || effectiveStatus,
          fallback_to_draft: fallbackToDraft,
        })
      }

      return NextResponse.json({
        error: result?.error || "ERP Error",
        message: result?.message || "Request failed",
        errors: result?.errors,
        invitation_status: result?.invitation_status,
        tender_status: result?.tender_status,
        submission_deadline: result?.submission_deadline,
        data: result?.data,
      }, { status: response.status })
    }

    return NextResponse.json({ error: "API not configured" }, { status: 502 })
  } catch (error) {
    return NextResponse.json(
      { error: "Process failed", message: error instanceof Error ? error.message : "Unknown error" },
      { status: 500 }
    )
  }
}

export async function PUT(request: NextRequest) {
  try {
    const session = await getAuthSession()
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

    const body: UpdateBidRequest = await request.json()
    const { bidId, ...updateData } = body

    if (!bidId || !EXTERNAL_API_URL) {
      return NextResponse.json({ error: "Missing ID or Configuration" }, { status: 400 })
    }

    const response = await fetch(`${EXTERNAL_API_URL}/api/tender-bids/${bidId}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${session.accessToken}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        ...updateData,
        modifiedBy: session.user.id,
        modifiedOn: new Date().toISOString(),
      }),
    })

    const result = await response.json()
    if (!response.ok) throw new Error(result.message || "Update failed")

    return NextResponse.json({ message: "Updated", data: result })
  } catch (error) {
    return NextResponse.json(
      { error: "Update failed", message: error instanceof Error ? error.message : "Unknown error" },
      { status: 500 }
    )
  }
}
