import {NextRequest, NextResponse} from "next/server";
import {getServerSession} from "next-auth";
import {authOptions} from "../auth/[...nextauth]/route";

export async function GET(request: NextRequest) {
    try {
        const session = await getServerSession(authOptions);

        if (!session?.user) {
            return NextResponse.json(
                {error: "Unauthorized - Please log in first"},
                {status: 401}
            );
        }

        // Test connection to backend API
        const backendUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

        if (!backendUrl) {
            return NextResponse.json({
                status: "backend_error",
                message: "Backend URL not configured",
                details: "NEXT_PUBLIC_EXTERNAL_API_URL not set in environment",
                data_source: "mock_data",
                recommendations: [
                    "Set NEXT_PUBLIC_EXTERNAL_API_URL in .env.local",
                    "Ensure backend server is running",
                    "Verify API endpoints are implemented"
                ]
            });
        }

        try {
            // Test basic connectivity
            const response = await fetch(`${backendUrl}/api/health`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
                signal: AbortSignal.timeout(5000), // 5 second timeout
            });

            if (!response.ok) {
                throw new Error(`Backend responded with status: ${response.status}`);
            }

            // Test tenders endpoint
            const tendersResponse = await fetch(`${backendUrl}/api/tenders?limit=1`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
                signal: AbortSignal.timeout(5000),
            });

            const tendersWorking = tendersResponse.ok;

            // Test invitations endpoint
            const invitationsResponse = await fetch(`${backendUrl}/api/tender-invitations?supplier_id=${session.user.thirdPartyId}&limit=1`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${session.accessToken}`,
                    'Accept': 'application/json',
                },
                signal: AbortSignal.timeout(5000),
            });

            const invitationsWorking = invitationsResponse.ok;

            return NextResponse.json({
                status: "backend_connected",
                message: "Successfully connected to backend API",
                data_source: "real_data",
                backend_url: backendUrl,
                endpoints_status: {
                    health: "✅ Connected",
                    tenders: tendersWorking ? "✅ Working" : "❌ Not working",
                    invitations: invitationsWorking ? "✅ Working" : "❌ Not working"
                },
                user_info: {
                    id: session.user.id,
                    email: session.user.email,
                    supplier_id: session.user.thirdPartyId,
                },
                timestamp: new Date().toISOString()
            });

        } catch (fetchError) {
            return NextResponse.json({
                status: "backend_error",
                message: "Cannot connect to backend API",
                data_source: "mock_data",
                backend_url: backendUrl,
                error_details: fetchError instanceof Error ? fetchError.message : "Unknown connection error",
                recommendations: [
                    "Verify backend server is running",
                    "Check network connectivity",
                    "Ensure CORS is configured on backend",
                    "Verify API endpoints are implemented"
                ],
                fallback_behavior: "Using mock data for development"
            });
        }

    } catch (error) {
        console.error('Test connection error:', error);
        return NextResponse.json(
            {
                status: "system_error",
                error: "Internal server error during connection test",
                message: error instanceof Error ? error.message : "Unknown error"
            },
            {status: 500}
        );
    }
}
