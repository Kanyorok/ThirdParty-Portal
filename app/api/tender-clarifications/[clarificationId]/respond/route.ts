import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "../../../auth/[...nextauth]/route";

interface RespondClarificationRequest {
  response: string;
  responseBy?: string;
  publishToAll?: boolean;
  status?: 'answered' | 'closed';
  attachments?: string[];
}

interface TenderClarification {
  id: number;
  tenderId: string;
  supplierId: number;
  question: string;
  questionDate: string;
  response?: string;
  responseDate?: string;
  responseBy?: string;
  status: 'pending' | 'answered' | 'closed';
  isPublic: boolean;
  attachments?: string[];
  createdBy: string;
  createdOn: string;
  modifiedBy?: string;
  modifiedOn?: string;
}

export async function PUT(
  request: NextRequest,
  { params }: { params: { clarificationId: string } }
) {
  try {
    const session = await getServerSession(authOptions);
    
    if (!session?.user) {
      return NextResponse.json(
        { error: "Unauthorized" },
        { status: 401 }
      );
    }

    const clarificationId = params.clarificationId;
    const body: RespondClarificationRequest = await request.json();
    const { response, responseBy, publishToAll, status, attachments } = body;

    // Validate required fields
    if (!clarificationId || !response?.trim()) {
      return NextResponse.json(
        { error: "Clarification ID and response are required" },
        { status: 400 }
      );
    }

    // Prepare payload for external ERP API
    const responsePayload = {
      clarificationId,
      response: response.trim(),
      responseBy: responseBy || 'Procurement Team',
      responseDate: new Date().toISOString(),
      status: status || 'answered',
      publishToAll: publishToAll || false,
      attachments: attachments || [],
      modifiedBy: session.user.id,
      modifiedOn: new Date().toISOString(),
    };

    const externalApiUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

    if (externalApiUrl) {
      try {
        // Send response to external ERP API - direct Laravel call
        const apiUrl = `${externalApiUrl}/api/tender-clarifications/${clarificationId}/respond`;
        
        const apiResponse = await fetch(apiUrl, {
          method: 'PUT',
          headers: {
            'Authorization': `Bearer ${session.accessToken}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(responsePayload),
          signal: AbortSignal.timeout(10000)
        });

        if (apiResponse.ok) {
          const updatedClarification = await apiResponse.json();

          return NextResponse.json({
            message: "Clarification response submitted successfully",
            data: updatedClarification,
            publishedToAll: publishToAll,
          });
        } else {
          // Log the error but continue with mock response
          const errorData = await apiResponse.text();
          console.warn('ERP API response failed:', apiResponse.status, errorData);
        }
      } catch (error) {
        console.warn('External ERP API not available for clarification response, using mock response:', error);
      }
    }

    // Fallback: Mock successful response submission
    const mockUpdatedClarification: TenderClarification = {
      id: parseInt(clarificationId),
      tenderId: "TENDER/2024/001", // Use realistic tender ID
      supplierId: 1,
      question: "Can you please clarify the delivery requirements for this tender?",
      questionDate: new Date(Date.now() - 86400000).toISOString(), // 1 day ago
      response: response.trim(),
      responseDate: new Date().toISOString(),
      responseBy: responseBy || 'Procurement Team',
      status: status || 'answered',
      isPublic: publishToAll || false,
      attachments: attachments || [],
      createdBy: 'supplier@example.com',
      createdOn: new Date(Date.now() - 86400000).toISOString(),
      modifiedBy: session.user.id,
      modifiedOn: new Date().toISOString(),
    };

    console.log('Mock clarification response submitted:', mockUpdatedClarification);

    return NextResponse.json({
      message: "Clarification response submitted successfully (mock mode)",
      data: mockUpdatedClarification,
      publishedToAll: publishToAll,
      fallback: true,
    });

  } catch (error) {
    console.error('Failed to respond to clarification:', error);
    return NextResponse.json(
      {
        error: "Failed to submit clarification response",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: 500 }
    );
  }
}
