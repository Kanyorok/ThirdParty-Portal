import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

type SearchResult = {
  type: "tender" | "rfq" | "document";
  id: string | number;
  title: string;
  description?: string;
  href?: string;
  meta?: Record<string, unknown>;
};

export async function GET(request: NextRequest) {
  const session = await getServerSession(authOptions);
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  const accessToken = (session as any).accessToken as string | undefined;
  const thirdPartyId = (session.user as any)?.thirdPartyId as number | undefined;
  const q = (request.nextUrl.searchParams.get('q') || '').trim();
  const limit = Math.max(1, Math.min(20, parseInt(request.nextUrl.searchParams.get('limit') || '10')));

  if (!q) return NextResponse.json({ data: [], total: 0 });

  const erpBase = process.env.ERP_BASE_URL || process.env.NEXT_PUBLIC_ERP_BASE_URL || process.env.NEXT_PUBLIC_EXTERNAL_API_URL || 'http://127.0.0.1:8000';
  const headers: HeadersInit = accessToken ? { 'Accept': 'application/json', 'Authorization': `Bearer ${accessToken}` } : { 'Accept': 'application/json' };

  const tasks: Array<Promise<SearchResult[]>> = [];

  tasks.push((async () => {
    try {
      const apiUrl = new URL(`${erpBase}/api/tenders`);
      apiUrl.searchParams.set('enforce_invites', 'true');
      if (thirdPartyId) apiUrl.searchParams.set('third_party_id', String(thirdPartyId));
      apiUrl.searchParams.set('search', q);
      const res = await fetch(apiUrl.toString(), { headers, signal: AbortSignal.timeout(8000) });
      if (!res.ok) return [];
      const data = await res.json();
      const items: any[] = Array.isArray(data?.data) ? data.data : [];
      return items.slice(0, limit).map((t) => ({
        type: 'tender' as const,
        id: t.id ?? t.Id,
        title: t.title ?? t.Title ?? `${t.tenderNo || ''} ${t.title || ''}`.trim(),
        description: t.scopeOfWork || t.instructions || '',
        href: `/dashboard/tenders?search=${encodeURIComponent(q)}`,
        meta: { tenderNo: t.tenderNo, status: t.status, submissionDeadline: t.submissionDeadline }
      }));
    } catch { return []; }
  })());

  tasks.push((async () => {
    try {
      const apiUrl = new URL(`${erpBase}/api/procurement/rfq-suppliers`);
      if (thirdPartyId) apiUrl.searchParams.set('third_party_id', String(thirdPartyId));
      const res = await fetch(apiUrl.toString(), { headers, signal: AbortSignal.timeout(8000) });
      if (!res.ok) return [];
      const data = await res.json();
      const items: any[] = Array.isArray(data?.data) ? data.data : [];
      const filtered = items.filter((r) => {
        const txt = `${r.number || ''} ${r.comments || ''}`.toLowerCase();
        return txt.includes(q.toLowerCase());
      });
      return filtered.slice(0, limit).map((r) => ({
        type: 'rfq' as const,
        id: r.rfqId ?? r.Id ?? r.id,
        title: r.number || `RFQ ${r.rfqId ?? ''}`,
        description: r.comments || '',
        href: `/dashboard/supplier/rfqs/${encodeURIComponent(r.rfqId ?? r.Id ?? r.id ?? '')}`,
        meta: { status: r.status, submissionDeadline: r.submissionDeadline }
      }));
    } catch { return []; }
  })());

  tasks.push((async () => {
    try {
      const origin = new URL(request.url).origin;
      const res = await fetch(`${origin}/api/dms/documents?q=${encodeURIComponent(q)}&limit=${limit}`, { headers, signal: AbortSignal.timeout(8000) });
      if (!res.ok) return [];
      const data = await res.json();
      const items: any[] = Array.isArray(data?.data) ? data.data : [];
      return items.slice(0, limit).map((d) => ({
        type: 'document' as const,
        id: d.id,
        title: d.name,
        description: `${d.repository || ''} • v${d.version ?? ''}`.trim(),
        href: d.previewUrl,
        meta: { mimeType: d.mimeType, size: d.size }
      }));
    } catch { return []; }
  })());

  const results = await Promise.allSettled(tasks);
  const flattened: SearchResult[] = results.flatMap((r) => r.status === 'fulfilled' ? r.value : []);
  const limited = flattened.slice(0, limit * 3);

  return NextResponse.json({ data: limited, total: limited.length });
}
