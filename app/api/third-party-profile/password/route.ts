import { getServerSession } from "next-auth";
import { NextRequest, NextResponse } from "next/server";
import { authOptions } from "@/lib/auth-options";
import { getApiUrl } from "@/lib/config";

function getBaseApiUrl() {
    return process.env.NEXT_PUBLIC_EXTERNAL_API_URL || getApiUrl();
}

async function parseBody(res: Response) {
    const text = await res.text();
    if (!text) return null;
    try {
        return JSON.parse(text);
    } catch {
        return { message: text };
    }
}

export async function PUT(req: NextRequest) {
    const session = await getServerSession(authOptions);
    const accessToken = (session as any)?.accessToken as string | undefined;

    if (!session || !accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }

    try {
        const body = await req.json().catch(() => ({}));
        const currentPassword = body.current_password ?? body.currentPassword;
        const newPassword = body.new_password ?? body.newPassword;
        const newPasswordConfirmation =
            body.new_password_confirmation ??
            body.newPasswordConfirmation ??
            body.confirmNewPassword ??
            body.new_password ??
            body.newPassword;

        if (!currentPassword || !newPassword || !newPasswordConfirmation) {
            const errors: Record<string, string[]> = {};
            if (!currentPassword) errors.current_password = ["The current_password field is required."];
            if (!newPassword) errors.new_password = ["The new_password field is required."];
            if (!newPasswordConfirmation) errors.new_password_confirmation = ["The new_password_confirmation field is required."];

            return NextResponse.json({ message: "Validation failed.", errors }, { status: 422 });
        }

        const res = await fetch(`${getBaseApiUrl()}/api/third-party-profile/password`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${accessToken}`,
            },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                new_password_confirmation: newPasswordConfirmation,
            }),
            cache: "no-store",
        });

        const responseData = await parseBody(res);
        return NextResponse.json(responseData ?? { message: res.ok ? "Password updated successfully." : "Action failed." }, { status: res.status });

    } catch (error) {
        console.error("[Third Party Profile Password API] Error:", error);
        return NextResponse.json({ message: "An internal error occurred." }, { status: 500 });
    }
}
