import { NextResponse } from 'next/server'

// Minimal handler to satisfy Next 15 typed-route requirements
export async function GET() {
	return NextResponse.json({ message: 'Dashboard data API' })
}
