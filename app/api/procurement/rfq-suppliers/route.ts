import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

type AwardPayload = {
	rfqId: number
	supplierId: number
	status: "AWARDED"
	awardedOn: string
	comments?: string
}

export async function GET(request: NextRequest) {
	const session = await getServerSession(authOptions)

	if (!session?.accessToken) {
		return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
	}

	const search = request.nextUrl.searchParams.toString()
	const baseUrl = process.env.NEXT_PUBLIC_API_URL

	if (!baseUrl) {
		return NextResponse.json({ message: "API not configured" }, { status: 500 })
	}

	const targetUrl = `${baseUrl}/api/procurement/rfqs/invitations${search ? `?${search}` : ""}`

	try {
		const res = await fetch(targetUrl, {
			headers: {
				Accept: "application/json",
				Authorization: `Bearer ${session.accessToken}`,
			},
			cache: "no-store",
		})

		const contentType = res.headers.get("content-type") ?? ""
		const payload = contentType.includes("application/json")
			? await res.json()
			: await res.text()

		if (!res.ok) {
			return NextResponse.json(
				{
					message: (payload as any)?.message ?? "Failed to fetch RFQs",
					errors: (payload as any)?.errors,
				},
				{ status: res.status }
			)
		}

		return NextResponse.json(payload)
	} catch (error) {
		const message = error instanceof Error ? error.message : "Unknown error"
		return NextResponse.json(
			{ message: "Upstream request failed", error: message },
			{ status: 502 }
		)
	}
}

export async function POST(request: NextRequest) {
	let body: AwardPayload

	try {
		body = await request.json()
	} catch {
		return NextResponse.json({ message: "Invalid JSON body" }, { status: 400 })
	}

	if (
		!Number.isInteger(body.rfqId) ||
		!Number.isInteger(body.supplierId) ||
		body.status !== "AWARDED" ||
		typeof body.awardedOn !== "string"
	) {
		return NextResponse.json({ message: "Invalid payload" }, { status: 422 })
	}

	return NextResponse.json({ success: true })
}
