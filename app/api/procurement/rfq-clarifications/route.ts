import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

export async function POST(request: NextRequest) {
    const session = await getServerSession(authOptions);
    if (!session || !session.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }

    let payload: unknown;
    try {
        payload = await request.json();
    } catch {
        return NextResponse.json({ message: "Invalid JSON body" }, { status: 400 });
    }

    try {
        const res = await fetch(`${process.env.NEXTAUTH_URL}/api/procurement/rfq-clarifications`, {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "Authorization": `Bearer ${session.accessToken}`,
            },
            body: JSON.stringify(payload),
        });

        const text = await res.text();
        const contentType = res.headers.get("content-type") || "";
        const data = contentType.includes("application/json") ? JSON.parse(text || "{}") : text;

        if (!res.ok) {
            return NextResponse.json({ message: data?.message || "Failed to create clarification", errors: data?.errors }, { status: res.status });
        }

        return NextResponse.json(data);
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error";
        return NextResponse.json({ message: "Internal server error", error: message }, { status: 500 });
    }
}


