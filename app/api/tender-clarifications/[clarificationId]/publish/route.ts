import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "../../../auth/[...nextauth]/route";

interface PublishClarificationRequest {
  publishToAll: boolean;
  notifySuppliers?: boolean;
  publishedBy?: string;
}

export async function PATCH(
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
    const body: PublishClarificationRequest = await request.json();
    const { publishToAll, notifySuppliers, publishedBy } = body;

    // Validate required fields
    if (!clarificationId) {
      return NextResponse.json(
        { error: "Clarification ID is required" },
        { status: 400 }
      );
    }

    // Prepare payload for external ERP API
    const publishPayload = {
      clarificationId,
      publishToAll: publishToAll !== false, // Default to true
      notifySuppliers: notifySuppliers !== false, // Default to true - notify all suppliers
      publishedBy: publishedBy || 'Procurement Team',
      publishedDate: new Date().toISOString(),
      modifiedBy: session.user.id,
      modifiedOn: new Date().toISOString(),
    };

    const externalApiUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

    if (externalApiUrl) {
      try {
        // Send publish request to external ERP API
        const apiUrl = `${externalApiUrl}/api/tender-clarifications/${clarificationId}/publish`;
        
        const response = await fetch(apiUrl, {
          method: 'PATCH',
          headers: {
            'Authorization': `Bearer ${session.accessToken}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(publishPayload),
          signal: AbortSignal.timeout(10000)
        });

        if (response.ok) {
          const publishedClarification = await response.json();

          return NextResponse.json({
            message: publishToAll 
              ? "Clarification published to all suppliers successfully"
              : "Clarification set to private successfully",
            data: publishedClarification,
            suppliersNotified: notifySuppliers && publishToAll,
          });
        }
      } catch (error) {
        console.warn('External ERP API not available for clarification publishing, using mock response:', error);
      }
    }

    // Fallback: Mock successful publishing
    const mockPublishedClarification = {
      id: parseInt(clarificationId),
      isPublic: publishToAll,
      publishedDate: new Date().toISOString(),
      publishedBy: publishedBy || 'Procurement Team',
      suppliersNotified: notifySuppliers && publishToAll,
      modifiedBy: session.user.id,
      modifiedOn: new Date().toISOString(),
    };

    console.log('Mock clarification published:', mockPublishedClarification);

    return NextResponse.json({
      message: publishToAll 
        ? "Clarification published to all suppliers successfully (mock mode)"
        : "Clarification set to private successfully (mock mode)",
      data: mockPublishedClarification,
      suppliersNotified: notifySuppliers && publishToAll,
      fallback: true,
    });

  } catch (error) {
    console.error('Failed to publish clarification:', error);
    return NextResponse.json(
      {
        error: "Failed to publish clarification",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: 500 }
    );
  }
}
