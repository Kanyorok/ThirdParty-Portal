import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";

export async function POST(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);
    if (!session?.user) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });

    const { subject, message, category, pageUrl } = await request.json();
    if (!subject || !message) {
      return NextResponse.json({ error: 'Subject and message are required' }, { status: 400 });
    }

    const erpBase = process.env.ERP_BASE_URL || process.env.NEXT_PUBLIC_ERP_BASE_URL || process.env.NEXT_PUBLIC_EXTERNAL_API_URL || 'http://127.0.0.1:8000';
    const apiUrl = `${erpBase}/api/support/contact`;

    const res = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': session.accessToken ? `Bearer ${session.accessToken}` : ''
      },
      body: JSON.stringify({ subject, message, category, pageUrl }),
      signal: AbortSignal.timeout(10000)
    });

    const text = await res.text();
    let data: any = null;
    try { data = JSON.parse(text || '{}'); } catch {}
    if (!res.ok) {
      return NextResponse.json({ error: data?.message || data?.error || `HTTP ${res.status}` }, { status: res.status });
    }
    return NextResponse.json({ status: 'ok' });
  } catch (e: any) {
    return NextResponse.json({ error: e?.message || 'Failed to contact support' }, { status: 500 });
  }
}



