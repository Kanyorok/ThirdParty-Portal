import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "../../auth/[...nextauth]/route";

export async function GET(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);
    if (!session?.user) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const searchParams = request.nextUrl.searchParams;
    const q = searchParams.get('q') || '';
    const page = searchParams.get('page') || '1';
    const limit = searchParams.get('limit') || '20';

    const erpBase = process.env.ERP_BASE_URL || process.env.NEXT_PUBLIC_ERP_BASE_URL || 'http://127.0.0.1:8000';
    const apiUrl = new URL(`${erpBase}/api/dms/documents`);
    if (q) apiUrl.searchParams.set('q', q);
    apiUrl.searchParams.set('page', page);
    apiUrl.searchParams.set('limit', limit);

    const response = await fetch(apiUrl.toString(), {
      headers: {
        'Accept': 'application/json',
        'Authorization': session.accessToken ? `Bearer ${session.accessToken}` : ''
      },
      signal: AbortSignal.timeout(10000)
    });

    if (!response.ok) {
      let message = `HTTP ${response.status}`;
      try {
        const err = await response.json();
        message = err.message || err.error || message;
      } catch {}
      return NextResponse.json({ error: message }, { status: response.status });
    }

    const data = await response.json();
    return NextResponse.json(data);
  } catch (e: any) {
    return NextResponse.json({ error: e?.message || 'Failed to fetch documents' }, { status: 500 });
  }
}


