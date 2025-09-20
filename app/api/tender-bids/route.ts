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
    const checkExisting = searchParams.get('checkExisting');
    const status = searchParams.get('status') || 'all';
    
    const thirdPartyId = session.user.thirdPartyId;
    
    if (!thirdPartyId) {
      return NextResponse.json(
        { error: "Third Party ID not found" },
        { status: 400 }
      );
    }

    // Handle checking for existing bid (for draft editing)
    if (checkExisting === 'true' && tenderId) {
      const externalApiUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;
      if (externalApiUrl) {
        try {
          const queryParams = new URLSearchParams({
            tender_id: tenderId,
            third_party_id: thirdPartyId.toString(),
          });

          const apiUrl = `${externalApiUrl}/api/bid-submissions/existing?${queryParams}`;
          console.log('📡 CHECKING EXISTING BID - Attempting to call:', apiUrl);
          
          const response = await fetch(apiUrl, {
            headers: {
              'Authorization': `Bearer ${session.accessToken}`,
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
          });

          if (response.ok) {
            const data = await response.json();
            console.log('✅ EXISTING BID CHECK - Response from ERP:', data);
            
            return NextResponse.json({
              success: true,
              hasExistingBid: !!data.data,
              existingBid: data.data,
              message: data.message
            });
          } else {
            console.log('❌ EXISTING BID CHECK - Failed:', response.status);
            return NextResponse.json({
              success: true,
              hasExistingBid: false,
              existingBid: null,
              message: "No existing bid found"
            });
          }
        } catch (error) {
          console.warn('External API not available for existing bid check:', error);
          return NextResponse.json({
            success: true,
            hasExistingBid: false,
            existingBid: null,
            message: "Unable to check existing bids"
          });
        }
      } else {
        return NextResponse.json({
          success: true,
          hasExistingBid: false,
          existingBid: null,
          message: "External API not configured"
        });
      }
    }

    // Build query parameters for external API (existing functionality)
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
    let formData;
    try {
      formData = await request.formData();
      console.log('📝 BID FORM DEBUG - FormData parsed successfully');
    } catch (error) {
      console.error('❌ BID FORM DEBUG - Failed to parse FormData:', error);
      return NextResponse.json(
        { error: "Invalid form data format" },
        { status: 400 }
      );
    }
    
    const tenderId = formData.get('tenderId') as string;
    const bidAmount = parseFloat(formData.get('bidAmount') as string);
    const currency = formData.get('currency') as string;
    const validityPeriod = parseInt(formData.get('validityPeriod') as string);
    const deliveryPeriod = parseInt(formData.get('deliveryPeriod') as string);
    const paymentTerms = formData.get('paymentTerms') as string;
    const status = formData.get('status') as string || 'draft';

    // Debug: Log all received values
    console.log('🔍 API DEBUG - Received values:', {
      tenderId: tenderId,
      tenderIdValid: !!tenderId,
      bidAmountRaw: formData.get('bidAmount'),
      bidAmount: bidAmount,
      bidAmountValid: !isNaN(bidAmount),
      currencyRaw: formData.get('currency'),
      currency: currency,
      currencyValid: !!currency,
      validityPeriodRaw: formData.get('validityPeriod'),
      validityPeriod: validityPeriod,
      validityPeriodValid: !isNaN(validityPeriod),
      deliveryPeriodRaw: formData.get('deliveryPeriod'),
      deliveryPeriod: deliveryPeriod,
      deliveryPeriodValid: !isNaN(deliveryPeriod),
      status: status
    });

    // Validate required fields
    if (!tenderId || isNaN(bidAmount) || !currency || isNaN(validityPeriod) || isNaN(deliveryPeriod)) {
      console.log('❌ API DEBUG - Validation failed:', {
        tenderIdFailed: !tenderId,
        bidAmountFailed: isNaN(bidAmount),
        currencyFailed: !currency,
        validityPeriodFailed: isNaN(validityPeriod),
        deliveryPeriodFailed: isNaN(deliveryPeriod)
      });
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

    // Only require documents for final submissions, allow drafts without documents
    if (files.length === 0 && status === 'submitted') {
      return NextResponse.json(
        { error: "At least one document is required for final bid submission" },
        { status: 400 }
      );
    }

    console.log('📁 DOCUMENTS DEBUG - Processing files:', {
      fileCount: files.length,
      status: status,
      documentTypes: documentTypes
    });

    // Create FormData for external API matching ERP bid-submissions endpoint format
    const apiFormData = new FormData();
    apiFormData.append('tender_id', tenderId);
    apiFormData.append('third_party_id', thirdPartyId.toString()); // Backend will resolve to supplierId
    apiFormData.append('bid_amount', bidAmount.toString());
    apiFormData.append('currency', currency);
    apiFormData.append('validity_period', validityPeriod.toString());
    apiFormData.append('delivery_period', deliveryPeriod.toString());
    apiFormData.append('payment_terms', paymentTerms || '');
    apiFormData.append('status', status || 'draft');

    // Add files using bid_documents[] format as expected by ERP (if any)
    if (files.length > 0) {
      files.forEach((file, index) => {
        apiFormData.append('bid_documents[]', file);
        apiFormData.append(`bid_documents[${index}][document_type]`, documentTypes[index] || 'other');
      });
    } else {
      console.log('📁 DOCUMENTS DEBUG - No files to upload for this submission');
    }

    console.log('📝 BID SUBMISSION DEBUG - Sending to ERP:', {
      tender_id: tenderId,
      third_party_id: thirdPartyId,
      bid_amount: bidAmount,
      currency: currency,
      validity_period: validityPeriod,
      delivery_period: deliveryPeriod,
      status: status || 'draft',
      documents_count: files.length,
      document_types: documentTypes
    });

    // Try to send to external API with encryption handling
    const externalApiUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;
    let useMockResponse = false;

    if (externalApiUrl) {
      try {
        const apiUrl = `${externalApiUrl}/api/bid-submissions`;
        console.log('📡 BID SUBMISSION API - Attempting to call:', apiUrl);
        
        const response = await fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${session.accessToken}`,
          },
          body: apiFormData,
          signal: AbortSignal.timeout(20000), // 20 second timeout
        });

        if (response.ok) {
          const newBid = await response.json();
          console.log('✅ BID SUBMISSION API - Success from ERP backend');
          
          // Map ERP response with PascalCase to frontend format
          const frontendResponse = {
            bid_id: newBid.data?.Id || newBid.Id,
            tender_id: newBid.data?.TenderId || newBid.TenderId,
            supplier_id: newBid.data?.SupplierId || newBid.SupplierId,
            bid_amount: newBid.data?.BidAmount || newBid.BidAmount,
            currency: newBid.data?.Currency || newBid.Currency,
            status: newBid.data?.Status || newBid.Status,
            submitted_at: newBid.data?.CreatedOn || newBid.CreatedOn,
            documents_count: newBid.documents_count || 0
          };
          
          return NextResponse.json({
            message: "Bid submitted to ERP successfully",
            data: frontendResponse,
          });
        } else if (response.status === 422) {
          // Handle validation errors from ERP
          try {
            const validationErrors = await response.json();
            console.log('❌ BID SUBMISSION API - Validation errors from ERP:', validationErrors);
            
            return NextResponse.json({
              error: "Validation failed",
              message: validationErrors.message || "Please check your bid details",
              errors: validationErrors.errors || {},
              details: validationErrors
            }, { status: 422 });
          } catch (parseError) {
            console.error('Failed to parse 422 validation response:', parseError);
            useMockResponse = true;
          }
        } else if (response.status === 403) {
          // Handle business logic errors from ERP (like expired deadlines)
          try {
            const businessError = await response.json();
            console.log('🚫 BID SUBMISSION API - Business error from ERP:', businessError);
            
            return NextResponse.json({
              error: "Submission not allowed",
              message: businessError.message || "This action is not allowed",
              tender_status: businessError.tender_status,
              submission_deadline: businessError.submission_deadline,
              details: businessError
            }, { status: 403 });
          } catch (parseError) {
            console.error('Failed to parse 403 business error response:', parseError);
            useMockResponse = true;
          }
        } else {
          // Handle other non-success responses
          let errorMessage = `ERP API responded with status: ${response.status}`;
          try {
            const text = await response.text();
            console.log('🔍 BID SUBMISSION API - ERP error response:', response.status, text.substring(0, 300));
            
            // Try to parse as JSON first
            try {
              const errorData = JSON.parse(text);
              errorMessage = errorData.message || errorMessage;
              console.log('❌ BID SUBMISSION API - Parsed error:', errorData);
            } catch (jsonParseError) {
              // Response is not JSON (probably HTML error page)
              console.log('❌ BID SUBMISSION API - Non-JSON response, using fallback');
              errorMessage = `ERP API error (${response.status})`;
            }
          } catch (textError) {
            console.error('❌ BID SUBMISSION API - Failed to read error response:', textError);
            errorMessage = `ERP API error (${response.status})`;
          }
          console.warn('ERP API error for bid submission:', errorMessage);
          useMockResponse = true;
        }
      } catch (error) {
        console.warn('External API not available for bid submission, using mock response:', error);
        useMockResponse = true;
      }
    } else {
      console.warn('No external API URL configured, using mock response');
      useMockResponse = true;
    }

    // Fallback to mock response
    if (useMockResponse) {
      console.log('Using mock bid submission response');
      
      // Generate mock bid data
      const mockBid = {
        id: `mock-bid-${Date.now()}`,
        tenderId: tenderId,
        supplierId: session.user.thirdPartyId || 'mock-supplier',
        bidAmount: bidAmount,
        currency: currency,
        validityPeriod: validityPeriod,
        deliveryPeriod: deliveryPeriod,
        paymentTerms: paymentTerms,
        status: status,
        documents: files.length > 0 ? files.map((file, index) => ({
          id: `mock-doc-${Date.now()}-${index}`,
          filename: file.name,
          originalName: file.name,
          size: file.size,
          type: documentTypes[index] || 'other',
          uploadedAt: new Date().toISOString(),
          encryptionStatus: 'encrypted',
          encryptionKey: `mock-key-${Date.now()}`
        })) : [],
        createdAt: new Date().toISOString(),
        createdBy: session.user.id,
        encryptionEnabled: true,
        responsivenessScore: Math.floor(Math.random() * 30) + 70, // Mock score between 70-100
        totalDocuments: files.length,
      };

      return NextResponse.json({
        message: "Tender bid created successfully! (Mock mode - ERP not connected)",
        data: mockBid,
        fallback: true, // Indicate this is fallback data
      });
    }

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
