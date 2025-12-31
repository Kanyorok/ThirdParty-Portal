import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

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

interface CreateClarificationRequest {
  tenderId: string;
  question: string;
  isPublic?: boolean;
  attachments?: string[];
}

const EXTERNAL_API_URL = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

async function getAuthSession() {
  const session = await getServerSession(authOptions);
  if (!session?.user) return null;
  return session;
}

export async function GET(request: NextRequest) {
  try {
    const session = await getAuthSession();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const searchParams = request.nextUrl.searchParams;
    const tenderId = searchParams.get('tenderId');
    if (!tenderId) return NextResponse.json({ error: "Tender ID is required" }, { status: 400 });

    const status = searchParams.get('status') || 'all';
    const page = parseInt(searchParams.get('page') || '1');
    const limit = parseInt(searchParams.get('limit') || '20');
    const thirdPartyId = session.user.thirdPartyId;

    const queryParams = new URLSearchParams({
      tender_id: tenderId,
      page: page.toString(),
      limit: limit.toString(),
    });

    if (status !== 'all') queryParams.append('status', status);
    if (thirdPartyId) queryParams.append('third_party_id', thirdPartyId.toString());

    if (EXTERNAL_API_URL) {
      try {
        const response = await fetch(`${EXTERNAL_API_URL}/api/tender-clarifications?${queryParams}`, {
          headers: {
            'Authorization': `Bearer ${session.accessToken}`,
            'Accept': 'application/json',
          },
          signal: AbortSignal.timeout(20000)
        });

        if (response.ok) {
          const result = await response.json();
          return NextResponse.json({
            data: result.data,
            pagination: {
              total: result.total,
              page: result.page,
              limit: result.limit,
              pages: Math.ceil(result.total / result.limit),
            },
          });
        }
      } catch (e) {
        console.error("ERP Fetch Error:", e);
      }
    }

    return NextResponse.json({
      data: [],
      pagination: { total: 0, page, limit, pages: 0 },
      message: "No data available"
    });
  } catch (error) {
    return NextResponse.json(
      { error: "Fetch failed", message: error instanceof Error ? error.message : "Unknown error" },
      { status: 500 }
    );
  }
}

export async function POST(request: NextRequest) {
  try {
    const session = await getAuthSession();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const body: CreateClarificationRequest = await request.json();
    const { tenderId, question, isPublic, attachments } = body;

    if (!tenderId || !question?.trim()) {
      return NextResponse.json({ error: "Tender ID and question are required" }, { status: 400 });
    }

    const thirdPartyId = session.user.thirdPartyId;
    if (!thirdPartyId) return NextResponse.json({ error: "Third Party ID not found" }, { status: 400 });

    const payload = {
      tender_id: parseInt(tenderId),
      third_party_id: thirdPartyId,
      question: question.trim(),
      question_date: new Date().toISOString(),
      status: 'pending',
      is_public: !!isPublic,
      attachments: attachments || [],
      created_by: session.user.id,
      created_on: new Date().toISOString(),
    };

    if (EXTERNAL_API_URL) {
      const response = await fetch(`${EXTERNAL_API_URL}/api/tender-clarifications`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${session.accessToken}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
        signal: AbortSignal.timeout(20000)
      });

      if (response.ok) {
        const data = await response.json();
        return NextResponse.json({ message: "Submitted successfully", data });
      }
    }

    return NextResponse.json({ error: "Service unavailable" }, { status: 503 });
  } catch (error) {
    return NextResponse.json(
      { error: "Submission failed", message: error instanceof Error ? error.message : "Unknown error" },
      { status: 500 }
    );
  }
}

export async function PUT(request: NextRequest) {
  try {
    const session = await getAuthSession();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const { clarificationId, response, responseBy, publishToAll, status } = await request.json();

    if (!clarificationId || !response?.trim()) {
      return NextResponse.json({ error: "ID and response required" }, { status: 400 });
    }

    const payload = {
      response: response.trim(),
      response_by: responseBy || 'Procurement Team',
      response_date: new Date().toISOString(),
      status: status || 'answered',
      is_public: !!publishToAll,
      modified_by: session.user.id,
      modified_on: new Date().toISOString(),
    };

    if (EXTERNAL_API_URL) {
      const res = await fetch(`${EXTERNAL_API_URL}/api/tender-clarifications/${clarificationId}/respond`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${session.accessToken}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
        signal: AbortSignal.timeout(20000)
      });

      if (res.ok) {
        const data = await res.json();
        return NextResponse.json({ message: "Response recorded", data });
      }
    }

    return NextResponse.json({ error: "Service unavailable" }, { status: 503 });
  } catch (error) {
    return NextResponse.json(
      { error: "Update failed", message: error instanceof Error ? error.message : "Unknown error" },
      { status: 500 }
    );
  }
}