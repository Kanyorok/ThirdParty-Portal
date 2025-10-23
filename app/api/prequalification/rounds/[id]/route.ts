import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth/next";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";

const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

// In Next 15+, dynamic route params must be awaited
export async function GET(_request: NextRequest, { params }: { params: Promise<{ id: string }> }) {
  const session = await getServerSession(authOptions);
  if (!session?.accessToken) {
    return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
  }

  const { id } = await params;
  if (!id) {
    return NextResponse.json({ message: "Round id is required" }, { status: 400 });
  }

  try {
    const res = await fetch(`${EXTERNAL_API_BASE}/api/prequalification/rounds/${encodeURIComponent(id)}` , {
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${session.accessToken}`,
      },
      // Always fresh to reflect live round detail (sections/criteria)
      cache: "no-store",
    });

    const data = await res.json().catch(() => null);
    if (!res.ok) {
      return NextResponse.json(data || { message: "Failed to fetch round" }, { status: res.status });
    }

    return NextResponse.json(data ?? { data: null }, { status: 200, headers: { "Cache-Control": "no-store" } });
  } catch (err: unknown) {
    return NextResponse.json({ message: "Failed to fetch round", error: err instanceof Error ? err.message : String(err) }, { status: 500 });
  }
}
