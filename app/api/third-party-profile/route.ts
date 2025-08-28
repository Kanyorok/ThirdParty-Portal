import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";

const EXTERNAL_API_BASE_URL = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

interface ProfileFullUpdatePayload {
    firstName: string;
    lastName: string;
    phone: string;
    email: string;
    gender?: string | null;
    imageId?: number | null;
    tradingName?: string;
    businessType?: string;
    registrationNumber?: string;
    taxPin?: string;
    vatNumber?: string;
    country?: string;
    physicalAddress?: string;
    website?: string | null;
}

interface ProfilePartialUpdatePayload {
    firstName?: string;
    lastName?: string;
    phone?: string;
    email?: string;
    gender?: string | null;
    imageId?: number | null;
    tradingName?: string;
    businessType?: string;
    registrationNumber?: string;
    taxPin?: string;
    vatNumber?: string;
    country?: string;
    physicalAddress?: string;
    website?: string | null;
}

interface ExternalProfileResponse {
    message?: string;
    success?: boolean;
    user_profile?: {
        id: number;
        firstName: string;
        lastName: string;
        phone: string;
        email: string;
        gender: string | null;
        imageId: number | null;
        tradingName: string;
        businessType: string;
        registrationNumber: string;
        taxPin: string;
        vatNumber: string;
        country: string;
        physicalAddress: string;
        website: string | null;
    };
    errors?: Record<string, string[]>;
}

async function handleRequest(request: NextRequest, method: string): Promise<NextResponse> {
    const session = await getServerSession(authOptions);

    if (!session || !session.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }

    try {
        let externalApiResponse: Response;
        let fetchOptions: RequestInit = {
            method: method,
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${session.accessToken}`,
            },
        };

        if (method === 'PUT' || method === 'PATCH' || method === 'POST') {
            try {
                const body = await request.json();
                let requestBody: Record<string, any> = {};

                if (method === 'PUT') {
                    requestBody = body as ProfileFullUpdatePayload;
                } else if (method === 'PATCH') {
                    const partialData = body as ProfilePartialUpdatePayload;
                    for (const key in partialData) {
                        if (Object.prototype.hasOwnProperty.call(partialData, key) && (partialData as any)[key] !== undefined) {
                            requestBody[key] = (partialData as any)[key];
                        }
                    }
                } else {
                    requestBody = body;
                }

                fetchOptions.headers = {
                    ...fetchOptions.headers,
                    'Content-Type': 'application/json',
                };
                fetchOptions.body = JSON.stringify(requestBody);

            } catch (jsonError) {
                console.error(`Error parsing JSON for ${method} request:`, jsonError);
                return NextResponse.json({ message: "Invalid JSON body" }, { status: 400 });
            }
        }

        externalApiResponse = await fetch(`${EXTERNAL_API_BASE_URL}/api/third-party-profile`, fetchOptions);

        if (!externalApiResponse.ok) {
            const errorData: ExternalProfileResponse = await externalApiResponse.json();
            const errorMessage = errorData.message || `Failed to ${method.toLowerCase()} profile`;

            return NextResponse.json({
                message: errorMessage,
                errors: errorData.errors || {}
            }, { status: externalApiResponse.status });
        }

        if (method === 'DELETE') {
            return new NextResponse(null, { status: 204 });
        }

        const data: ExternalProfileResponse = await externalApiResponse.json();

        return NextResponse.json({ success: true, userProfile: data.user_profile || data });

    } catch (error: unknown) {
        const errorMessage = error instanceof Error ? error.message : "An unknown error occurred";
        console.error("API Route Error:", errorMessage, error);
        return NextResponse.json({ message: "Internal server error" }, { status: 500 });
    }
}

export async function GET(request: NextRequest): Promise<NextResponse> {
    return handleRequest(request, 'GET');
}

export async function PUT(request: NextRequest): Promise<NextResponse> {
    return handleRequest(request, 'PUT');
}

export async function PATCH(request: NextRequest): Promise<NextResponse> {
    return handleRequest(request, 'PATCH');
}

export async function DELETE(request: NextRequest): Promise<NextResponse> {
    return handleRequest(request, 'DELETE');
}