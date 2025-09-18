import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "../auth/[...nextauth]/route";

// Types for Tender Bids
interface TenderBid {
  id: number;
  tenderId: string;
  supplierId: number;
  bidAmount: number;
  currency: string;
  bidDocuments: EncryptedDocument[];
  technicalProposal?: EncryptedDocument;
  financialProposal?: EncryptedDocument;
  complianceDocuments: EncryptedDocument[];
  submissionDate: string;
  status: 'draft' | 'submitted' | 'evaluated' | 'awarded' | 'rejected';
  evaluationScore?: number;
  evaluationNotes?: string;
  bidBond?: EncryptedDocument;
  validityPeriod: number; // days
  deliveryPeriod: number; // days
  paymentTerms?: string;
  createdBy: string;
  createdOn: string;
  modifiedBy?: string;
  modifiedOn?: string;
}

interface EncryptedDocument {
  id: string;
  originalFileName: string;
  encryptedFileName: string;
  fileSize: number;
  mimeType: string;
  documentType: 'technical' | 'financial' | 'compliance' | 'bond' | 'other';
  encryptionKeyId: string; // Reference to encryption key for tender opening
  uploadDate: string;
  checksum: string; // For integrity verification
}

interface CreateBidRequest {
  tenderId: string;
  bidAmount: number;
  currency: string;
  validityPeriod: number;
  deliveryPeriod: number;
  paymentTerms?: string;
  documents: {
    file: File;
    documentType: string;
  }[];
}

interface UpdateBidRequest {
  bidId: number;
  bidAmount?: number;
  validityPeriod?: number;
  deliveryPeriod?: number;
  paymentTerms?: string;
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

    const searchParams = request.nextUrl.searchParams;
    const tenderId = searchParams.get('tenderId');
    const status = searchParams.get('status') || 'all';
    
    const thirdPartyId = session.user.thirdPartyId;
    
    if (!thirdPartyId) {
      return NextResponse.json(
        { error: "Third Party ID not found" },
        { status: 400 }
      );
    }

    // Build query parameters for external API
    const queryParams = new URLSearchParams({
      third_party_id: thirdPartyId.toString(), // Backend will resolve to supplier_id
    });

    if (tenderId) {
      queryParams.append('tender_id', tenderId);
    }

    if (status !== 'all') {
      queryParams.append('status', status);
    }

    // Fetch from external API
    const apiUrl = `${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/tender-bids?${queryParams}`;
    
    const response = await fetch(apiUrl, {
      headers: {
        'Authorization': `Bearer ${session.accessToken}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error(`API responded with status: ${response.status}`);
    }

    const data: {
      data: TenderBid[];
      total: number;
    } = await response.json();

    return NextResponse.json({
      data: data.data,
      total: data.total,
    });

  } catch (error) {
    console.error('Failed to fetch tender bids:', error);
    return NextResponse.json(
      { 
        error: "Failed to fetch tender bids",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: 500 }
    );
  }
}

export async function POST(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);
    
    if (!session?.user) {
      return NextResponse.json(
        { error: "Unauthorized" },
        { status: 401 }
      );
    }

    // Handle multipart form data for file uploads
    const formData = await request.formData();
    
    const tenderId = formData.get('tenderId') as string;
    const bidAmount = parseFloat(formData.get('bidAmount') as string);
    const currency = formData.get('currency') as string;
    const validityPeriod = parseInt(formData.get('validityPeriod') as string);
    const deliveryPeriod = parseInt(formData.get('deliveryPeriod') as string);
    const paymentTerms = formData.get('paymentTerms') as string;

    // Validate required fields
    if (!tenderId || isNaN(bidAmount) || !currency || isNaN(validityPeriod) || isNaN(deliveryPeriod)) {
      return NextResponse.json(
        { error: "Tender ID, bid amount, currency, validity period, and delivery period are required" },
        { status: 400 }
      );
    }

    const thirdPartyId = session.user.thirdPartyId;
    
    if (!thirdPartyId) {
      return NextResponse.json(
        { error: "Third Party ID not found" },
        { status: 400 }
      );
    }

    // Process uploaded files
    const files = formData.getAll('documents') as File[];
    const documentTypes = formData.getAll('documentTypes') as string[];

    if (files.length === 0) {
      return NextResponse.json(
        { error: "At least one document is required for bid submission" },
        { status: 400 }
      );
    }

    // Create FormData for external API with files
    const apiFormData = new FormData();
    apiFormData.append('tenderId', tenderId);
    apiFormData.append('thirdPartyId', thirdPartyId.toString()); // Backend will resolve to supplierId
    apiFormData.append('bidAmount', bidAmount.toString());
    apiFormData.append('currency', currency);
    apiFormData.append('validityPeriod', validityPeriod.toString());
    apiFormData.append('deliveryPeriod', deliveryPeriod.toString());
    apiFormData.append('paymentTerms', paymentTerms || '');
    apiFormData.append('status', 'draft'); // Default to draft until submitted
    apiFormData.append('createdBy', session.user.id);
    apiFormData.append('createdOn', new Date().toISOString());

    // Add files to form data
    files.forEach((file, index) => {
      apiFormData.append('documents', file);
      apiFormData.append('documentTypes', documentTypes[index] || 'other');
    });

    // Send to external API with encryption handling
    const apiUrl = `${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/tender-bids`;
    
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${session.accessToken}`,
      },
      body: apiFormData,
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || `API responded with status: ${response.status}`);
    }

    const newBid = await response.json();

    return NextResponse.json({
      message: "Tender bid created successfully",
      data: newBid,
    });

  } catch (error) {
    console.error('Failed to create tender bid:', error);
    return NextResponse.json(
      { 
        error: "Failed to create tender bid",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: 500 }
    );
  }
}

export async function PUT(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);
    
    if (!session?.user) {
      return NextResponse.json(
        { error: "Unauthorized" },
        { status: 401 }
      );
    }

    const body: UpdateBidRequest = await request.json();
    const { bidId, ...updateData } = body;

    if (!bidId) {
      return NextResponse.json(
        { error: "Bid ID is required" },
        { status: 400 }
      );
    }

    // Prepare payload for external API
    const updatePayload = {
      ...updateData,
      modifiedBy: session.user.id,
      modifiedOn: new Date().toISOString(),
    };

    // Send to external API
    const apiUrl = `${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/tender-bids/${bidId}`;
    
    const response = await fetch(apiUrl, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${session.accessToken}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(updatePayload),
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || `API responded with status: ${response.status}`);
    }

    const updatedBid = await response.json();

    return NextResponse.json({
      message: "Tender bid updated successfully",
      data: updatedBid,
    });

  } catch (error) {
    console.error('Failed to update tender bid:', error);
    return NextResponse.json(
      { 
        error: "Failed to update tender bid",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: 500 }
    );
  }
}
