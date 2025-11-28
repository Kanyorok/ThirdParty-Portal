// import { NextRequest, NextResponse } from "next/server";
// import { getServerSession } from "next-auth";
// import { authOptions } from "@/lib/auth-options";
// import { normalizeExternalProfileResponse, toPascalPayload } from "@/lib/third-party";

// const EXTERNAL_API_BASE_URL = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

// export async function GET(req: NextRequest) {
//     const session = await getServerSession(authOptions);
//     if (!session || !session.accessToken) return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
//     try {
//         const res = await fetch(`${EXTERNAL_API_BASE_URL}/api/third-party-profile`, {
//             method: "GET",
//             headers: {
//                 Accept: "application/json",
//                 Authorization: `Bearer ${session.accessToken}`,
//             },
//         });
//         const body = await res.json().catch(() => null);
//         if (!res.ok) return NextResponse.json(body ?? { message: "Failed to fetch" }, { status: res.status });
//         const normalized = normalizeExternalProfileResponse(body);
//         if (!normalized) return NextResponse.json({ message: "Malformed response" }, { status: 502 });
//         return NextResponse.json(normalized);
//     } catch (e) {
//         return NextResponse.json({ message: "Internal server error" }, { status: 500 });
//     }
// }

// export async function PUT(req: NextRequest) {
//     const session = await getServerSession(authOptions);
//     if (!session || !session.accessToken) return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
//     try {
//         const frontendBody = await req.json();
//         const backendBody = toPascalPayload(frontendBody);
//         const res = await fetch(`${EXTERNAL_API_BASE_URL}/api/third-party-profile`, {
//             method: "PUT",
//             headers: {
//                 "Content-Type": "application/json",
//                 Accept: "application/json",
//                 Authorization: `Bearer ${session.accessToken}`,
//             },
//             body: JSON.stringify(backendBody),
//         });
//         const responseBody = await res.json().catch(() => null);
//         if (!res.ok) return NextResponse.json(responseBody ?? { message: "Failed to update" }, { status: res.status });
//         const normalized = normalizeExternalProfileResponse(responseBody);
//         return NextResponse.json({ success: true, data: normalized ?? responseBody });
//     } catch (e) {
//         return NextResponse.json({ message: "Internal server error" }, { status: 500 });
//     }
// }
