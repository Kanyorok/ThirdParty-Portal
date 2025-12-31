import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

export type PreqBreakdown = Record<"approved" | "submitted" | "under_review" | "rejected" | "not_applied", number>;
export type InvitationsBreakdown = Record<"pending" | "accepted" | "declined" | "submitted", number>;

const API_BASE = process.env.NEXT_PUBLIC_API_URL;

export async function getDashboardData() {
    const session = await getServerSession(authOptions);
    if (!session?.user?.thirdPartyId) return null;

    const { accessToken } = session as any;
    const thirdPartyId = session.user.thirdPartyId;
    const headers = {
        "Accept": "application/json",
        ...(accessToken && { "Authorization": `Bearer ${accessToken}` }),
    };

    try {
        const [preqRes, invitesRes, tendersRes] = await Promise.allSettled([
            fetch(`${API_BASE}/api/prequalification/rounds`, { headers, next: { revalidate: 60 } }).then(r => r.json()),
            fetch(`${API_BASE}/api/tender-invitations?third_party_id=${thirdPartyId}`, { headers, cache: 'no-store' }).then(r => r.json()),
            fetch(`${API_BASE}/api/tenders?enforce_invites=true&third_party_id=${thirdPartyId}`, { headers, cache: 'no-store' }).then(r => r.json()),
        ]);

        let activePreq = 0;
        let completedPreq = 0;
        const preqBreakdown: PreqBreakdown = { approved: 0, submitted: 0, under_review: 0, rejected: 0, not_applied: 0 };
        const inviteBreakdown: InvitationsBreakdown = { pending: 0, accepted: 0, declined: 0, submitted: 0 };

        if (preqRes.status === 'fulfilled' && Array.isArray(preqRes.value?.data)) {
            preqRes.value.data.forEach((round: any) => {
                round.categories?.forEach((c: any) => {
                    const status = (c.status || "").toUpperCase();
                    if (!(c.hasApplied ?? c.has_applied)) {
                        preqBreakdown.not_applied++;
                    } else {
                        if (status === "APPROVED") { preqBreakdown.approved++; completedPreq++; }
                        else if (status === "REJECTED") preqBreakdown.rejected++;
                        else {
                            if (status === "UNDER_REVIEW") preqBreakdown.under_review++;
                            else if (status === "SUBMITTED") preqBreakdown.submitted++;
                            activePreq++;
                        }
                    }
                });
            });
        }

        const inviteData = invitesRes.status === 'fulfilled' ? invitesRes.value?.data : [];
        if (Array.isArray(inviteData)) {
            inviteData.forEach((inv: any) => {
                const status = (inv.invitation?.ResponseStatus ?? inv.ResponseStatus ?? "").toLowerCase();
                if (status in inviteBreakdown) inviteBreakdown[status as keyof InvitationsBreakdown]++;
            });
        }

        const tenderVal = tendersRes.status === 'fulfilled' ? tendersRes.value : null;
        const tendersAvailable = tenderVal?.total ?? (Array.isArray(tenderVal?.data) ? tenderVal.data.length : 0);

        return {
            summary: { activePreq, directInvites: inviteData.length, tendersAvailable, completedPreq },
            breakdowns: { prequalification: preqBreakdown, invitations: inviteBreakdown }
        };
    } catch (error) {
        return null;
    }
}