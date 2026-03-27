import { NextResponse, type NextRequest } from "next/server";
import { getServerSession } from "next-auth/next";
import { authOptions } from "@/lib/auth-options";
import { z } from "zod";

const API_BASE = process.env.NEXT_PUBLIC_API_URL;
const PREQ_APPLICATIONS_PATH =
    process.env.PREQUALIFICATION_APPLICATIONS_PATH ||
    "/api/v1/supplier/prequalification/applications";
const PREQ_APPLICATIONS_FALLBACK_PATH =
    process.env.PREQUALIFICATION_APPLICATIONS_FALLBACK_PATH ||
    "/api/procurement/prequalification/applications";

const IdentifierSchema = z.union([z.string(), z.number()]);

const ResponseSchema = z.object({
    criteria_id: IdentifierSchema,
    response_text: z.string().nullable().optional()
});

const ApplicationSchema = z.object({
    round_id: IdentifierSchema,
    category_ids: z.array(IdentifierSchema).optional(),
    responses: z.array(ResponseSchema).optional()
});

function toPositiveInt(value: string | number) {
    const num = Number(value);
    return Number.isInteger(num) && num > 0 ? num : null;
}

function joinUrl(base: string, path: string) {
    const cleanBase = base.replace(/\/+$/, "");
    const cleanPath = path.startsWith("/") ? path : `/${path}`;
    return `${cleanBase}${cleanPath}`;
}

async function postUpstream(url: string, authorization: string, payload: unknown) {
    const res = await fetch(url, {
        method: "POST",
        headers: {
            "Accept": "application/json",
            "Content-Type": "application/json",
            "Authorization": authorization,
        },
        body: JSON.stringify(payload),
        cache: "no-store",
    });

    const raw = await res.text();
    const contentType = res.headers.get("content-type") || "";
    let data: unknown = null;

    if (contentType.includes("application/json")) {
        try {
            data = JSON.parse(raw || "{}");
        } catch {
            data = null;
        }
    }

    return { status: res.status, ok: res.ok, data, raw, url };
}

export async function POST(req: NextRequest) {
    let body;
    try {
        body = await req.json();
    } catch {
        return NextResponse.json({ message: "Invalid JSON" }, { status: 400 });
    }

    const parsed = ApplicationSchema.safeParse(body);
    if (!parsed.success) {
        return NextResponse.json(
            { message: "Validation failed", issues: parsed.error.format() },
            { status: 422 }
        );
    }

    if (!API_BASE) {
        return NextResponse.json({ message: "API not configured" }, { status: 500 });
    }

    const session = await getServerSession(authOptions);
    const authorization = session?.accessToken
        ? `Bearer ${session.accessToken}`
        : req.headers.get('authorization');

    if (!authorization) {
        return NextResponse.json(
            { message: "Unauthorized" },
            { status: 401 }
        );
    }

    const roundId = toPositiveInt(parsed.data.round_id);
    const categoryIds = (parsed.data.category_ids ?? [])
        .map((id) => toPositiveInt(id))
        .filter((id): id is number => id !== null);

    if (!roundId) {
        return NextResponse.json(
            { message: "Validation failed", issues: { round_id: ["round_id must be a positive integer"] } },
            { status: 422 }
        );
    }

    const normalizedPayload: Record<string, unknown> = { round_id: roundId };
    if (categoryIds.length > 0) normalizedPayload.category_ids = categoryIds;
    if (Array.isArray(parsed.data.responses) && parsed.data.responses.length > 0) {
        normalizedPayload.responses = parsed.data.responses;
    }

    if (!normalizedPayload.category_ids && !normalizedPayload.responses) {
        return NextResponse.json(
            {
                message: "Validation failed",
                issues: { category_ids: ["Provide at least one category or response payload"] },
            },
            { status: 422 }
        );
    }

    try {
        const primaryUrl = joinUrl(API_BASE, PREQ_APPLICATIONS_PATH);
        const fallbackUrl = joinUrl(API_BASE, PREQ_APPLICATIONS_FALLBACK_PATH);

        let result = await postUpstream(primaryUrl, authorization, normalizedPayload);
        if (result.status === 404 || result.status === 405 || result.status === 501) {
            result = await postUpstream(fallbackUrl, authorization, normalizedPayload);
        } else if (!result.ok && result.status >= 500 && fallbackUrl !== primaryUrl) {
            const fallbackResult = await postUpstream(fallbackUrl, authorization, normalizedPayload);
            if (fallbackResult.ok || fallbackResult.status < 500) {
                result = fallbackResult;
            }
        }

        const payload =
            result.data && typeof result.data === "object"
                ? result.data
                : {
                    message: result.raw || "Failed to create application",
                    upstreamUrl: result.url,
                };

        return NextResponse.json(payload, {
            status: result.status,
            headers: { "Cache-Control": "no-store" },
        });
    } catch (err: unknown) {
        return NextResponse.json(
            {
                message: "Failed to create application",
                error: err instanceof Error ? err.message : String(err),
            },
            { status: 502 }
        );
    }
}
