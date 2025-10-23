import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth/next";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";

const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

// Proxy: POST upload document
export async function POST(req: NextRequest, { params }: { params: Promise<{ roundId: string; categoryId: string }> }) {
  const session = await getServerSession(authOptions);
  if (!session?.accessToken) {
    return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
  }

  const { roundId, categoryId } = await params;
  if (!roundId || !categoryId) {
    return NextResponse.json({ message: "RoundId and CategoryId are required" }, { status: 400 });
  }

  try {
    const formData = await req.formData();
    const res = await fetch(
      `${EXTERNAL_API_BASE}/api/procurement/prequalification/applications/${encodeURIComponent(roundId)}/categories/${encodeURIComponent(categoryId)}/documents`,
      {
        method: "POST",
        headers: { Authorization: `Bearer ${session.accessToken}` },
        body: formData,
      }
    );
    // try to parse JSON; if fails, forward empty
    const data = await res.json().catch(() => null);
    return NextResponse.json(data ?? {}, { status: res.status });
  } catch (err: unknown) {
    return NextResponse.json({ message: "Failed to upload document", error: err instanceof Error ? err.message : String(err) }, { status: 500 });
  }
}
