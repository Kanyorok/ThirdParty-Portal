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

export async function GET(request: NextRequest) {
  try {
    const session = await getAuthSession()
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

    const searchParams = request.nextUrl.searchParams
    const tenderId = searchParams.get('tenderId')
    const checkExisting = searchParams.get('checkExisting')
    const status = searchParams.get('status') || 'all'
    const thirdPartyId = session.user.thirdPartyId

    if (!thirdPartyId) {
      return NextResponse.json({ error: "Third Party ID not found" }, { status: 400 })
    }

    if (checkExisting === 'true' && tenderId && EXTERNAL_API_URL) {
      const queryParams = new URLSearchParams({
        tender_id: tenderId,
        third_party_id: thirdPartyId.toString(),
      })

      const response = await fetch(`${EXTERNAL_API_URL}/api/bid-submissions/existing?${queryParams}`, {
        headers: {
          'Authorization': `Bearer ${session.accessToken}`,
          'Accept': 'application/json',
        },
      })

      if (response.ok) {
        const data = await response.json()
        return NextResponse.json({
          success: true,
          hasExistingBid: !!data.data,
          existingBid: data.data,
          message: data.message
        })
      }
      return NextResponse.json({ success: true, hasExistingBid: false, existingBid: null })
    }

    const queryParams = new URLSearchParams({ third_party_id: thirdPartyId.toString() })
    if (tenderId) queryParams.append('tender_id', tenderId)
    if (status !== 'all') queryParams.append('status', status)

    const response = await fetch(`${EXTERNAL_API_URL}/api/tender-bids?${queryParams}`, {
      headers: {
        'Authorization': `Bearer ${session.accessToken}`,
        'Accept': 'application/json',
      },
    })

    if (!response.ok) throw new Error(`Status: ${response.status}`)
    const data = await response.json()

    return NextResponse.json({ data: data.data, total: data.total })
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
    const bidAmount = parseFloat(formData.get('bidAmount') as string)
    const currency = formData.get('currency') as string
    const validityPeriod = parseInt(formData.get('validityPeriod') as string)
    const deliveryPeriod = parseInt(formData.get('deliveryPeriod') as string)
    const status = (formData.get('status') as string) || 'draft'
    const thirdPartyId = session.user.thirdPartyId

    if (!tenderId || isNaN(bidAmount) || !currency || isNaN(validityPeriod) || isNaN(deliveryPeriod)) {
      return NextResponse.json({ error: "Required fields missing or invalid" }, { status: 400 })
    }

    if (!thirdPartyId) return NextResponse.json({ error: "ID not found" }, { status: 400 })

    const files = formData.getAll('documents') as File[]
    const documentTypes = formData.getAll('documentTypes') as string[]

    if (files.length === 0 && status === 'submitted') {
      return NextResponse.json({ error: "Documents required for submission" }, { status: 400 })
    }

    const apiFormData = new FormData()
    apiFormData.append('tender_id', tenderId)
    apiFormData.append('third_party_id', thirdPartyId.toString())
    apiFormData.append('bid_amount', bidAmount.toString())
    apiFormData.append('currency', currency)
    apiFormData.append('validity_period', validityPeriod.toString())
    apiFormData.append('delivery_period', deliveryPeriod.toString())
    apiFormData.append('payment_terms', (formData.get('paymentTerms') as string) || '')
    apiFormData.append('status', status)

    files.forEach((file, index) => {
      apiFormData.append('bid_documents[]', file)
      apiFormData.append(`bid_documents[${index}][document_type]`, documentTypes[index] || 'other')
    })

    if (EXTERNAL_API_URL) {
      const response = await fetch(`${EXTERNAL_API_URL}/api/bid-submissions`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${session.accessToken}` },
        body: apiFormData,
        signal: AbortSignal.timeout(30000),
      })

      const result = await response.json()

      if (response.ok) {
        return NextResponse.json({
          message: "Success",
          data: {
            bid_id: result.data?.Id || result.Id,
            tender_id: result.data?.TenderId || result.TenderId,
            status: result.data?.Status || result.Status,
          },
        })
      }

      return NextResponse.json({
        error: result.error || "ERP Error",
        message: result.message || "Request failed",
        errors: result.errors
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
