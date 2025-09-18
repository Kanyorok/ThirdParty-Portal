import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "../../../auth/[...nextauth]/route";

interface SubmitBidRequest {
  confirmSubmission: boolean;
  finalDeclaration?: string;
}

export async function POST(
  request: NextRequest,
  { params }: { params: { bidId: string } }
) {
  try {
    const session = await getServerSession(authOptions);
    
    if (!session?.user) {
      return NextResponse.json(
        { error: "Unauthorized" },
        { status: 401 }
      );
    }

    const { bidId } = params;
    const body: SubmitBidRequest = await request.json();

    if (!body.confirmSubmission) {
      return NextResponse.json(
        { error: "Confirmation is required to submit bid" },
        { status: 400 }
      );
    }

    const supplierId = session.user.thirdPartyId;
    
    if (!supplierId) {
      return NextResponse.json(
        { error: "Supplier ID not found" },
        { status: 400 }
      );
    }

    // Prepare submission payload
    const submissionPayload = {
      status: 'submitted',
      submissionDate: new Date().toISOString(),
      finalDeclaration: body.finalDeclaration || null,
      modifiedBy: session.user.id,
      modifiedOn: new Date().toISOString(),
    };

    // Send to external API
    const apiUrl = `${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/tender-bids/${bidId}/submit`;
    
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${session.accessToken}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(submissionPayload),
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || `API responded with status: ${response.status}`);
    }

    const submittedBid = await response.json();

    return NextResponse.json({
      message: "Bid submitted successfully",
      data: submittedBid,
    });

  } catch (error) {
    console.error('Failed to submit tender bid:', error);
    return NextResponse.json(
      { 
        error: "Failed to submit tender bid",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: 500 }
    );
  }
}
