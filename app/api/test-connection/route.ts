import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "../auth/[...nextauth]/route";

export async function GET(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);
    
    if (!session?.user) {
      return NextResponse.json(
        { error: "Unauthorized - Please login first" },
        { status: 401 }
      );
    }

    const externalApiUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;
    
    if (!externalApiUrl) {
      return NextResponse.json({
        status: "using_mock_data",
        message: "NEXT_PUBLIC_EXTERNAL_API_URL not configured - using mock data",
        user: {
          id: session.user.id,
          email: session.user.email,
          thirdPartyId: session.user.thirdPartyId
        },
        recommendations: [
          "Set NEXT_PUBLIC_EXTERNAL_API_URL in your .env.local file",
          "Implement backend APIs according to BACKEND_API_SPECIFICATION.md", 
          "Test connection with GET /api/test-connection again"
        ]
      });
    }

    // Test connection to external API
    try {
      const testUrl = `${externalApiUrl}/api/health`;
      const response = await fetch(testUrl, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${session.accessToken}`,
          'Accept': 'application/json',
        },
        signal: AbortSignal.timeout(5000) // 5 seconds timeout
      });

      if (response.ok) {
        const healthData = await response.json();
        return NextResponse.json({
          status: "backend_connected",
          message: "Successfully connected to external API",
          backend_url: externalApiUrl,
          user: {
            id: session.user.id,
            email: session.user.email,
            thirdPartyId: session.user.thirdPartyId
          },
          backend_response: healthData,
          next_steps: [
            "Backend is reachable!",
            "Test tender endpoints: GET /api/tenders",
            "Test invitations: GET /api/tender-invitations", 
            "Frontend will now use real data instead of mock data"
          ]
        });
      } else {
        throw new Error(`Backend responded with status: ${response.status}`);
      }

    } catch (apiError) {
      return NextResponse.json({
        status: "backend_error", 
        message: "External API configured but not accessible",
        backend_url: externalApiUrl,
        error: apiError instanceof Error ? apiError.message : "Unknown error",
        user: {
          id: session.user.id,
          email: session.user.email, 
          thirdPartyId: session.user.thirdPartyId
        },
        troubleshooting: [
          "Check if your backend server is running",
          "Verify the URL in NEXT_PUBLIC_EXTERNAL_API_URL",
          "Ensure backend implements GET /api/health endpoint",
          "Check backend CORS settings allow frontend origin",
          "Frontend will fallback to mock data until backend is ready"
        ]
      });
    }

  } catch (error) {
    console.error('Connection test failed:', error);
    return NextResponse.json(
      { 
        status: "test_failed",
        error: "Failed to test connection",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: 500 }
    );
  }
}
