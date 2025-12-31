import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth/next";
import { authOptions } from "@/lib/auth-options";

export async function POST(req: NextRequest) {
  const session = await getServerSession(authOptions);
  if (!session?.accessToken) {
    return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
  }
  const url = new URL(req.url);
  const parts = url.pathname.split("/");
  const categoryId = parts[parts.length - 2];
  const roundId = parts[parts.indexOf("applications") + 1];
  if (!roundId || !categoryId) {
    return NextResponse.json({ message: "RoundId and CategoryId are required" }, { status: 400 });
  }

  try {
    const formData = await req.formData();
    const res = await fetch(
      `${process.env.NEXTAUTH_URL}/api/procurement/prequalification/applications/${encodeURIComponent(roundId)}/categories/${encodeURIComponent(categoryId)}/documents`,
      {
        method: "POST",
        headers: { Authorization: `Bearer ${session.accessToken}` },
        body: formData,
      }
    );
    const data = await res.json().catch(() => null);
    return NextResponse.json(data ?? {}, { status: res.status });
  } catch (err: unknown) {
    return NextResponse.json({ message: "Failed to upload document", error: err instanceof Error ? err.message : String(err) }, { status: 500 });
  }
}
