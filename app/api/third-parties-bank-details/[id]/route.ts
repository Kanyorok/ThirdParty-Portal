import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

interface FrontendBankDetailPayload {
    bankName?: string;
    branch?: string;
    accountNumber?: string;
    currencyId?: number;
    swiftCode?: string | null;
    thirdPartyId?: number;
}

interface BackendBankDetailPayload {
    BankName?: string;
    Branch?: string;
    AccountNumber?: string;
    CurrencyId?: number;
    SwiftCode?: string | null;
    ThirdPartyId?: number;
}

interface BankDetailResponse {
    message?: string;
    errors?: Record<string, string[]>;
    bankDetail?: any;
    data?: any;
}

function transformToPascalCase(payload: FrontendBankDetailPayload): BackendBankDetailPayload {
    const transformed: BackendBankDetailPayload = {};
    if (payload.bankName !== undefined) transformed.BankName = payload.bankName;
    if (payload.branch !== undefined) transformed.Branch = payload.branch;
    if (payload.accountNumber !== undefined) transformed.AccountNumber = payload.accountNumber;
    if (payload.currencyId !== undefined) transformed.CurrencyId = payload.currencyId;
    if (payload.swiftCode !== undefined) transformed.SwiftCode = payload.swiftCode;
    if (payload.thirdPartyId !== undefined) transformed.ThirdPartyId = payload.thirdPartyId;
    return transformed;
}

export async function PUT(
    req: NextRequest,
    { params }: { params: Promise<{ id: string }> }
) {
    const session = await getServerSession(authOptions);
    const accessToken = (session as any)?.accessToken as string | undefined
    const thirdPartyId =
        Number((session?.user as any)?.thirdPartyId ?? (session?.user as any)?.third_party_id ?? 0) || undefined

    if (!session || !accessToken || !thirdPartyId) {
        return NextResponse.json(
            { message: "Unauthorized or Missing ThirdPartyId in session" },
            { status: 401 }
        );
    }

    const { id } = await params;

    try {
        const frontendBody: FrontendBankDetailPayload = await req.json();
        frontendBody.thirdPartyId = thirdPartyId;

        const backendBody = transformToPascalCase(frontendBody);

        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/third-parties-bank-details/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${accessToken}`,
            },
            body: JSON.stringify(backendBody),
        });

        const data: BankDetailResponse = await res.json();

        if (!res.ok) {
            console.error('Backend error (PUT):', data);
            return NextResponse.json(data, { status: res.status });
        }

        return NextResponse.json(data);
    } catch (error) {
        console.error('API Route Error (PUT bank details):', error);
        return NextResponse.json({ message: "Internal server error" }, { status: 500 });
    }
}

export async function DELETE(
    _req: NextRequest,
    { params }: { params: Promise<{ id: string }> }
) {
    const session = await getServerSession(authOptions);
    const accessToken = (session as any)?.accessToken as string | undefined
    if (!session || !accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }

    const { id } = await params;

    try {
        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/third-parties-bank-details/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${accessToken}`,
            },
        });

        if (!res.ok) {
            const data: BankDetailResponse = await res.json();
            console.error('Backend error (DELETE):', data);
            return NextResponse.json(data, { status: res.status });
        }

        return new NextResponse(null, { status: 204 });
    } catch (error) {
        console.error('API Route Error (DELETE bank details):', error);
        return NextResponse.json({ message: "Internal server error" }, { status: 500 });
    }
}
