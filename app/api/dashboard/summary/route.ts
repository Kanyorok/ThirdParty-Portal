import { NextResponse } from "next/server"
import { getDashboardData } from "@/lib/dashboard-summary-data"

export async function GET() {
  const data = await getDashboardData()

  if (!data) {
    return NextResponse.json({ error: "Not Authorized!" }, { status: 401 })
  }

  return NextResponse.json(data)
}