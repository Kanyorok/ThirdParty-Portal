import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";

const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;
const SUPPLIER_PORTAL_API_KEY = process.env.SUPPLIER_PORTAL_API_KEY;

// Temporary in-memory store (replace with DB integration)
const rfqAwards = new Map<string, { rfqId: number; supplierId: number; status: string; awardedOn: string; comments?: string }>();

export async function GET(request: NextRequest) {
	const session = await getServerSession(authOptions);
	if (!session || !session.accessToken) {
		return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
	}

	const search = request.nextUrl.searchParams.toString();
	const targetUrl = `${EXTERNAL_API_BASE}/api/procurement/rfq-suppliers${search ? `?${search}` : ""}`;

	try {
		const res = await fetch(targetUrl, {
			method: "GET",
			headers: {
				"Accept": "application/json",
				"Authorization": `Bearer ${session.accessToken}`,
			},
			cache: "no-store",
		});

		const bodyText = await res.text();
		const contentType = res.headers.get("content-type") || "";
		const data = contentType.includes("application/json") ? JSON.parse(bodyText || "{}") : bodyText;

		if (!res.ok) {
			return NextResponse.json({ message: (data as any)?.message || "Failed to fetch RFQ invitations", errors: (data as any)?.errors }, { status: res.status });
		}

		return NextResponse.json(data);
	} catch (error: unknown) {
		const message = error instanceof Error ? error.message : "Unknown error";
		return NextResponse.json({ message: "Internal server error", error: message }, { status: 500 });
	}
}

export async function POST(request: NextRequest) {
	const apiKey = request.headers.get("x-api-key") || request.headers.get("X-API-Key");
	if (!SUPPLIER_PORTAL_API_KEY || apiKey !== SUPPLIER_PORTAL_API_KEY) {
		return NextResponse.json({ message: "Forbidden" }, { status: 403 });
	}
	let payload: unknown;
	try {
		payload = await request.json();
	} catch {
		return NextResponse.json({ message: "Invalid JSON body" }, { status: 400 });
	}
	const body = payload as { rfqId?: number; supplierId?: number; status?: string; awardedOn?: string; comments?: string };
	if (!body.rfqId || !body.supplierId || !body.status || !body.awardedOn) {
		return NextResponse.json({ message: "Missing required fields" }, { status: 422 });
	}
	// Basic validation
	if (body.status !== "Awarded" && body.status !== "AWARDED") {
		return NextResponse.json({ message: "Unsupported status" }, { status: 422 });
	}
	const key = `${body.rfqId}:${body.supplierId}`;
	rfqAwards.set(key, { rfqId: body.rfqId, supplierId: body.supplierId, status: "Awarded", awardedOn: body.awardedOn, comments: body.comments });
	return NextResponse.json({ ok: true });
}


