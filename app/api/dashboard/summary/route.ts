import { NextResponse } from "next/server"
import { getDashboardData } from "@/lib/dashboard-summary-data"

export async function GET() {
  const data = await getDashboardData()

  if (!data) {
    return NextResponse.json({ error: "Unauthorized or Error" }, { status: 401 })
  }

  return NextResponse.json(data)
}