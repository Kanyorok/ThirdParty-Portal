import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

export const dynamic = "force-dynamic"

export async function GET(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions)
    const accessToken = session?.accessToken

    if (!session?.user || !accessToken) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const { searchParams } = request.nextUrl
    const search = searchParams.get('search')
    const status = searchParams.get('status')
    const tenderType = searchParams.get('tenderType') ?? searchParams.get('tender_type')

    try {
      const erpBase = process.env.ERP_BASE_URL || process.env.NEXT_PUBLIC_API_URL
      if (!erpBase) {
        return NextResponse.json({ error: "API not configured" }, { status: 502 })
      }

      const apiUrl = new URL(`${erpBase}/api/v1/supplier/tenders`)
      if (search) apiUrl.searchParams.set('search', search)
      if (status && status !== 'all') apiUrl.searchParams.set('status', status)
      if (tenderType && tenderType !== 'all') {
        apiUrl.searchParams.set('tender_type', tenderType)
      }

      const response = await fetch(apiUrl.toString(), {
        headers: {
          'Accept': 'application/json',
          Authorization: `Bearer ${accessToken}`,
        },
        signal: AbortSignal.timeout(10000)
      })

      if (response.ok) {
        const data = await response.json()
        return NextResponse.json(data)
      } else {
        const errorText = await response.text()
        console.error(`ERP /api/v1/supplier/tenders returned error ${response.status}:`, errorText)

        if (response.status === 401) {
          return NextResponse.json({
            data: [],
            total: 0,
            page: 1,
            limit: 10,
            pages: 0,
            message: "Authentication with ERP failed. Try logging out and in again."
          })
        }
      }
    } catch (e) {
      console.warn('ERP /api/v1/supplier/tenders call failed:', e)
    }

    return NextResponse.json({
      data: [],
      total: 0,
      page: 1,
      limit: 10,
      pages: 0,
      fallback: false
    })

  } catch (error) {
    console.error('Failed to fetch tenders:', error)
    return NextResponse.json(
      {
        error: "Failed to fetch tenders",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: 500 }
    )
  }
}
