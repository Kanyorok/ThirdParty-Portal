import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

// Types for Tender Clarifications
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
  isPublic: boolean; // Whether the clarification is visible to all suppliers
  attachments?: string[];
  createdBy: string;
  createdOn: string;
  modifiedBy?: string;
  modifiedOn?: string;
}

interface CreateClarificationRequest {
  tenderId: string;
  question: string;
  isPublic?: boolean;
  attachments?: string[];
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
    const tenderId = searchParams.get('tender_id') || searchParams.get('tenderId');
    const status = searchParams.get('status') || 'all';
    const page = parseInt(searchParams.get('page') || '1');
    const limit = parseInt(searchParams.get('limit') || '20');

    if (!tenderId) {
      return NextResponse.json(
        { error: "Tender ID is required" },
        { status: 400 }
      );
    }

    const thirdPartyId = session.user.thirdPartyId;

    // Build query parameters for external API
    const queryParams = new URLSearchParams({
      tender_id: tenderId,
      page: page.toString(),
      limit: limit.toString(),
    });

    if (status !== 'all') {
      queryParams.append('status', status);
    }

    // Include third party ID for supplier lookup on backend
    if (thirdPartyId) {
      queryParams.append('third_party_id', thirdPartyId.toString());
    }

    const externalApiUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

    if (externalApiUrl) {
      try {
        // Fetch from external API
        const apiUrl = `${externalApiUrl}/api/tender-clarifications?${queryParams}`;

        const response = await fetch(apiUrl, {
          headers: {
            'Authorization': `Bearer ${session.accessToken}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          signal: AbortSignal.timeout(20000) // 10 seconds timeout
        });

        if (response.ok) {
          const data: {
            data: TenderClarification[];
            total: number;
            page: number;
            limit: number;
          } = await response.json();

          return NextResponse.json({
            data: data.data || [],
            pagination: {
              total: data.total,
              page: data.page,
              limit: data.limit,
              pages: Math.ceil(data.total / data.limit),
            },
          });
        } else {
          const errorData = await response.json();
          return NextResponse.json(errorData, { status: response.status });
        }
      } catch (error) {
        console.error('External API error:', error);
        // Better to return the actual error than fallback to mock data silently in production
        return NextResponse.json(
          { error: "External API Error", details: error instanceof Error ? error.message : String(error) },
          { status: 502 }
        );
      }
    }



  } catch (error) {
    console.error('Failed to fetch tender clarifications:', error);
    return NextResponse.json(
      {
        error: "Failed to fetch tender clarifications",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: 500 }
    );
  }
}

// PUT method for ERP to respond to clarifications
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
    const { clarificationId, response, responseBy, publishToAll, status } = body;

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
      publishToAll: publishToAll || false, // Option to make private clarifications public
      modifiedBy: session.user.id,
      modifiedOn: new Date().toISOString(),
    };

    const externalApiUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

    if (externalApiUrl) {
      try {
        // Send response to external ERP API
        const apiUrl = `${externalApiUrl}/api/tender-clarifications/${clarificationId}/respond`;

        const response = await fetch(apiUrl, {
          method: 'PUT',
          headers: {
            'Authorization': `Bearer ${session.accessToken}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(responsePayload),
          signal: AbortSignal.timeout(20000)
        });

        if (response.ok) {
          const updatedClarification = await response.json();

          return NextResponse.json({
            message: "Clarification response submitted successfully",
            data: updatedClarification,
          });
        } else {
          const errorData = await response.json();
          return NextResponse.json(errorData, { status: response.status });
        }
      } catch (error) {
        console.error('External ERP API error:', error);
        return NextResponse.json(
          { error: "External API Error", details: error instanceof Error ? error.message : String(error) },
          { status: 502 }
        );
      }
    } else {
      return NextResponse.json(
        { error: "Configuration Error", details: "NEXT_PUBLIC_EXTERNAL_API_URL is not defined" },
        { status: 500 }
      );
    }

    return NextResponse.json(
      { error: "Unknown Error", details: "Failed to process request" },
      { status: 500 }
    );

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

export async function POST(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);

    if (!session?.user) {
      return NextResponse.json(
        { error: "Unauthorized" },
        { status: 401 }
      );
    }

    const body = await request.json();
    // Support both camelCase and snake_case
    const tenderId = body.tenderId || body.tender_id;
    const { question, isPublic, attachments } = body;

    // Validate required fields
    if (!tenderId || !question?.trim()) {
      return NextResponse.json(
        { error: "Tender ID and question are required" },
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

    // Prepare payload for external API using Laravel's expected field names (snake_case)
    const clarificationPayload = {
      tender_id: parseInt(tenderId.toString()), // Laravel expects integer
      third_party_id: thirdPartyId, // Laravel expects snake_case
      question: question.trim(),
      question_date: new Date().toISOString(),
      status: 'pending',
      is_public: isPublic || false,  // Laravel expects snake_case
      attachments: attachments || [],
      created_by: session.user.id,
      created_on: new Date().toISOString(),

      // Also include camelCase versions for backwards compatibility
      tenderId: parseInt(tenderId.toString()),
      thirdPartyId,
      questionDate: new Date().toISOString(),
      isPublic: isPublic || false,
      createdBy: session.user.id,
      createdOn: new Date().toISOString(),
    };

    const externalApiUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

    if (externalApiUrl) {
      try {
        // Send to external API
        const apiUrl = `${externalApiUrl}/api/tender-clarifications`;

        const response = await fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${session.accessToken}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(clarificationPayload),
          signal: AbortSignal.timeout(20000) // 10 seconds timeout
        });

        if (response.ok) {
          const newClarification = await response.json();

          return NextResponse.json({
            message: "Clarification request submitted successfully",
            data: newClarification,
          });
        } else {
          const errorData = await response.json();
          return NextResponse.json(errorData, { status: response.status });
        }
      } catch (error) {
        console.error('External API error:', error);
        return NextResponse.json(
          { error: "External API Error", details: error instanceof Error ? error.message : String(error) },
          { status: 502 }
        );
      }
    } else {
      return NextResponse.json(
        { error: "Configuration Error", details: "NEXT_PUBLIC_EXTERNAL_API_URL is not defined" },
        { status: 500 }
      );
    }

    return NextResponse.json(
      { error: "Unknown Error", details: "Failed to process request" },
      { status: 500 }
    );

  } catch (error) {
    console.error('Failed to create tender clarification:', error);
    return NextResponse.json(
      {
        error: "Failed to submit clarification request",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: 500 }
    );
  }
}
