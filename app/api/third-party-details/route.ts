import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";

const EXTERNAL_API_BASE_URL = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

/**
 * This works inside the bashboard (party details)
 */
interface BackendThirdPartyPayload {
    ThirdPartyName: string;
    TradingName: string | null;
    BusinessType: number;
    RegistrationNumber: string;
    TaxPIN: string;
    VATNumber: string | null;
    Country: string;
    PhysicalAddress: string;
    Email: string;
    Phone: string;
    Website: string | null;
}

interface ThirdPartyResponse {
    message?: string;
    errors?: Record<string, string[]>;
    userProfile?: {
        id: number;
        userId: string;
        firstName: string;
        lastName: string;
        fullName: string;
        email: string;
        phone: string;
        imageId: string | null;
        gender: string;
        thirdPartyId: number;
        isActive: boolean;
        isApproved: boolean;
        isSupplier: boolean;
        emailVerifiedOn: string | null;
        createdOn: string;
        modifiedOn: string | null;
        thirdParty: {
            id: number;
            thirdPartyName: string;
            tradingName: string | null;
            businessType: string;
            registrationNumber: string;
            taxPIN: string;
            vatNumber: string | null;
            country: string;
            physicalAddress: string;
            email: string;
            phone: string;
            website: string | null;
            approvalStatus: string;
            status: string;
            thirdPartyType: string;
            createdOn: string;
            modifiedOn: string | null;
            createdBy: number;
            isActive: boolean;
        };
    };
}

export enum BusinessTypeEnum {
    SoleProprietorship = 1,
    Partnership = 2,
    LimitedCompany = 3,
    NonProfit = 4,
    Other = 5,
}

function mapBusinessTypeTextToEnum(text: string): BusinessTypeEnum {
    switch (text.toLowerCase()) {
        case 'sole': return BusinessTypeEnum.SoleProprietorship;
        case 'partnership': return BusinessTypeEnum.Partnership;
        case 'limited': return BusinessTypeEnum.LimitedCompany;
        case 'non-profit': return BusinessTypeEnum.NonProfit;
        case 'other': return BusinessTypeEnum.Other;
        default: return BusinessTypeEnum.Other; // Fallback
    }
}

function mapApprovalStatusTextToEnum(text: string): number {
    return text.toUpperCase() === 'A' ? 1 : 0;
}

function mapStatusTextToEnum(text: string): number {
    return text.toUpperCase() === 'A' ? 1 : 0;
}

function mapThirdPartyTypeTextToEnum(text: string): number {
    return text.toUpperCase() === 'S' ? 1 : 0;
}


function transformToPascalCase(payload: any): BackendThirdPartyPayload {
    return {
        ThirdPartyName: payload.thirdPartyName,
        TradingName: payload.tradingName,
        BusinessType: payload.businessType,
        RegistrationNumber: payload.registrationNumber,
        TaxPIN: payload.taxPIN,
        VATNumber: payload.vatNumber,
        Country: payload.country,
        PhysicalAddress: payload.physicalAddress,
        Email: payload.email,
        Phone: payload.phone,
        Website: payload.website,
    };
}

export async function GET(req: NextRequest) {
    const session = await getServerSession(authOptions);
    if (!session || !session.accessToken || !session.user?.thirdParty?.id) {
        return NextResponse.json({ message: "Unauthorized or Missing ThirdPartyId in session" }, { status: 401 });
    }

    try {
        const res = await fetch(`${EXTERNAL_API_BASE_URL}/api/third-party-profile`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${session.accessToken}`,
            },
        });

        const rawData: ThirdPartyResponse = await res.json();

        if (!res.ok) {
            return NextResponse.json(rawData, { status: res.status });
        }

        if (!rawData.userProfile || !rawData.userProfile.thirdParty) {
            return NextResponse.json({ message: "Invalid response structure from backend: missing userProfile or thirdParty" }, { status: 500 });
        }

        const thirdPartyRawData = rawData.userProfile.thirdParty;

        const transformedThirdPartyData = {
            id: thirdPartyRawData.id,
            thirdPartyName: thirdPartyRawData.thirdPartyName,
            tradingName: thirdPartyRawData.tradingName,
            businessType: mapBusinessTypeTextToEnum(thirdPartyRawData.businessType),
            registrationNumber: thirdPartyRawData.registrationNumber,
            taxPIN: thirdPartyRawData.taxPIN,
            vatNumber: thirdPartyRawData.vatNumber,
            country: thirdPartyRawData.country,
            physicalAddress: thirdPartyRawData.physicalAddress,
            email: thirdPartyRawData.email,
            phone: thirdPartyRawData.phone,
            website: thirdPartyRawData.website,
            approvalStatus: mapApprovalStatusTextToEnum(thirdPartyRawData.approvalStatus),
            status: mapStatusTextToEnum(thirdPartyRawData.status),
            thirdPartyType: mapThirdPartyTypeTextToEnum(thirdPartyRawData.thirdPartyType),
            createdOn: thirdPartyRawData.createdOn,
            modifiedOn: thirdPartyRawData.modifiedOn,
        };

        return NextResponse.json(transformedThirdPartyData);
    } catch (error) {
        return NextResponse.json({ message: "Internal server error" }, { status: 500 });
    }
}

export async function PUT(req: NextRequest) {
    const session = await getServerSession(authOptions);
    if (!session || !session.accessToken || !session.user?.thirdParty?.id) {
        return NextResponse.json({ message: "Unauthorized or Missing ThirdPartyId in session" }, { status: 401 });
    }

    try {
        const frontendBody: any = await req.json();

        const backendBody = transformToPascalCase(frontendBody);

        const res = await fetch(`${EXTERNAL_API_BASE_URL}/api/third-party-profile`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${session.accessToken}`,
            },
            body: JSON.stringify(backendBody),
        });

        const data: ThirdPartyResponse = await res.json();

        if (!res.ok) {
            return NextResponse.json(data, { status: res.status });
        }

        return NextResponse.json(data, { status: 200 });
    } catch (error) {
        return NextResponse.json({ message: "Internal server error" }, { status: 500 });
    }
}