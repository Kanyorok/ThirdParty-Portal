import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";

const EXTERNAL_API_BASE_URL = process.env.EXTERNAL_API_URL;

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

export async function PUT(req: NextRequest, context: { params: { id: string } }) {
    const session = await getServerSession(authOptions);
    if (!session || !session.accessToken || !session.user?.thirdParty?.id) {
        return NextResponse.json(
            { message: "Unauthorized or Missing ThirdPartyId in session" },
            { status: 401 }
        );
    }

    const { id } = context.params;

    try {
        const frontendBody: FrontendBankDetailPayload = await req.json();
        frontendBody.thirdPartyId = session.user.thirdParty.id;

        const backendBody = transformToPascalCase(frontendBody);

        const res = await fetch(`${EXTERNAL_API_BASE_URL}/api/third-parties-bank-details/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${session.accessToken}`,
            },
            body: JSON.stringify(backendBody),
        });

        const data: BankDetailResponse = await res.json();

        if (!res.ok) {
            return NextResponse.json(data, { status: res.status });
        }

        return NextResponse.json(data);
    } catch (error) {
        return NextResponse.json({ message: "Internal server error" }, { status: 500 });
    }
}

export async function DELETE(req: NextRequest, context: { params: { id: string } }) {
    const session = await getServerSession(authOptions);
    if (!session || !session.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }

    const { id } = context.params;

    try {
        const res = await fetch(`${EXTERNAL_API_BASE_URL}/api/third-parties-bank-details/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${session.accessToken}`,
            },
        });

        if (!res.ok) {
            const data: BankDetailResponse = await res.json();
            return NextResponse.json(data, { status: res.status });
        }

        return new NextResponse(null, { status: 204 });
    } catch {
        return NextResponse.json({ message: "Internal server error" }, { status: 500 });
    }
}
