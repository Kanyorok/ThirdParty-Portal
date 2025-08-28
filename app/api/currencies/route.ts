import { NextResponse } from 'next/server';

const API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL

export async function GET() {
    try {
        const response = await fetch(`${API_BASE}/api/v1/currencies`, {
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            next: { revalidate: 60 }
        });

        if (!response.ok) {
            const errorData = await response.json();
            return new NextResponse(JSON.stringify(errorData), {
                status: response.status,
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
            });
        }

        const data = await response.json();
        return NextResponse.json(data);
    } catch (error) {
        console.error('Error fetching currencies from Laravel:', error);
        return new NextResponse(JSON.stringify({ message: 'Internal server error while fetching currencies.' }), {
            status: 500,
            headers: {
                'Content-Type': 'application/json',
            },
        });
    }
}