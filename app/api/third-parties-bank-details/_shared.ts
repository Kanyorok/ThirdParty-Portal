import { getServerSession } from "next-auth"

import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

export const BANK_DETAILS_ENDPOINT = "/api/third-parties-bank-details"

export type BankDetailsWritePayload = {
  ThirdPartyId: number
  BankID?: number
  BankName?: string
  Branch?: string
  SwiftCode?: string
  BranchID?: number
  BranchId?: number
  AccountNumber?: string
  CurrencyId?: number
}

export function getBaseApiUrl() {
  return process.env.NEXT_PUBLIC_API_URL || process.env.NEXT_PUBLIC_EXTERNAL_API_URL || getApiUrl()
}

export async function parseBody(res: Response) {
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

export async function getSessionContext() {
  const session = await getServerSession(authOptions)
  const accessToken = (session as any)?.accessToken as string | undefined
  const thirdPartyId = resolveThirdPartyId(session)

  if (!session || !accessToken || !thirdPartyId) return null
  return { accessToken, thirdPartyId }
}

export function buildWritePayload(input: unknown, thirdPartyId: number): BankDetailsWritePayload {
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
    BankID: normalizeNumber(payload.BankID ?? payload.bankId ?? payload.bankID ?? payload.bank_id),
    BankName: normalizeText(payload.BankName ?? payload.bankName),
    Branch: normalizeText(payload.Branch ?? payload.branch),
    SwiftCode: normalizeText(payload.SwiftCode ?? payload.swiftCode),
    BranchID: branchId,
    BranchId: branchId,
    AccountNumber: normalizeText(payload.accountNumber ?? payload.AccountNumber),
    CurrencyId: normalizeNumber(payload.currencyId ?? payload.CurrencyId),
  }
}

export function validateWritePayload(payload: BankDetailsWritePayload) {
  if (!payload.BankID) {
    return "Bank is required."
  }
  if (!payload.BranchID) {
    return "Branch is required."
  }

  if (!payload.AccountNumber) {
    return "Account number is required."
  }

  if (!payload.CurrencyId) {
    return "Currency is required."
  }

  return null
}
