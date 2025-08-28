import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";

const EXTERNAL_API_BASE_URL = process.env.EXTERNAL_API_URL;

interface FrontendBankDetailPayload {
    thirdPartyId: number;
    bankName: string;
    branch: string;
    accountNumber: string;
    currencyId: number;
    swiftCode?: string | null;
}

interface BackendBankDetailPayload {
    ThirdPartyId: number;
    BankName: string;
    Branch: string;
    AccountNumber: string;
    CurrencyId: number;
    SwiftCode?: string | null;
}

interface BankDetailResponse {
    message?: string;
    errors?: Record<string, string[]>;
    bankDetails?: any[];
    bankDetail?: any;
    data?: any;
}

function transformToPascalCase(payload: FrontendBankDetailPayload): BackendBankDetailPayload {
    return {
        ThirdPartyId: payload.thirdPartyId,
        BankName: payload.bankName,
        Branch: payload.branch,
        AccountNumber: payload.accountNumber,
        CurrencyId: payload.currencyId,
        SwiftCode: payload.swiftCode,
    };
}

export async function GET(req: NextRequest) {
    const session = await getServerSession(authOptions);
    if (!session || !session.accessToken || !session.user?.thirdParty?.id) {
        return NextResponse.json({ message: "Unauthorized or Missing ThirdPartyId in session" }, { status: 401 });
    }

    const { searchParams } = new URL(req.url);
    const thirdPartyId = session.user.thirdParty.id; // Use thirdPartyId from session for security

    try {
        const res = await fetch(`${EXTERNAL_API_BASE_URL}/api/third-parties-bank-details?ThirdPartyId=${thirdPartyId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${session.accessToken}`,
            },
        });

        const data: BankDetailResponse = await res.json();

        if (!res.ok) {
            console.error("Laravel API Error (GET bank details):", data);
            return NextResponse.json(data, { status: res.status });
        }

        return NextResponse.json(data);
    } catch (error) {
        console.error("API Route Error (GET bank details):", error);
        return NextResponse.json({ message: "Internal server error" }, { status: 500 });
    }
}

export async function POST(req: NextRequest) {
    const session = await getServerSession(authOptions);
    if (!session || !session.accessToken || !session.user?.thirdParty?.id) {
        return NextResponse.json({ message: "Unauthorized or Missing ThirdPartyId in session" }, { status: 401 });
    }

    try {
        const frontendBody: FrontendBankDetailPayload = await req.json();
        frontendBody.thirdPartyId = session.user.thirdParty.id;

        const backendBody = transformToPascalCase(frontendBody);

        const res = await fetch(`${EXTERNAL_API_BASE_URL}/api/third-parties-bank-details`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${session.accessToken}`,
            },
            body: JSON.stringify(backendBody),
        });

        const data: BankDetailResponse = await res.json();

        if (!res.ok) {
            console.error("Laravel API Error (POST bank details):", data);
            return NextResponse.json(data, { status: res.status });
        }

        return NextResponse.json(data, { status: 201 });
    } catch (error) {
        console.error("API Route Error (POST bank details):", error);
        return NextResponse.json({ message: "Internal server error" }, { status: 500 });
    }
}