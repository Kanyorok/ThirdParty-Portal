// import { NextRequest, NextResponse } from "next/server";
// import { getServerSession } from "next-auth";
// import { authOptions } from "@/lib/auth-options";
// import { normalizeExternalProfileResponse, toPascalPayload } from "@/lib/third-party";

// const EXTERNAL_API_BASE_URL = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

// async function proxy(request: NextRequest, method: string) {
//     const session = await getServerSession(authOptions);
//     if (!session || !session.accessToken) return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
//     try {
//         const init: RequestInit = {
//             method,
//             headers: {
//                 Accept: "application/json",
//                 Authorization: `Bearer ${session.accessToken}`,
//             },
//         };
//         if (method === "PUT" || method === "PATCH" || method === "POST") {
//             const body = await request.json().catch(() => null);
//             const payload = toPascalPayload(body ?? {});
//             init.headers = { ...init.headers, "Content-Type": "application/json" };
//             init.body = JSON.stringify(payload);
//         }
//         const res = await fetch(`${EXTERNAL_API_BASE_URL}/api/third-party-profile`, init);
//         const body = await res.json().catch(() => null);
//         if (!res.ok) return NextResponse.json(body ?? { message: "External API error" }, { status: res.status });
//         const normalized = normalizeExternalProfileResponse(body);
//         return NextResponse.json({ success: true, userProfile: normalized ?? body });
//     } catch {
//         return NextResponse.json({ message: "Internal server error" }, { status: 500 });
//     }
// }

// export async function GET(request: NextRequest) {
//     return proxy(request, "GET");
// }

// export async function PUT(request: NextRequest) {
//     return proxy(request, "PUT");
// }

// export async function PATCH(request: NextRequest) {
//     return proxy(request, "PATCH");
// }

// export async function DELETE(request: NextRequest) {
//     const session = await getServerSession(authOptions);
//     if (!session || !session.accessToken) return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
//     try {
//         const res = await fetch(`${EXTERNAL_API_BASE_URL}/api/third-party-profile`, {
//             method: "DELETE",
//             headers: {
//                 Accept: "application/json",
//                 Authorization: `Bearer ${session.accessToken}`,
//             },
//         });
//         if (!res.ok) return NextResponse.json({ message: "Failed to delete" }, { status: res.status });
//         return new NextResponse(null, { status: 204 });
//     } catch {
//         return NextResponse.json({ message: "Internal server error" }, { status: 500 });
//     }
// }
