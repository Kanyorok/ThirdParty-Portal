import { NextResponse } from "next/server"
import { getServerSession } from "next-auth"

import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

const BRANCH_LOOKUP_ENDPOINTS = [
  "/api/v1/portal/auth/metadata/code-details/Branch",
  "/api/v1/portal/auth/metadata/code-details/BankBranch",
  "/api/v1/portal/auth/metadata/code-details/Branches",
  "/api/v1/portal/auth/metadata/code-details/BankBranches",
] as const

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
  const text = String(value ?? "").trim()
  return text.length > 0 ? text : undefined
}

function normalizeBranchOptions(payload: unknown) {
  const body = payload as any
  const candidates = [body?.data, body?.items, body]

  for (const candidate of candidates) {
    if (!Array.isArray(candidate)) continue

    const options = candidate
      .map((entry: any) => {
        const id = Number(
          entry?.id ??
          entry?.Id ??
          entry?.branchId ??
          entry?.BranchId ??
          entry?.BranchID ??
          entry?.branch_id,
        )
        if (!Number.isFinite(id) || id <= 0) return null

        const name =
          normalizeText(entry?.name ?? entry?.Name) ??
          normalizeText(entry?.label ?? entry?.Label) ??
          normalizeText(entry?.description ?? entry?.Description) ??
          `Branch #${id}`

        return {
          id,
          name,
          value: normalizeText(entry?.value ?? entry?.Value),
        }
      })
      .filter((entry: any) => entry !== null)

    if (options.length > 0) {
      const unique = new Map<number, { id: number; name: string; value?: string }>()
      const normalizedOptions = options as Array<{ id: number; name: string; value?: string }>

      for (const option of normalizedOptions) {
        if (!unique.has(option.id)) {
          unique.set(option.id, option)
        }
      }
      return [...unique.values()]
    }
  }

  return []
}

export async function GET() {
  const session = await getServerSession(authOptions)
  const accessToken = (session as any)?.accessToken as string | undefined
  if (!session || !accessToken) {
    return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
  }

  try {
    const baseUrl = getBaseApiUrl()
    for (const endpoint of BRANCH_LOOKUP_ENDPOINTS) {
      const res = await fetch(`${baseUrl}${endpoint}`, {
        method: "GET",
        headers: {
          Accept: "application/json",
          Authorization: `Bearer ${accessToken}`,
        },
        cache: "no-store",
      })

      const body = await parseBody(res)
      if (!res.ok) continue

      const options = normalizeBranchOptions(body)
      if (options.length > 0) {
        return NextResponse.json({ status: "success", data: options }, { status: 200 })
      }
    }

    return NextResponse.json({ status: "success", data: [] }, { status: 200 })
  } catch (error) {
    console.error("[Third Party Bank Details API] branches lookup error:", error)
    return NextResponse.json({ message: "Internal server error" }, { status: 500 })
  }
}
