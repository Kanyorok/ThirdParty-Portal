import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

const FINAL_INVITATION_STATUSES = new Set(["accepted", "declined", "rejected"]);

function normalizeStatus(value: unknown) {
  return String(value ?? "").trim().toLowerCase();
}

export async function GET(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);

    if (!session?.user || !session.accessToken) {
      return NextResponse.json(
        { success: false, error: "Unauthorized" },
        { status: 401 }
      );
    }

    const searchParams = request.nextUrl.searchParams;
    const queryParams = new URLSearchParams();

    const tenderId = searchParams.get("tender_id") ?? searchParams.get("tenderId");
    if (tenderId) queryParams.append("tender_id", tenderId);

    const status = searchParams.get("status");
    if (status) queryParams.append("status", status);

    const apiBase =
      process.env.ERP_BASE_URL ||
      process.env.NEXT_PUBLIC_API_URL;

    if (!apiBase) {
      return NextResponse.json(
        { success: false, error: "API configuration missing" },
        { status: 500 }
      );
    }

    const qs = queryParams.toString();
    const url = `${apiBase}/api/v1/supplier/tenders/invitations${qs ? `?${qs}` : ""}`;

    const response = await fetch(url, {
      method: "GET",
      headers: {
        Authorization: `Bearer ${session.accessToken}`,
        Accept: "application/json",
      },
      signal: AbortSignal.timeout(10000),
    });

    const body = await response.json().catch(() => null);

    if (!response.ok) {
      return NextResponse.json(
        {
          success: false,
          error: body?.message || "Failed to fetch invitations",
        },
        { status: response.status }
      );
    }

    // Backend returns { success, message, data, total }
    return NextResponse.json({
      success: body?.success ?? true,
      message: body?.message ?? "OK",
      data: body?.data ?? [],
      total: body?.total ?? 0,
    });
  } catch (error) {
    return NextResponse.json(
      {
        success: false,
        error: "Failed to fetch tender invitations",
        message: error instanceof Error ? error.message : "Unknown error",
      },
      { status: 500 }
    );
  }
}

export async function POST(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);

    if (!session?.user || !session.accessToken) {
      return NextResponse.json(
        { success: false, error: "Unauthorized" },
        { status: 401 }
      );
    }

    const body = await request.json();
    const { responseStatus, response_status, declineReason, decline_reason, tenderId, tender_id } = body;
    const status = response_status ?? responseStatus;
    const tender = tender_id ?? tenderId;
    const decline = decline_reason ?? declineReason;
    const normalizedStatus = normalizeStatus(status);

    if (!tender || !normalizedStatus) {
      return NextResponse.json(
        { success: false, error: "Tender ID and ResponseStatus are required" },
        { status: 400 }
      );
    }

    if (!["accepted", "declined"].includes(normalizedStatus)) {
      return NextResponse.json(
        { success: false, error: "ResponseStatus must be either accepted or declined" },
        { status: 400 }
      );
    }

    if (normalizedStatus === "declined" && !decline) {
      return NextResponse.json(
        { success: false, error: "Decline reason is required" },
        { status: 400 }
      );
    }

    const apiBase =
      process.env.ERP_BASE_URL ||
      process.env.NEXT_PUBLIC_API_URL;

    if (!apiBase) {
      return NextResponse.json(
        { success: false, error: "API configuration missing" },
        { status: 500 }
      );
    }

    const existingResponse = await fetch(
      `${apiBase}/api/v1/supplier/tenders/invitations?tender_id=${encodeURIComponent(String(tender))}`,
      {
        method: "GET",
        headers: {
          Authorization: `Bearer ${session.accessToken}`,
          Accept: "application/json",
        },
        signal: AbortSignal.timeout(10000),
      }
    );

    if (existingResponse.ok) {
      const existingBody = await existingResponse.json().catch(() => null);
      const existingData = Array.isArray(existingBody?.data) ? existingBody.data : [];

      const matchedInvitation = existingData.find((entry: any) => {
        const invitation = entry?.invitation ?? entry;
        const invitationTenderId =
          invitation?.TenderId ??
          invitation?.tenderId ??
          entry?.tender?.Id ??
          entry?.tender?.id;
        return String(invitationTenderId ?? "").trim() === String(tender).trim();
      });

      const existingStatus = normalizeStatus(
        matchedInvitation?.ResponseStatus ?? matchedInvitation?.responseStatus
      );

      if (FINAL_INVITATION_STATUSES.has(existingStatus)) {
        return NextResponse.json(
          {
            success: false,
            error: `Invitation response is already ${existingStatus} and cannot be changed.`,
            responseStatus: existingStatus,
          },
          { status: 409 }
        );
      }
    }

    const response = await fetch(
      `${apiBase}/api/v1/supplier/tenders/respond`,
      {
        method: "POST",
        headers: {
          Authorization: `Bearer ${session.accessToken}`,
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          tender_id: Number(tender),
          response_status: normalizedStatus,
          decline_reason: decline || null,
        }),
      }
    );

    const result = await response.json().catch(() => null);

    if (!response.ok) {
      return NextResponse.json(
        { success: false, error: result?.message || "Failed to update invitation" },
        { status: response.status }
      );
    }

    return NextResponse.json({
      success: result?.success ?? true,
      message: result?.message ?? "Tender invitation updated successfully",
      data: result?.data ?? result,
    });
  } catch (error) {
    return NextResponse.json(
      {
        success: false,
        error: "Internal server error",
        message: error instanceof Error ? error.message : "Unknown error",
      },
      { status: 500 }
    );
  }
}
