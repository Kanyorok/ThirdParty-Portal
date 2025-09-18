import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "../auth/[...nextauth]/route";

export async function GET(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);
    
    if (!session?.user) {
      return NextResponse.json(
        { error: "Unauthorized" },
        { status: 401 }
      );
    }

    const externalApiUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;
    
    if (!externalApiUrl) {
      return NextResponse.json({
        error: "External API not configured",
        NEXT_PUBLIC_EXTERNAL_API_URL: process.env.NEXT_PUBLIC_EXTERNAL_API_URL || 'not set',
      });
    }

    const results: any = {};

    // Test tender data
    try {
      const tendersResponse = await fetch(`${externalApiUrl}/api/tenders`, {
        headers: {
          'Authorization': `Bearer ${session.accessToken}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        signal: AbortSignal.timeout(10000)
      });

      if (tendersResponse.ok) {
        const tendersData = await tendersResponse.json();
        results.tenders = {
          status: 'success',
          sample: tendersData.data?.[0] || null,
          count: tendersData.data?.length || 0,
        };
      } else {
        results.tenders = {
          status: 'error',
          statusCode: tendersResponse.status,
          statusText: tendersResponse.statusText,
        };
      }
    } catch (error) {
      results.tenders = {
        status: 'error',
        error: error instanceof Error ? error.message : 'Unknown error',
      };
    }

    // Test invitation data
    try {
      const invitationsResponse = await fetch(`${externalApiUrl}/api/tender-invitations?third_party_id=${session.user.thirdPartyId}`, {
        headers: {
          'Authorization': `Bearer ${session.accessToken}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        signal: AbortSignal.timeout(10000)
      });

      if (invitationsResponse.ok) {
        const invitationsData = await invitationsResponse.json();
        results.invitations = {
          status: 'success',
          raw: invitationsData,
          sample: invitationsData.data?.[0] || null,
          count: invitationsData.data?.length || 0,
        };
      } else {
        results.invitations = {
          status: 'error',
          statusCode: invitationsResponse.status,
          statusText: invitationsResponse.statusText,
        };
      }
    } catch (error) {
      results.invitations = {
        status: 'error',
        error: error instanceof Error ? error.message : 'Unknown error',
      };
    }

    return NextResponse.json({
      user: {
        id: session.user.id,
        email: session.user.email,
        thirdPartyId: session.user.thirdPartyId,
      },
      externalApiUrl,
      results,
      debugInfo: {
        timestamp: new Date().toISOString(),
        message: "Use this to debug the exact data structure returned by your backend"
      }
    });

  } catch (error) {
    console.error('Debug endpoint error:', error);
    return NextResponse.json(
      { 
        error: "Debug endpoint failed",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: 500 }
    );
  }
}
