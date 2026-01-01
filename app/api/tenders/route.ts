import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

// Types for Tender data (matching the interface from tenders.tsx)
interface Tender {
  id: string;
  tenderNo: string;
  title: string;
  tenderType: string;
  tenderCategory: string;
  scopeOfWork: string;
  instructions: string;
  submissionDeadline: string;
  openingDate: string;
  status: string;
  procurementModeId: number | null;
  estimatedValue?: string | null;
  itemCategoryId: number;
  currencyId: string;
  createdBy: string | null;
  createdOn: string;
  modifiedBy: string | null;
  modifiedOn: string;
  deletedBy: string | null;
  deletedOn: string | null;
  relatedPRID?: number | null;
  approvalRemarks: string | null;
  approvalStatus: number;
  procurementMode?: {
    id: number;
    name: string;
  } | null;
  currency?: {
    id: number;
    name: string;
    code: string;
    symbol: string;
    symbolNative: string;
    decimalDigits: number;
    rounding: number;
    createdOn: string;
    modifiedOn: string;
    deletedOn: string | null;
  } | null;
  tenderCategoryRelation?: {
    id: number;
    categoryCode: string;
    tenderCategory: string;
    description: string;
    createdBy: string | null;
    createdOn: string;
    modifiedBy: string | null;
    modifiedOn: string;
    deletedBy: string | null;
    deletedOn: string | null;
  };
  itemCategoryRelation?: {
    id: number;
    name: string;
    description: string;
    parentId: number | null;
    createdBy: number | null;
    createdOn: string;
    modifiedBy: number | null;
    modifiedOn: string;
    deletedBy: string | null;
    deletedOn: string | null;
    categoryCode: string;
    status: string;
  };
}

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
    } else {
      // Forward backend error
      const errorData = await response.json().catch(() => ({ message: `Backend returned ${response.status}` }));
      console.error('ERP /api/tenders call failed:', response.status, errorData);
      return NextResponse.json(
        { error: "Failed to fetch tenders from backend", details: errorData },
        { status: response.status }
      );
    }

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
