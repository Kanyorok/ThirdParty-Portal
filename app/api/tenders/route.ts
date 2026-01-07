import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

// Preferred approach is calling ERP backend directly.
// Returning empty data when ERP is unreachable prevents mock data from appearing in production.

export async function GET(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);
    
    if (!session?.user) {
      return NextResponse.json(
        { error: "Unauthorized" },
        { status: 401 }
      );
    }

    const { searchParams } = request.nextUrl;
    const search = searchParams.get('search');
    const status = searchParams.get('status');
    const tenderType = searchParams.get('tenderType');

    // Prefer calling ERP backend directly; enforce invites with third party context
    try {
      const erpBase = process.env.ERP_BASE_URL || process.env.NEXT_PUBLIC_ERP_BASE_URL || 'http://127.0.0.1:8000';
      const apiUrl = new URL(`${erpBase}/api/tenders`);
      // Forward filters
      if (search) apiUrl.searchParams.set('search', search);
      if (status && status !== 'all') apiUrl.searchParams.set('status', status);
      if (tenderType && tenderType !== 'all') apiUrl.searchParams.set('tenderType', tenderType);
      // Always enforce invitations and pass third party id; backend will include open tenders + invited restricted
      const thirdPartyId = (session.user as any)?.thirdPartyId;
      apiUrl.searchParams.set('enforce_invites', 'true');
      if (thirdPartyId) {
        apiUrl.searchParams.set('third_party_id', String(thirdPartyId));
      }

      const response = await fetch(apiUrl.toString(), {
        headers: {
          'Accept': 'application/json',
          'Authorization': session.accessToken ? `Bearer ${session.accessToken}` : ''
        },
        signal: AbortSignal.timeout(10000)
      });

      if (response.ok) {
        const data = await response.json();
        return NextResponse.json(data);
      }
    } catch (e) {
      console.warn('ERP /api/tenders call failed, falling back to mock:', e);
    }

    // If ERP call failed and we reached here, return empty data
    return NextResponse.json({
      data: [],
      total: 0,
      page: 1,
      limit: 10,
      pages: 0,
      fallback: false
    });

  } catch (error) {
    console.error('Failed to fetch tenders:', error);
    return NextResponse.json(
      { 
        error: "Failed to fetch tenders",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: 500 }
    );
  }
}
