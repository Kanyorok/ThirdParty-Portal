import { getToken } from "next-auth/jwt";
import { NextRequest, NextResponse } from "next/server";

export async function PUT(req: NextRequest) {
    const token = await getToken({ req });
    if (!token || !token.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }

    try {
        const body = await req.json();
        const { currentPassword, newPassword } = body;

        const res = await fetch(`${process.env.EXTERNAL_API_URL}/api/third-party-profile/password`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token.accessToken}`,
            },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                new_password_confirmation: newPassword,
            }),
        });

        const responseData = await res.json();

        if (!res.ok) {
            return NextResponse.json(
                { message: responseData.message || "Failed to update password.", errors: responseData.errors },
                { status: res.status }
            );
        }

        return NextResponse.json({ message: responseData.message || "Password updated successfully." });

    } catch (error) {
        return NextResponse.json({ message: "An internal error occurred." }, { status: 500 });
    }
}