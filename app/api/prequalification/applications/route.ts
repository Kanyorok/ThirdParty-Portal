import { apiFetch } from "@/lib/api-base";
import { NextResponse, type NextRequest } from "next/server";
import { z } from "zod";

const ResponseSchema = z.object({
    criteria_id: z.union([z.string(), z.number()]),
    response_text: z.string().nullable().optional()
});

const ApplicationSchema = z.object({
    round_id: z.union([z.string(), z.number()]),
    responses: z.array(ResponseSchema)
});

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

    const authorization = req.headers.get('authorization');

    if (!authorization) {
        return NextResponse.json(
            { message: "Authorization header is required" },
            { status: 401 }
        );
    }

    try {
        const data = await apiFetch(`/api/procurement/prequalification/applications`, {
            method: "POST",
            body: JSON.stringify(parsed.data),
            headers: {
                'Authorization': authorization,
                'Content-Type': 'application/json',
            }
        });

        return NextResponse.json(data, {
            status: 201,
            headers: { "Cache-Control": "no-store" }
        });
    } catch (err: unknown) {
        let errorMessage = "An unknown error occurred.";
        let statusCode = 500;

        if (err instanceof Error) {
            errorMessage = err.message;
            if (errorMessage.includes('401') || errorMessage.includes('unauthorized')) {
                statusCode = 401;
            } else if (errorMessage.includes('403') || errorMessage.includes('forbidden')) {
                statusCode = 403;
            }
        }

        return NextResponse.json(
            { message: "Failed to create application", error: errorMessage },
            { status: statusCode }
        );
    }
}