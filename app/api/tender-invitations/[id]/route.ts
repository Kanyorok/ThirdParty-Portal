import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

export async function PUT(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const session = await getServerSession(authOptions);
    
    console.log('=== TENDER INVITATION UPDATE DEBUG ===');
    console.log('Session exists:', !!session);
    console.log('Session user:', session?.user?.email);
    console.log('Access token exists:', !!session?.accessToken);
    console.log('Access token preview:', session?.accessToken?.substring(0, 20) + '...');
    
    if (!session?.user) {
      console.log('ERROR: No session user found');
      return NextResponse.json(
        { error: "Unauthorized - Please log in" },
        { status: 401 }
      );
    }

    if (!session?.accessToken) {
      console.log('ERROR: No access token in session');
      return NextResponse.json(
        { error: "Authentication token missing" },
        { status: 401 }
      );
    }

    const { id } = await params;
    const body = await request.json();
    const { 
      responseStatus, 
      declineReason,
    } = body;

    console.log('Request payload:', { id, responseStatus, declineReason });

    // Validate required fields
    if (!responseStatus) {
      return NextResponse.json(
        { error: "ResponseStatus is required" },
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

    // Prepare payload for external API (match backend field names)
    const updatePayload = {
      responseStatus,
      declineReason: declineReason || null,
    };

    // Send to external API
    const externalApiUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;
    
    if (!externalApiUrl) {
      return NextResponse.json(
        { error: "External API URL not configured" },
        { status: 500 }
      );
    }

    const apiUrl = `${externalApiUrl}/api/tender-invitations/${id}`;
    
    console.log('Calling Laravel API:', apiUrl);
    console.log('With token:', session.accessToken.substring(0, 30) + '...');
    
    const response = await fetch(apiUrl, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${session.accessToken}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(updatePayload),
    });

    console.log('Laravel response status:', response.status);
    console.log('Laravel response ok:', response.ok);

    const responseText = await response.text();
    let updatedInvitation;
    
    try {
      updatedInvitation = responseText ? JSON.parse(responseText) : {};
    } catch {
      return NextResponse.json(
        { 
          error: "Invalid response from server",
          details: responseText.slice(0, 200)
        },
        { status: 500 }
      );
    }

    if (!response.ok) {
      return NextResponse.json(
        { 
          error: updatedInvitation.error || updatedInvitation.message || "Failed to update invitation",
          details: updatedInvitation
        },
        { status: response.status }
      );
    }

    return NextResponse.json({
      message: "Tender invitation response updated successfully",
      data: updatedInvitation.data || updatedInvitation,
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
