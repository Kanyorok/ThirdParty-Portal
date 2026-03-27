import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"

import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

const BANK_DETAILS_ENDPOINT = "/api/third-parties-bank-details"

type BankDetailsWritePayload = {
  ThirdPartyId: number
  BankName?: string
  Branch?: string
  SwiftCode?: string
  BranchID?: number
  BranchId?: number
  AccountNumber?: string
  CurrencyId?: number
}

function getBaseApiUrl() {
  return process.env.NEXT_PUBLIC_API_URL || process.env.NEXT_PUBLIC_EXTERNAL_API_URL || getApiUrl()
}

async function parseBody(res: Response) {
  const text = await res.text().catch(() => "")
  if (!text) return null

  try {
    return JSON.parse(text)
  } catch {
    return { message: text }
  }
}

function normalizeText(value: unknown) {
  if (typeof value !== "string") return undefined
  const trimmed = value.trim()
  return trimmed.length > 0 ? trimmed : undefined
}

function normalizeNumber(value: unknown) {
  const next = Number(value)
  return Number.isFinite(next) && next > 0 ? next : undefined
}

function resolveThirdPartyId(session: unknown) {
  const user = (session as any)?.user as Record<string, unknown> | undefined
  const fromCamel = Number(user?.thirdPartyId ?? 0)
  if (Number.isFinite(fromCamel) && fromCamel > 0) return fromCamel

  const fromSnake = Number(user?.third_party_id ?? 0)
  if (Number.isFinite(fromSnake) && fromSnake > 0) return fromSnake

  return null
}

async function getSessionContext() {
  const session = await getServerSession(authOptions)
  const accessToken = (session as any)?.accessToken as string | undefined
  const thirdPartyId = resolveThirdPartyId(session)

  if (!session || !accessToken || !thirdPartyId) return null
  return { accessToken, thirdPartyId }
}

function buildWritePayload(input: unknown, thirdPartyId: number): BankDetailsWritePayload {
  const payload = (input && typeof input === "object" ? input : {}) as Record<string, unknown>
  const branchId = normalizeNumber(
    payload.BranchID ??
    payload.BranchId ??
    payload.branchId ??
    payload.branchID ??
    payload.branch_id,
  )

  return {
    ThirdPartyId: thirdPartyId,
    BankName: normalizeText(payload.BankName ?? payload.bankName),
    Branch: normalizeText(payload.Branch ?? payload.branch),
    SwiftCode: normalizeText(payload.SwiftCode ?? payload.swiftCode),
    BranchID: branchId,
    BranchId: branchId,
    AccountNumber: normalizeText(payload.accountNumber ?? payload.AccountNumber),
    CurrencyId: normalizeNumber(payload.currencyId ?? payload.CurrencyId),
  }
}

export async function PUT(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> },
) {
  const context = await getSessionContext()
  if (!context) {
    return NextResponse.json({ message: "Unauthorized or missing third-party context." }, { status: 401 })
  }

  try {
    const { id } = await params
    if (!id) {
      return NextResponse.json({ message: "Bank detail id is required." }, { status: 400 })
    }

    const requestBody = await request.json().catch(() => ({}))
    const payload = buildWritePayload(requestBody, context.thirdPartyId)

    const requestUrl = new URL(`${getBaseApiUrl()}${BANK_DETAILS_ENDPOINT}/${encodeURIComponent(id)}`)
    requestUrl.searchParams.set("ThirdPartyId", String(context.thirdPartyId))

    const res = await fetch(requestUrl.toString(), {
      method: "PUT",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${context.accessToken}`,
      },
      body: JSON.stringify(payload),
      cache: "no-store",
    })

    const body = await parseBody(res)
    return NextResponse.json(body ?? { message: "Failed to update bank detail." }, { status: res.status })
  } catch (error) {
    console.error("[Third Party Bank Details API] PUT error:", error)
    return NextResponse.json({ message: "Internal server error" }, { status: 500 })
  }
}

export async function DELETE(
  _request: NextRequest,
  { params }: { params: Promise<{ id: string }> },
) {
  const context = await getSessionContext()
  if (!context) {
    return NextResponse.json({ message: "Unauthorized or missing third-party context." }, { status: 401 })
  }

  try {
    const { id } = await params
    if (!id) {
      return NextResponse.json({ message: "Bank detail id is required." }, { status: 400 })
    }

    const requestUrl = new URL(`${getBaseApiUrl()}${BANK_DETAILS_ENDPOINT}/${encodeURIComponent(id)}`)
    requestUrl.searchParams.set("ThirdPartyId", String(context.thirdPartyId))

    const res = await fetch(requestUrl.toString(), {
      method: "DELETE",
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${context.accessToken}`,
      },
      cache: "no-store",
    })

    if (res.status === 204) {
      return new NextResponse(null, { status: 204 })
    }

    const body = await parseBody(res)
    return NextResponse.json(body ?? { message: "Failed to delete bank detail." }, { status: res.status })
  } catch (error) {
    console.error("[Third Party Bank Details API] DELETE error:", error)
    return NextResponse.json({ message: "Internal server error" }, { status: 500 })
  }
}
