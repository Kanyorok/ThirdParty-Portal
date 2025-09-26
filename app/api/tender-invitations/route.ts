import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "../auth/[...nextauth]/route";

// Types based on your t_TenderInvitations table structure
interface TenderInvitation {
  InvitationID: number;
  TenderId: string;
  SupplierId: number;
  InvitationDate: string;
  ResponseStatus: 'pending' | 'accepted' | 'declined' | 'submitted';
  ResponseDate?: string;
  DeclineReason?: string;
  ConfirmationAttachment?: string;
  CreatedBy: string;
  CreatedOn: string;
  ModifiedBy?: string;
  ModifiedOn?: string;
  DeletedBy?: string;
  DeletedOn?: string;
}

interface TenderInvitationResponse {
  invitation: TenderInvitation;
  tender: {
    id: string;
    tenderNo: string;
    title: string;
    tenderType: string;
    submissionDeadline: string;
    openingDate: string;
    status: string;
    estimatedValue?: string;
    currency?: {
      code: string;
      symbol: string;
    };
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

    const searchParams = request.nextUrl.searchParams;
    const status = searchParams.get('status') || 'all';
    const page = parseInt(searchParams.get('page') || '1');
    const limit = parseInt(searchParams.get('limit') || '10');
    
    // Get thirdPartyId from session (logged in user's profile)
    const thirdPartyId = session.user.thirdPartyId;
    
    if (!thirdPartyId) {
      return NextResponse.json(
        { error: "Third Party ID not found in session" },
        { status: 400 }
      );
    }

    // Build query parameters for external API
    // The external API should handle the multi-table lookup:
    // 1. Get supplierID from t_Suppliers where ThirdPartyID = thirdPartyId
    // 2. Get active RoundID from t_PrequalificationRounds where status = 'O'
    // 3. Fetch invitations from t_TenderInvitations for that supplierID
    const queryParams = new URLSearchParams({
      third_party_id: thirdPartyId.toString(), // Pass thirdPartyId instead of supplierId
      page: page.toString(),
      limit: limit.toString(),
    });

    if (status !== 'all') {
      queryParams.append('status', status);
    }

    // Try to fetch from external API first
    const externalApiUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;
    
    if (externalApiUrl) {
      try {
        const apiUrl = `${externalApiUrl}/api/tender-invitations?${queryParams}`;
        
        const response = await fetch(apiUrl, {
          headers: {
            'Authorization': `Bearer ${session.accessToken}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          signal: AbortSignal.timeout(10000) // 10 seconds timeout
        });

        if (response.ok) {
          const data: {
            data: TenderInvitationResponse[];
            total: number;
            page: number;
            limit: number;
            supplierInfo?: {
              supplierId: number;
              activeRoundId: number;
              thirdPartyId: number;
            };
          } = await response.json();

          return NextResponse.json({
            data: data.data,
            pagination: {
              total: data.total,
              page: data.page,
              limit: data.limit,
              pages: Math.ceil(data.total / data.limit),
            },
            supplierInfo: data.supplierInfo,
          });
        }
      } catch (error) {
        console.warn('External API not available for tender invitations, using mock data:', error);
      }
    }

    // Fallback to mock data if external API is unavailable
    const mockInvitations: TenderInvitation[] = [
      {
        InvitationID: 1,
        TenderId: 1,
        SupplierId: 1,
        InvitationDate: "2024-11-15T08:00:00.000Z",
        ResponseStatus: "pending",
        ResponseDate: undefined,
        DeclineReason: undefined,
        ConfirmationAttachment: undefined,
        CreatedBy: "system",
        CreatedOn: "2024-11-15T08:00:00.000Z",
        ModifiedBy: undefined,
        ModifiedOn: undefined,
        DeletedBy: undefined,
        DeletedOn: undefined,
      },
      {
        InvitationID: 2,
        TenderId: 2,
        SupplierId: 1,
        InvitationDate: "2024-11-10T10:00:00.000Z",
        ResponseStatus: "accepted",
        ResponseDate: "2024-11-12T14:30:00.000Z",
        DeclineReason: undefined,
        ConfirmationAttachment: undefined,
        CreatedBy: "system",
        CreatedOn: "2024-11-10T10:00:00.000Z",
        ModifiedBy: "user",
        ModifiedOn: "2024-11-12T14:30:00.000Z",
        DeletedBy: undefined,
        DeletedOn: undefined,
      },
    ];

    // Filter mock data based on status if specified
    let filteredInvitations = [...mockInvitations];
    if (status !== 'all') {
      filteredInvitations = filteredInvitations.filter(inv => inv.ResponseStatus === status);
    }

    // Create mock response data
    const mockResponseData: TenderInvitationResponse[] = filteredInvitations.map(invitation => ({
      invitation,
        tender: {
          id: invitation.TenderId,
          tenderNo: invitation.TenderId === 1 ? "TENDER/2024/001" : "TENDER/2024/002",
          title: invitation.TenderId === 1 ? "Supply and Installation of Office Equipment" : "Construction of Drainage System",
          tenderType: invitation.TenderId === 1 ? "op" : "rs",
          submissionDeadline: invitation.TenderId === 1 ? "2024-12-01T23:59:00.000Z" : "2024-11-30T17:00:00.000Z",
          openingDate: invitation.TenderId === 1 ? "2024-12-02T10:00:00.000Z" : "2024-12-01T14:00:00.000Z",
          status: "pb",
          estimatedValue: invitation.TenderId === 1 ? "2500000" : "15000000",
          currency: {
            code: "KES",
            symbol: "KSh"
          }
        }
    }));

    // Apply pagination
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + limit;
    const paginatedData = mockResponseData.slice(startIndex, endIndex);

    return NextResponse.json({
      data: paginatedData,
      pagination: {
        total: mockResponseData.length,
        page,
        limit,
        pages: Math.ceil(mockResponseData.length / limit),
      },
      supplierInfo: {
        supplierId: 1,
        activeRoundId: 1,
        thirdPartyId: thirdPartyId,
      },
      fallback: true, // Indicates this is mock data
    });

  } catch (error) {
    console.error('Failed to fetch tender invitations:', error);
    return NextResponse.json(
      { 
        error: "Failed to fetch tender invitations",
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

    const body = await request.json();
    const { 
      invitationId, 
      responseStatus, 
      declineReason,
      confirmationAttachment 
    } = body;

    // Validate required fields
    if (!invitationId || !responseStatus) {
      return NextResponse.json(
        { error: "InvitationID and ResponseStatus are required" },
        { status: 400 }
      );
    }

    // Validate decline reason if status is declined
    if (responseStatus === 'declined' && !declineReason) {
      return NextResponse.json(
        { error: "Decline reason is required when declining an invitation" },
        { status: 400 }
      );
    }

    // Prepare payload for external API
    const updatePayload = {
      InvitationID: invitationId,
      ResponseStatus: responseStatus,
      ResponseDate: new Date().toISOString(),
      DeclineReason: declineReason || null,
      ConfirmationAttachment: confirmationAttachment || null,
      ModifiedBy: session.user.id,
      ModifiedOn: new Date().toISOString(),
    };

    // Send to external API
    const apiUrl = `${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/tender-invitations/${invitationId}`;
    
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

    const updatedInvitation = await response.json();

    return NextResponse.json({
      message: "Tender invitation response updated successfully",
      data: updatedInvitation,
    });

  } catch (error) {
    console.error('Failed to update tender invitation:', error);
    return NextResponse.json(
      { 
        error: "Failed to update tender invitation",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: 500 }
    );
  }
}
