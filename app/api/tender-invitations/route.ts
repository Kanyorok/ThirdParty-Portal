import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

interface TenderInvitation {
  InvitationID: number;
  TenderId: string;
  SupplierId: number;
  InvitationDate: string;
  ResponseStatus: "pending" | "accepted" | "declined" | "submitted";
  ResponseDate?: string;
  DeclineReason?: string;
  ConfirmationAttachment?: string;
  CreatedBy: string;
  CreatedOn: string;
  ModifiedBy?: string;
  ModifiedOn?: string;
}

interface TenderInvitationResponse {
  invitation: TenderInvitation;
  tender: {
    id: string;
    tenderNo: string;
    title: string;
    tenderType: string;
    submissionDeadline: string;
    openingDate: string;
    status: string;
    estimatedValue?: string;
    currency?: {
      code: string;
      symbol: string;
    };
  };
}

interface ExternalApiResponse {
  data: TenderInvitationResponse[];
  total: number;
  page: number;
  limit: number;
  supplierInfo?: {
    supplierId: number;
    activeRoundId: number;
    third_party_id: number;
  };
}

export async function GET(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);

    if (!session?.user || !session.accessToken) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const third_party_id = session.user.third_party_id;
    if (!third_party_id) {
      return NextResponse.json(
        { error: "Third Party ID not found in session" },
        { status: 400 }
      );
    }

    const searchParams = request.nextUrl.searchParams;
    const queryParams = new URLSearchParams({
      third_party_id: third_party_id.toString(),
      page: searchParams.get("page") || "1",
      limit: searchParams.get("limit") || "10",
    });

    const status = searchParams.get("status");
    if (status && status !== "all") {
      queryParams.append("status", status);
    }

    const externalApiUrl = process.env.NEXT_PUBLIC_API_URL;
    if (!externalApiUrl) {
      return NextResponse.json(
        { error: "API configuration missing" },
        { status: 500 }
      );
    }

    const response = await fetch(
      `${externalApiUrl}/api/tender-invitations?${queryParams}`,
      {
        headers: {
          Authorization: `Bearer ${session.accessToken}`,
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        signal: AbortSignal.timeout(10000),
      }
    );

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      return NextResponse.json(
        { error: errorData.message || "External API error" },
        { status: response.status }
      );
    }

    const data: ExternalApiResponse = await response.json();

    return NextResponse.json({
      data: data.data,
      pagination: {
        total: data.total,
        page: data.page,
        limit: data.limit,
        pages: Math.ceil(data.total / data.limit),
      },
      supplierInfo: data.supplierInfo,
    });
  } catch (error) {
    return NextResponse.json(
      {
        error: "Failed to fetch tender invitations",
        message: error instanceof Error ? error.message : "Unknown error",
      },
      { status: 500 }
    );
  }
}

export async function PUT(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);

    if (!session?.user || !session.accessToken) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const body = await request.json();
    const { invitationId, responseStatus, declineReason } = body;

    if (!invitationId || !responseStatus) {
      return NextResponse.json(
        { error: "InvitationID and ResponseStatus are required" },
        { status: 400 }
      );
    }

    if (responseStatus === "declined" && !declineReason) {
      return NextResponse.json(
        { error: "Decline reason is required" },
        { status: 400 }
      );
    }

    const externalApiUrl = process.env.NEXT_PUBLIC_API_URL;
    const response = await fetch(
      `${externalApiUrl}/api/tender-invitations/${invitationId}`,
      {
        method: "PUT",
        headers: {
          Authorization: `Bearer ${session.accessToken}`,
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          responseStatus,
          declineReason: declineReason || null,
        }),
      }
    );

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      return NextResponse.json(
        { error: errorData.message || "Failed to update invitation" },
        { status: response.status }
      );
    }

    const result = await response.json();
    return NextResponse.json({
      message: "Tender invitation updated successfully",
      data: result,
    });
  } catch (error) {
    return NextResponse.json(
      {
        error: "Internal server error",
        message: error instanceof Error ? error.message : "Unknown error",
      },
      { status: 500 }
    );
  }
}