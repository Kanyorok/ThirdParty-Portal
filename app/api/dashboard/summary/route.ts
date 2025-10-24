import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

type PreqCategory = {
  has_applied?: boolean;
  hasApplied?: boolean;
  status?: string;
};

type PreqRound = {
  categories?: PreqCategory[];
};

type InvitationsBreakdown = Record<"pending" | "accepted" | "declined" | "submitted", number>;
type PreqBreakdown = Record<"approved" | "submitted" | "under_review" | "rejected" | "not_applied", number>;

async function safeJson(response: Response) {
  try {
    return await response.json();
  } catch {
    return null;
  }
}

export async function GET(request: NextRequest) {
  const session = await getServerSession(authOptions);
  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  const accessToken = (session as any).accessToken as string | undefined;
  const thirdPartyId = (session.user as any)?.thirdPartyId as number | undefined;

  const origin = new URL(request.url).origin;
  const externalBase = process.env.NEXT_PUBLIC_EXTERNAL_API_URL || process.env.ERP_BASE_URL || process.env.NEXT_PUBLIC_ERP_BASE_URL;

  const headers: HeadersInit = accessToken
    ? { Accept: "application/json", Authorization: `Bearer ${accessToken}` }
    : { Accept: "application/json" };

  // Defaults
  let activePreq = 0;
  let completedPreq = 0;
  let invitesTotal = 0;
  let tendersAvailable = 0;

  const preqBreakdown: PreqBreakdown = {
    approved: 0,
    submitted: 0,
    under_review: 0,
    rejected: 0,
    not_applied: 0,
  };

  const inviteBreakdown: InvitationsBreakdown = {
    pending: 0,
    accepted: 0,
    declined: 0,
    submitted: 0,
  };

  // Helper to fetch with fallback to internal proxy
  async function fetchWithFallback(primaryUrl: string | null, fallbackPath: string) {
    try {
      if (primaryUrl) {
        const res = await fetch(primaryUrl, { headers, signal: AbortSignal.timeout(10000) });
        if (res.ok) return await safeJson(res);
      }
    } catch {}
    try {
      const res2 = await fetch(`${origin}${fallbackPath}`, { headers, signal: AbortSignal.timeout(10000) });
      if (res2.ok) return await safeJson(res2);
    } catch {}
    return null;
  }

  // 1) Prequalification rounds and categories per supplier
  const preqUrl = externalBase ? `${externalBase}/api/prequalification/rounds` : null;
  const preqData = await fetchWithFallback(preqUrl, "/api/prequalification/rounds");

  const rounds: PreqRound[] = Array.isArray(preqData?.data) ? preqData.data : [];
  for (const round of rounds) {
    const cats = Array.isArray(round.categories) ? round.categories : [];
    for (const c of cats) {
      const hasApplied = Boolean(c.hasApplied ?? c.has_applied);
      const status = (c.status || "").toUpperCase();
      if (!hasApplied) {
        preqBreakdown.not_applied += 1;
        continue;
      }
      if (status === "APPROVED") {
        preqBreakdown.approved += 1;
        completedPreq += 1;
      } else if (status === "REJECTED") {
        preqBreakdown.rejected += 1;
      } else if (status === "UNDER_REVIEW") {
        preqBreakdown.under_review += 1;
        activePreq += 1;
      } else if (status === "SUBMITTED") {
        preqBreakdown.submitted += 1;
        activePreq += 1;
      } else {
        // Unknown statuses treated as active
        activePreq += 1;
      }
    }
  }

  // 2) Tender invitations per supplier
  let invitesUrl: string | null = null;
  if (externalBase && thirdPartyId) {
    const qp = new URLSearchParams({ third_party_id: String(thirdPartyId) });
    invitesUrl = `${externalBase}/api/tender-invitations?${qp.toString()}`;
  }
  const invitesData = await fetchWithFallback(invitesUrl, "/api/tender-invitations");
  const invites = Array.isArray(invitesData?.data) ? invitesData.data : [];
  invitesTotal = invites.length;
  for (const inv of invites) {
    // Normalize shape (proxy returns { invitation, tender })
    const status = ((inv.invitation?.ResponseStatus ?? inv.ResponseStatus) || "").toLowerCase();
    if (status === "pending") inviteBreakdown.pending += 1;
    else if (status === "accepted") inviteBreakdown.accepted += 1;
    else if (status === "declined") inviteBreakdown.declined += 1;
    else if (status === "submitted") inviteBreakdown.submitted += 1;
  }

  // 3) Tenders available (open + invited restricted)
  let tendersUrl: string | null = null;
  if (externalBase) {
    const api = new URL(`${externalBase}/api/tenders`);
    api.searchParams.set("enforce_invites", "true");
    if (thirdPartyId) api.searchParams.set("third_party_id", String(thirdPartyId));
    tendersUrl = api.toString();
  }
  const tendersData = await fetchWithFallback(tendersUrl, "/api/tenders");
  if (tendersData) {
    if (typeof tendersData.total === "number") tendersAvailable = tendersData.total;
    else if (Array.isArray(tendersData.data)) tendersAvailable = tendersData.data.length;
  }

  return NextResponse.json({
    summary: {
      activePrequalificationRequests: activePreq,
      directInvites: invitesTotal,
      availableTenders: tendersAvailable,
      completedRequests: completedPreq,
    },
    breakdowns: {
      prequalification: preqBreakdown,
      invitations: inviteBreakdown,
    },
  });
}



