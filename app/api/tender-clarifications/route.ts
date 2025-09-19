import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "../auth/[...nextauth]/route";

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
    const tenderId = searchParams.get('tenderId');
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
            data: data.data,
            pagination: {
              total: data.total,
              page: data.page,
              limit: data.limit,
              pages: Math.ceil(data.total / data.limit),
            },
          });
        }
      } catch (error) {
        console.warn('External API not available for clarifications, using mock data:', error);
        console.warn('ERP URL attempted:', `${externalApiUrl}/api/tender-clarifications?${queryParams}`);
      }
    }

    // Fallback to mock data
    const mockClarifications: TenderClarification[] = [
      {
        id: 1,
        tenderId: tenderId,
        supplierId: 1,
        question: "Can you clarify the technical specifications for item #3 in the tender document?",
        questionDate: "2024-11-20T09:30:00.000Z",
        response: "The technical specifications for item #3 require a minimum processing speed of 3.2GHz and 16GB RAM. Please refer to appendix A for detailed requirements.",
        responseDate: "2024-11-20T14:45:00.000Z",
        responseBy: "John Smith - Procurement Manager",
        status: "answered",
        isPublic: true,
        createdBy: "supplier",
        createdOn: "2024-11-20T09:30:00.000Z",
        modifiedBy: "procurement",
        modifiedOn: "2024-11-20T14:45:00.000Z",
      },
      {
        id: 2,
        tenderId: tenderId,
        supplierId: 1,
        question: "What is the delivery timeline expectation for this tender?",
        questionDate: "2024-11-19T11:15:00.000Z",
        status: "pending",
        isPublic: false,
        createdBy: "supplier",
        createdOn: "2024-11-19T11:15:00.000Z",
      },
      {
        id: 3,
        tenderId: tenderId,
        supplierId: 2,
        question: "Are there any preferred brands for the networking equipment?",
        questionDate: "2024-11-18T16:20:00.000Z",
        response: "We do not have preferred brands, but all equipment must meet the ISO standards specified in section 4.2 of the tender document.",
        responseDate: "2024-11-19T08:30:00.000Z",
        responseBy: "Sarah Johnson - Technical Lead",
        status: "answered",
        isPublic: true,
        createdBy: "supplier",
        createdOn: "2024-11-18T16:20:00.000Z",
        modifiedBy: "procurement",
        modifiedOn: "2024-11-19T08:30:00.000Z",
      },
      {
        id: 4,
        tenderId: tenderId,
        supplierId: 1,
        question: "What is the payment schedule for this project? Are milestone payments available?",
        questionDate: new Date(Date.now() - 3600000).toISOString(),
        response: "Payment terms are Net 30 days from invoice date. Milestone payments are available upon completion of each phase as outlined in Section 7. Early payment discount of 2% applies if paid within 10 days.",
        responseDate: new Date(Date.now() - 1800000).toISOString(),
        responseBy: "Finance Department",
        status: "answered",
        isPublic: true,
        createdBy: "supplier",
        createdOn: new Date(Date.now() - 3600000).toISOString(),
        modifiedBy: "procurement",
        modifiedOn: new Date(Date.now() - 1800000).toISOString(),
      },
      {
        id: 5,
        tenderId: tenderId,
        supplierId: 3,
        question: "Are site visits required before bid submission? If so, when can they be scheduled?",
        questionDate: new Date(Date.now() - 1800000).toISOString(),
        status: "pending",
        isPublic: false,
        createdBy: "supplier3@example.com",
        createdOn: new Date(Date.now() - 1800000).toISOString(),
      },
      {
        id: 6,
        tenderId: tenderId,
        supplierId: 2,
        question: "Can you clarify the warranty requirements? The document mentions 2 years but doesn't specify coverage.",
        questionDate: new Date(Date.now() - 900000).toISOString(),
        status: "pending",
        isPublic: false,
        createdBy: "supplier2@example.com",
        createdOn: new Date(Date.now() - 900000).toISOString(),
      },
    ];

    // Filter by status if requested
    let filteredClarifications = [...mockClarifications];
    if (status !== 'all') {
      filteredClarifications = filteredClarifications.filter(c => c.status === status);
    }

    // Apply pagination
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + limit;
    const paginatedData = filteredClarifications.slice(startIndex, endIndex);

    return NextResponse.json({
      data: paginatedData,
      pagination: {
        total: filteredClarifications.length,
        page,
        limit,
        pages: Math.ceil(filteredClarifications.length / limit),
      },
      fallback: true, // Indicates mock data is being used
    });

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
        }
      } catch (error) {
        console.warn('External ERP API not available for clarification response, using mock response:', error);
      }
    }

    // Fallback: Mock successful response submission
    const mockUpdatedClarification: TenderClarification = {
      id: clarificationId,
      tenderId: "mock-tender-id",
      supplierId: 1,
      question: "Mock question for demonstration",
      questionDate: new Date(Date.now() - 86400000).toISOString(), // 1 day ago
      response: response.trim(),
      responseDate: new Date().toISOString(),
      responseBy: responseBy || 'Procurement Team',
      status: 'answered',
      isPublic: publishToAll || false,
      attachments: [],
      createdBy: 'mock-supplier',
      createdOn: new Date(Date.now() - 86400000).toISOString(),
      modifiedBy: session.user.id,
      modifiedOn: new Date().toISOString(),
    };

    console.log('Mock clarification response submitted:', mockUpdatedClarification);

    return NextResponse.json({
      message: "Clarification response submitted successfully (mock mode)",
      data: mockUpdatedClarification,
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

export async function POST(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);
    
    if (!session?.user) {
      return NextResponse.json(
        { error: "Unauthorized" },
        { status: 401 }
      );
    }

    const body: CreateClarificationRequest = await request.json();
    const { tenderId, question, isPublic, attachments } = body;

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
        }
      } catch (error) {
        console.warn('External API not available for submitting clarification, using mock response:', error);
      }
    }

    // Fallback: Mock successful submission
    const mockClarification: TenderClarification = {
      id: Math.floor(Math.random() * 1000) + 100, // Random ID for mock
      tenderId,
      supplierId: 1, // Mock supplier ID
      question: question.trim(),
      questionDate: new Date().toISOString(),
      status: 'pending',
      isPublic: isPublic || false,
      attachments: attachments || [],
      createdBy: session.user.email || session.user.id,
      createdOn: new Date().toISOString(),
    };

    console.log('Mock clarification submitted:', mockClarification);

    return NextResponse.json({
      message: "Clarification request submitted successfully (mock mode)",
      data: mockClarification,
      fallback: true, // Indicates mock submission
    });

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
