import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "../auth/[...nextauth]/route";

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

// Mock data for development/testing purposes
const mockTenders: Tender[] = [
  {
    id: 1,
    tenderNo: "TENDER/2024/001",
    title: "Supply and Installation of Office Equipment",
    tenderType: "op", // open
    tenderCategory: "Goods",
    scopeOfWork: "Supply and installation of modern office equipment including computers, printers, and networking hardware for the new headquarters building.",
    instructions: "All bidders must be prequalified and provide valid tax compliance certificates. Site visit is mandatory before bid submission.",
    submissionDeadline: "2024-12-01T23:59:00.000Z",
    openingDate: "2024-12-02T10:00:00.000Z",
    status: "pb", // published
    procurementModeId: 1,
    estimatedValue: "2500000",
    itemCategoryId: 1,
    currencyId: "KES",
    createdBy: "admin",
    createdOn: "2024-11-01T08:00:00.000Z",
    modifiedBy: null,
    modifiedOn: "2024-11-01T08:00:00.000Z",
    deletedBy: null,
    deletedOn: null,
    relatedPRID: null,
    approvalRemarks: null,
    approvalStatus: 1,
    procurementMode: {
      id: 1,
      name: "Open Tender"
    },
    currency: {
      id: 1,
      name: "Kenyan Shilling",
      code: "KES",
      symbol: "KSh",
      symbolNative: "KSh",
      decimalDigits: 2,
      rounding: 0,
      createdOn: "2024-01-01T00:00:00.000Z",
      modifiedOn: "2024-01-01T00:00:00.000Z",
      deletedOn: null
    },
    tenderCategoryRelation: {
      id: 1,
      categoryCode: "GDS001",
      tenderCategory: "Office Equipment",
      description: "Office furniture, computers, and related equipment",
      createdBy: "admin",
      createdOn: "2024-01-01T00:00:00.000Z",
      modifiedBy: null,
      modifiedOn: "2024-01-01T00:00:00.000Z",
      deletedBy: null,
      deletedOn: null
    },
    itemCategoryRelation: {
      id: 1,
      name: "Information Technology",
      description: "Computers, software, and IT equipment",
      parentId: null,
      createdBy: 1,
      createdOn: "2024-01-01T00:00:00.000Z",
      modifiedBy: null,
      modifiedOn: "2024-01-01T00:00:00.000Z",
      deletedBy: null,
      deletedOn: null,
      categoryCode: "IT001",
      status: "active"
    }
  },
  {
    id: 2,
    tenderNo: "TENDER/2024/002",
    title: "Construction of Drainage System",
    tenderType: "rs", // restricted
    tenderCategory: "Works",
    scopeOfWork: "Design and construction of a comprehensive drainage system for the industrial area, including storm water management and waste water treatment facilities.",
    instructions: "Only prequalified contractors with category NCA 1 certification are eligible to bid. Environmental impact assessment report required.",
    submissionDeadline: "2024-11-30T17:00:00.000Z",
    openingDate: "2024-12-01T14:00:00.000Z",
    status: "pb", // published
    procurementModeId: 2,
    estimatedValue: "15000000",
    itemCategoryId: 2,
    currencyId: "KES",
    createdBy: "admin",
    createdOn: "2024-10-15T08:00:00.000Z",
    modifiedBy: null,
    modifiedOn: "2024-10-15T08:00:00.000Z",
    deletedBy: null,
    deletedOn: null,
    relatedPRID: null,
    approvalRemarks: null,
    approvalStatus: 1,
    procurementMode: {
      id: 2,
      name: "Restricted Tender"
    },
    currency: {
      id: 1,
      name: "Kenyan Shilling",
      code: "KES",
      symbol: "KSh",
      symbolNative: "KSh",
      decimalDigits: 2,
      rounding: 0,
      createdOn: "2024-01-01T00:00:00.000Z",
      modifiedOn: "2024-01-01T00:00:00.000Z",
      deletedOn: null
    },
    tenderCategoryRelation: {
      id: 2,
      categoryCode: "WKS001",
      tenderCategory: "Construction Works",
      description: "Building and infrastructure construction projects",
      createdBy: "admin",
      createdOn: "2024-01-01T00:00:00.000Z",
      modifiedBy: null,
      modifiedOn: "2024-01-01T00:00:00.000Z",
      deletedBy: null,
      deletedOn: null
    },
    itemCategoryRelation: {
      id: 2,
      name: "Construction and Infrastructure",
      description: "Building, roads, and infrastructure projects",
      parentId: null,
      createdBy: 1,
      createdOn: "2024-01-01T00:00:00.000Z",
      modifiedBy: null,
      modifiedOn: "2024-01-01T00:00:00.000Z",
      deletedBy: null,
      deletedOn: null,
      categoryCode: "CON001",
      status: "active"
    }
  },
  {
    id: 3,
    tenderNo: "TENDER/2024/003",
    title: "Supply of Medical Equipment",
    tenderType: "op", // open
    tenderCategory: "Goods",
    scopeOfWork: "Procurement of modern medical equipment for the county hospital including X-ray machines, ultrasound equipment, and laboratory instruments.",
    instructions: "Suppliers must have valid medical equipment import licenses and provide manufacturer warranties. All equipment must be WHO approved.",
    submissionDeadline: "2024-12-15T16:00:00.000Z",
    openingDate: "2024-12-16T09:00:00.000Z",
    status: "pb", // published
    procurementModeId: 1,
    estimatedValue: "8500000",
    itemCategoryId: 3,
    currencyId: "KES",
    createdBy: "admin",
    createdOn: "2024-11-10T08:00:00.000Z",
    modifiedBy: null,
    modifiedOn: "2024-11-10T08:00:00.000Z",
    deletedBy: null,
    deletedOn: null,
    relatedPRID: null,
    approvalRemarks: null,
    approvalStatus: 1,
    procurementMode: {
      id: 1,
      name: "Open Tender"
    },
    currency: {
      id: 1,
      name: "Kenyan Shilling",
      code: "KES",
      symbol: "KSh",
      symbolNative: "KSh",
      decimalDigits: 2,
      rounding: 0,
      createdOn: "2024-01-01T00:00:00.000Z",
      modifiedOn: "2024-01-01T00:00:00.000Z",
      deletedOn: null
    },
    tenderCategoryRelation: {
      id: 3,
      categoryCode: "MED001",
      tenderCategory: "Medical Equipment",
      description: "Medical devices, equipment, and healthcare supplies",
      createdBy: "admin",
      createdOn: "2024-01-01T00:00:00.000Z",
      modifiedBy: null,
      modifiedOn: "2024-01-01T00:00:00.000Z",
      deletedBy: null,
      deletedOn: null
    },
    itemCategoryRelation: {
      id: 3,
      name: "Healthcare and Medical",
      description: "Medical equipment, supplies, and healthcare services",
      parentId: null,
      createdBy: 1,
      createdOn: "2024-01-01T00:00:00.000Z",
      modifiedBy: null,
      modifiedOn: "2024-01-01T00:00:00.000Z",
      deletedBy: null,
      deletedOn: null,
      categoryCode: "MED001",
      status: "active"
    }
  }
];

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

    // Try to fetch from external API first
    const externalApiUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;
    
    if (externalApiUrl) {
      try {
        const apiUrl = new URL(`${externalApiUrl}/api/tenders`);
        
        // Pass through all search parameters
        searchParams.forEach((value, key) => {
          apiUrl.searchParams.append(key, value);
        });

        const response = await fetch(apiUrl.toString(), {
          headers: {
            'Authorization': `Bearer ${session.accessToken}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          // Add a timeout
          signal: AbortSignal.timeout(10000) // 10 seconds timeout
        });

        if (response.ok) {
          const data = await response.json();
          return NextResponse.json(data);
        }
      } catch (error) {
        console.warn('External API not available, falling back to mock data:', error);
      }
    }

    // Fallback to mock data if external API is not available
    let filteredTenders = [...mockTenders];

    // Apply search filter
    if (search) {
      filteredTenders = filteredTenders.filter(tender => 
        tender.title.toLowerCase().includes(search.toLowerCase()) ||
        tender.tenderNo.toLowerCase().includes(search.toLowerCase()) ||
        tender.scopeOfWork.toLowerCase().includes(search.toLowerCase())
      );
    }

    // Apply status filter
    if (status && status !== 'all') {
      filteredTenders = filteredTenders.filter(tender => tender.status === status);
    }

    // Apply tender type filter
    if (tenderType && tenderType !== 'all') {
      filteredTenders = filteredTenders.filter(tender => tender.tenderType === tenderType);
    }

    // Return paginated results
    const page = parseInt(searchParams.get('page') || '1');
    const limit = parseInt(searchParams.get('limit') || '10');
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + limit;
    
    const paginatedTenders = filteredTenders.slice(startIndex, endIndex);

    return NextResponse.json({
      data: paginatedTenders,
      total: filteredTenders.length,
      page,
      limit,
      pages: Math.ceil(filteredTenders.length / limit),
      fallback: true // Indicates this is mock data
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
