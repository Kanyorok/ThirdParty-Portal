import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"
import { resolveBidStatus } from "@/lib/bids/status"

interface UpdateBidRequest {
  bidId: number
  bidAmount?: number
  validityPeriod?: number
  deliveryPeriod?: number
  paymentTerms?: string
}

const EXTERNAL_API_URL =
  process.env.EXTERNAL_API_URL ||
  process.env.ERP_BASE_URL ||
  process.env.NEXT_PUBLIC_API_URL
const DEFAULT_BID_PER_PAGE = 100
const MAX_BID_FETCH_PAGES = 40
const DEFAULT_TENDER_PER_PAGE = 100
const MAX_TENDER_FETCH_PAGES = 20

async function getAuthSession() {
  const session = await getServerSession(authOptions)
  if (!session?.user) return null
  return session
}

type BidPageMeta = {
  currentPage: number
  lastPage: number
  perPage: number
  total: number | null
}

type BidPageResult = {
  ok: boolean
  status: number
  payload: any
  rows: any[]
  meta: BidPageMeta
}

type TenderLookupRecord = {
  id: string
  no: string
  title: string
}

function text(value: unknown) {
  if (value == null) return ""
  return String(value).trim()
}

function pickFirstText(source: any, keys: string[]) {
  for (const key of keys) {
    const value = text(source?.[key])
    if (value) return value
  }
  return ""
}

function pickFirstNumber(source: any, keys: string[]) {
  for (const key of keys) {
    const raw = source?.[key]
    if (raw == null || raw === "") continue
    const value = Number(raw)
    if (Number.isFinite(value)) return value
  }
  return undefined
}

function normalizeBidStatusValue(source: any) {
  const raw = pickFirstText(source, ["bid_status", "status", "bidStatus", "BidStatus", "state", "envelope_status"])
  const submittedAt = pickFirstText(source, ["submitted_at", "submittedAt", "received_at", "receivedAt"])
  return resolveBidStatus(raw, { hasSubmittedTimestamp: Boolean(submittedAt) })
}

function hasBidSignal(source: any) {
  const keyFields = [
    "id",
    "bid_id",
    "bidId",
    "submission_reference",
    "submissionReference",
    "submitted_at",
    "submittedAt",
    "received_at",
    "receivedAt",
  ]
  if (keyFields.some((key) => text(source?.[key]))) return true

  const amount = pickFirstNumber(source, ["bid_amount", "bidAmount", "amount"])
  if (typeof amount === "number" && amount > 0) return true

  const docs = pickFirstNumber(source, ["documents_count", "documentsCount", "docs_count", "docsCount"])
  if (typeof docs === "number" && docs > 0) return true

  return false
}

function normalizeBidRecord(raw: any) {
  const tenderNode = raw?.tender && typeof raw.tender === "object" ? raw.tender : null
  const status = normalizeBidStatusValue(raw)
  const id = pickFirstNumber(raw, ["id", "bid_id", "bidId"])
  const bidId = pickFirstNumber(raw, ["bid_id", "bidId", "id"])
  const tenderId = pickFirstNumber(raw, ["tender_id", "tenderId", "TenderId"]) ??
    pickFirstNumber(tenderNode, ["id", "Id", "tender_id", "tenderId", "TenderID"])
  const documentsCount = pickFirstNumber(raw, ["documents_count", "documentsCount", "docs_count", "docsCount", "attachments_count"])

  const tenderNo =
    pickFirstText(raw, ["tender_no", "tenderNo", "TenderNo", "tender_ref", "tenderRef"]) ||
    pickFirstText(tenderNode, ["tenderNo", "TenderNo", "tender_no", "reference", "ref"])

  const tenderTitle =
    pickFirstText(raw, ["tender_title", "tenderTitle", "TenderTitle", "title", "tender_name", "tenderName"]) ||
    pickFirstText(tenderNode, ["title", "TenderTitle", "tender_title", "name", "tenderName"])

  return {
    ...raw,
    id: id ?? raw?.id,
    bid_id: bidId ?? raw?.bid_id,
    tender_id: tenderId ?? raw?.tender_id,
    tender_ref: pickFirstText(raw, ["tender_ref", "tenderRef", "tender_no", "tenderNo", "TenderNo"]) || tenderNo,
    tender_no: tenderNo,
    tender_title: tenderTitle,
    bid_amount: pickFirstNumber(raw, ["bid_amount", "bidAmount", "amount"]) ?? raw?.bid_amount,
    currency: pickFirstText(raw, ["currency", "currency_code", "currencyCode"]) || raw?.currency || "",
    validity_period: pickFirstNumber(raw, ["validity_period", "validityPeriod"]) ?? raw?.validity_period,
    delivery_period: pickFirstNumber(raw, ["delivery_period", "deliveryPeriod"]) ?? raw?.delivery_period,
    payment_terms: pickFirstText(raw, ["payment_terms", "paymentTerms", "terms"]) || raw?.payment_terms || "",
    submitted_at: pickFirstText(raw, ["submitted_at", "submittedAt", "received_at", "receivedAt"]) || raw?.submitted_at || null,
    received_at: pickFirstText(raw, ["received_at", "receivedAt", "submitted_at", "submittedAt"]) || raw?.received_at || null,
    documents_count: documentsCount ?? raw?.documents_count ?? 0,
    submission_reference: pickFirstText(raw, ["submission_reference", "submissionReference", "reference", "bid_reference"]) || raw?.submission_reference || "",
    envelope_status: pickFirstText(raw, ["envelope_status", "envelopeStatus"]) || raw?.envelope_status || "",
    status,
    bid_status: status,
  }
}

function extractTenderRows(payload: any) {
  for (const candidate of [
    payload?.data?.data,
    payload?.data?.items,
    payload?.data?.rows,
    payload?.items,
    payload?.rows,
    payload?.data,
    payload,
  ]) {
    if (Array.isArray(candidate)) return candidate
  }
  return []
}

function normalizeTenderLookupRecord(raw: any): TenderLookupRecord | null {
  const tenderNode = raw?.tender && typeof raw.tender === "object" ? raw.tender : null

  const id =
    pickFirstText(raw, ["id", "Id", "tender_id", "tenderId", "TenderID"]) ||
    pickFirstText(tenderNode, ["id", "Id", "tender_id", "tenderId", "TenderID"])
  const no =
    pickFirstText(raw, ["tenderNo", "TenderNo", "tender_no", "reference", "ref"]) ||
    pickFirstText(tenderNode, ["tenderNo", "TenderNo", "tender_no", "reference", "ref"])
  const title =
    pickFirstText(raw, ["title", "TenderTitle", "tender_title", "name", "tenderName"]) ||
    pickFirstText(tenderNode, ["title", "TenderTitle", "tender_title", "name", "tenderName"])

  if (!id && !no) return null
  return { id, no, title }
}

function needsTenderInfo(bid: any) {
  const no = text(bid?.tender_no || bid?.tender_ref || bid?.tenderNo || bid?.TenderNo)
  const title = text(bid?.tender_title || bid?.tenderTitle || bid?.TenderTitle)
  return !no || !title
}

async function fetchTenderLookupDirectory(
  accessToken: string
) {
  const byId = new Map<string, TenderLookupRecord>()
  const byNo = new Map<string, TenderLookupRecord>()

  const baseParams = new URLSearchParams()

  const fetchPage = async (page: number) => {
    const params = new URLSearchParams(baseParams)
    params.set("page", String(page))
    params.set("per_page", String(DEFAULT_TENDER_PER_PAGE))
    const response = await fetch(`${EXTERNAL_API_URL}/api/v1/supplier/tenders?${params.toString()}`, {
      headers: {
        Authorization: `Bearer ${accessToken}`,
        Accept: "application/json",
      },
      cache: "no-store",
    })

    if (!response.ok) return { rows: [] as any[], lastPage: page }
    const payload = await response.json().catch(() => null)
    const rows = extractTenderRows(payload)

    const meta = payload?.meta ?? payload?.data?.meta ?? payload?.pagination ?? payload?.data?.pagination ?? {}
    const metaLast = Number(meta?.last_page ?? meta?.lastPage ?? meta?.pages ?? meta?.total_pages)
    const metaTotal = Number(meta?.total ?? payload?.total)
    const inferredLast = Number.isFinite(metaTotal) && metaTotal > 0
      ? Math.ceil(metaTotal / DEFAULT_TENDER_PER_PAGE)
      : 1
    const lastPage = Number.isFinite(metaLast) && metaLast > 0 ? Math.trunc(metaLast) : inferredLast

    return { rows, lastPage: Math.max(1, lastPage) }
  }

  const first = await fetchPage(1)
  first.rows.forEach((row) => {
    const normalized = normalizeTenderLookupRecord(row)
    if (!normalized) return
    if (normalized.id) byId.set(normalized.id, normalized)
    if (normalized.no) byNo.set(normalized.no.toLowerCase(), normalized)
  })

  const finalPage = Math.min(first.lastPage, MAX_TENDER_FETCH_PAGES)
  if (finalPage > 1) {
    const calls: Array<Promise<{ rows: any[]; lastPage: number }>> = []
    for (let page = 2; page <= finalPage; page += 1) {
      calls.push(fetchPage(page))
    }
    const pages = await Promise.all(calls)
    pages.forEach((result) => {
      result.rows.forEach((row) => {
        const normalized = normalizeTenderLookupRecord(row)
        if (!normalized) return
        if (normalized.id && !byId.has(normalized.id)) byId.set(normalized.id, normalized)
        if (normalized.no && !byNo.has(normalized.no.toLowerCase())) byNo.set(normalized.no.toLowerCase(), normalized)
      })
    })
  }

  return { byId, byNo }
}

function enrichBidsWithTenderInfo(
  bids: any[],
  lookup: { byId: Map<string, TenderLookupRecord>; byNo: Map<string, TenderLookupRecord> }
) {
  return bids.map((bid) => {
    const existingId = text(bid?.tender_id || bid?.tenderId)
    const existingNo = text(bid?.tender_no || bid?.tender_ref || bid?.tenderNo || bid?.TenderNo).toLowerCase()
    const matched =
      (existingId && lookup.byId.get(existingId)) ||
      (existingNo && lookup.byNo.get(existingNo)) ||
      null

    if (!matched) return bid

    const nextNo = text(bid?.tender_no) || matched.no
    const nextRef = text(bid?.tender_ref) || nextNo
    const nextTitle = text(bid?.tender_title) || matched.title

    return {
      ...bid,
      tender_id: bid?.tender_id ?? matched.id,
      tender_no: nextNo,
      tender_ref: nextRef,
      tender_title: nextTitle,
    }
  })
}

function toPositiveInt(value: unknown, fallback: number) {
  const n = Number(value)
  if (!Number.isFinite(n)) return fallback
  return Math.max(1, Math.trunc(n))
}

function extractBidRows(payload: any) {
  for (const candidate of [
    payload?.data?.data,
    payload?.data?.items,
    payload?.data?.rows,
    payload?.items,
    payload?.rows,
    payload?.data,
    payload,
  ]) {
    if (Array.isArray(candidate)) return candidate
  }
  return []
}

function extractBidMeta(payload: any): BidPageMeta {
  const metaSource =
    payload?.meta ??
    payload?.data?.meta ??
    payload?.pagination ??
    payload?.data?.pagination ??
    payload ??
    {}

  const currentPage = toPositiveInt(
    metaSource?.current_page ?? metaSource?.currentPage ?? metaSource?.page,
    1
  )
  const perPage = toPositiveInt(
    metaSource?.per_page ?? metaSource?.perPage ?? metaSource?.page_size ?? metaSource?.pageSize ?? metaSource?.limit,
    DEFAULT_BID_PER_PAGE
  )
  const rawTotal = metaSource?.total ?? payload?.total
  const total = Number.isFinite(Number(rawTotal)) ? Number(rawTotal) : null
  const inferredLastPage = total != null ? Math.max(1, Math.ceil(total / perPage)) : 1
  const lastPage = toPositiveInt(
    metaSource?.last_page ?? metaSource?.lastPage ?? metaSource?.total_pages ?? metaSource?.pages,
    inferredLastPage
  )

  return { currentPage, lastPage, perPage, total }
}

function getBidKey(bid: any) {
  const directId = bid?.id ?? bid?.bid_id
  if (directId != null && String(directId).trim()) return `id:${String(directId).trim()}`

  const ref = String(
    bid?.submission_reference ?? bid?.submissionReference ?? bid?.reference ?? ""
  ).trim()
  if (ref) return `ref:${ref}`

  const tenderId = String(bid?.tender_id ?? bid?.tenderId ?? "").trim()
  const tenderNo = String(
    bid?.tender_no ?? bid?.tender_ref ?? bid?.tenderNo ?? bid?.TenderNo ?? ""
  ).trim()
  const stamp = String(
    bid?.submitted_at ?? bid?.submittedAt ?? bid?.received_at ?? bid?.receivedAt ?? bid?.updated_at ?? bid?.created_at ?? ""
  ).trim()
  const amount = String(bid?.bid_amount ?? bid?.bidAmount ?? bid?.amount ?? "").trim()
  return `fallback:${tenderId}:${tenderNo}:${stamp}:${amount}`
}

function sortBidsNewestFirst(left: any, right: any) {
  const leftTs = new Date(
    left?.submitted_at ??
    left?.received_at ??
    left?.updated_at ??
    left?.created_at ??
    0
  ).getTime()
  const rightTs = new Date(
    right?.submitted_at ??
    right?.received_at ??
    right?.updated_at ??
    right?.created_at ??
    0
  ).getTime()
  return rightTs - leftTs
}

async function fetchBidPage(
  accessToken: string,
  baseParams: URLSearchParams,
  page: number,
  perPage: number
): Promise<BidPageResult> {
  const params = new URLSearchParams(baseParams)
  params.set("page", String(page))
  params.set("per_page", String(perPage))
  const requestUrl = `${EXTERNAL_API_URL}/api/v1/supplier/bid-submissions?${params.toString()}`

  const response = await fetch(requestUrl, {
    headers: {
      Authorization: `Bearer ${accessToken}`,
      Accept: "application/json",
    },
    cache: "no-store",
  })

  const payload = await response.json().catch(() => null)
  if (!response.ok) {
    return {
      ok: false,
      status: response.status,
      payload,
      rows: [],
      meta: { currentPage: page, lastPage: page, perPage, total: null },
    }
  }

  return {
    ok: true,
    status: response.status,
    payload,
    rows: extractBidRows(payload),
    meta: extractBidMeta(payload),
  }
}

function isInvalidTenderIdError(payload: any) {
  const errors = payload?.errors
  if (!errors || typeof errors !== "object") return false
  const tenderErrors = Array.isArray((errors as Record<string, unknown>).tender_id)
    ? ((errors as Record<string, unknown>).tender_id as string[])
    : []
  if (!tenderErrors.length) return false
  return tenderErrors.some((message) =>
    String(message).toLowerCase().includes("invalid") ||
    String(message).toLowerCase().includes("required")
  )
}

function hasBidDocumentValidationError(payload: any) {
  const errors = payload?.errors
  if (!errors || typeof errors !== "object") return false
  const keys = Object.keys(errors as Record<string, unknown>)
  return keys.some((key) => key === "bid_documents" || key.startsWith("bid_documents."))
}

async function resolveTenderIdByNo(
  accessToken: string,
  tenderNo: string
) {
  if (!EXTERNAL_API_URL || !tenderNo) return null

  const params = new URLSearchParams()
  params.set("search", tenderNo)

  const response = await fetch(`${EXTERNAL_API_URL}/api/v1/supplier/tenders?${params.toString()}`, {
    headers: {
      Authorization: `Bearer ${accessToken}`,
      Accept: "application/json",
    },
  })
  if (!response.ok) return null

  const json = await response.json().catch(() => null)
  const list = Array.isArray(json?.data) ? json.data : Array.isArray(json) ? json : []
  const normalizedTenderNo = String(tenderNo).trim().toLowerCase()
  const match = list.find((item: any) => {
    const no = String(item?.tenderNo || item?.TenderNo || item?.tender_no || "").trim().toLowerCase()
    return no === normalizedTenderNo
  })
  const resolvedId = match?.id ?? match?.Id ?? null
  return resolvedId ? String(resolvedId) : null
}

export async function GET(request: NextRequest) {
  try {
    const session = await getAuthSession()
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    if (!EXTERNAL_API_URL) {
      return NextResponse.json({ error: "API not configured" }, { status: 502 })
    }

    const searchParams = request.nextUrl.searchParams
    const tenderId = searchParams.get('tenderId')
    const tenderNo = searchParams.get('tenderNo')
    const checkExisting = searchParams.get('checkExisting')
    const search = searchParams.get("search")
    const includeAll = searchParams.get("all") !== "false"
    const requestedPage = toPositiveInt(searchParams.get("page"), 1)
    const requestedPerPage = toPositiveInt(
      searchParams.get("per_page") ?? searchParams.get("perPage"),
      DEFAULT_BID_PER_PAGE
    )
    const upstreamParams = new URLSearchParams()
    if (tenderId) upstreamParams.set("tender_id", String(tenderId))
    if (tenderNo) upstreamParams.set("tender_no", String(tenderNo))
    if (search) upstreamParams.set("search", search)

    const shouldFetchAll = includeAll || checkExisting === "true" || Boolean(tenderId) || Boolean(tenderNo)
    const firstPage = shouldFetchAll ? 1 : requestedPage
    const firstResult = await fetchBidPage(String(session.accessToken), upstreamParams, firstPage, requestedPerPage)

    if (!firstResult.ok) {
      return NextResponse.json(
        {
          error: firstResult.payload?.error || "Fetch failed",
          message: firstResult.payload?.message || `Status: ${firstResult.status}`,
          errors: firstResult.payload?.errors,
        },
        { status: firstResult.status }
      )
    }

    const allRows = [...firstResult.rows]
    const upstreamLastPage = firstResult.meta.lastPage
    let fetchedPages = 1

    if (shouldFetchAll && upstreamLastPage > firstResult.meta.currentPage) {
      const finalPage = Math.min(upstreamLastPage, MAX_BID_FETCH_PAGES)
      const pageCalls: Array<Promise<BidPageResult>> = []
      for (let page = firstResult.meta.currentPage + 1; page <= finalPage; page += 1) {
        pageCalls.push(fetchBidPage(String(session.accessToken), upstreamParams, page, requestedPerPage))
      }
      const pageResults = await Promise.all(pageCalls)
      pageResults.forEach((result) => {
        if (result.ok) {
          fetchedPages += 1
          allRows.push(...result.rows)
        }
      })
    }

    const deduped = Array.from(
      new Map(allRows.map((row: any) => [getBidKey(row), normalizeBidRecord(row)])).values()
    )
    const meaningful = deduped.filter(hasBidSignal)
    const normalizedTenderNo = String(tenderNo || "").trim().toLowerCase()
    const filtered = meaningful.filter((bid: any) => {
      const byId = tenderId ? String(bid?.tender_id) === String(tenderId) : false
      const bidTenderNo = String(
        bid?.tender_no || bid?.tender_ref || bid?.tenderNo || bid?.TenderNo || ""
      ).trim().toLowerCase()
      const byNo = normalizedTenderNo ? bidTenderNo === normalizedTenderNo : false
      if (tenderId && normalizedTenderNo) return byId || byNo
      if (tenderId) return byId
      if (normalizedTenderNo) return byNo
      return true
    })

    let sortedFiltered = [...filtered].sort(sortBidsNewestFirst)

    if (sortedFiltered.some(needsTenderInfo)) {
      try {
        const lookup = await fetchTenderLookupDirectory(String(session.accessToken))
        sortedFiltered = enrichBidsWithTenderInfo(sortedFiltered, lookup)
      } catch {
        // Keep core bid results available even if tender enrichment fails.
      }
    }

    if (checkExisting === 'true' && (tenderId || tenderNo)) {
      const submitted = sortedFiltered.find((bid: any) =>
        resolveBidStatus(bid?.bid_status ?? bid?.status, {
          hasSubmittedTimestamp: Boolean(
            bid?.submitted_at || bid?.submittedAt || bid?.received_at || bid?.receivedAt
          ),
        }) === "submitted"
      )
      const existing = submitted ?? sortedFiltered[0] ?? null
      return NextResponse.json({
        success: true,
        hasExistingBid: !!existing,
        existingBid: existing,
        message: firstResult.payload?.message
      })
    }

    const upstreamTotal = firstResult.meta.total
    const hasTenderFilter = Boolean(tenderId || tenderNo)
    const total =
      hasTenderFilter || shouldFetchAll
        ? sortedFiltered.length
        : Number.isFinite(Number(upstreamTotal))
          ? Number(upstreamTotal)
          : sortedFiltered.length

    return NextResponse.json({
      success: firstResult.payload?.success ?? true,
      message: firstResult.payload?.message,
      data: sortedFiltered,
      total,
      supplier_name: firstResult.payload?.supplier_name,
      meta: {
        fetched_pages: fetchedPages,
        upstream_last_page: upstreamLastPage,
        upstream_total: upstreamTotal,
      },
    })
  } catch (error) {
    return NextResponse.json(
      { error: "Fetch failed", message: error instanceof Error ? error.message : "Unknown error" },
      { status: 500 }
    )
  }
}

export async function POST(request: NextRequest) {
  try {
    const session = await getAuthSession()
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

    const formData = await request.formData()
    const tenderId = formData.get('tenderId') as string
    const tenderNo = (formData.get('tenderNo') as string) || ""
    const bidAmount = parseFloat(formData.get('bidAmount') as string)
    const currency = String(formData.get('currency') || "").trim().toUpperCase().slice(0, 3)
    const validityPeriod = parseInt(formData.get('validityPeriod') as string)
    const deliveryPeriod = parseInt(formData.get('deliveryPeriod') as string)
    const status = (formData.get('status') as string) || 'draft'
    const requestedStatus = status === "submitted" ? "submitted" : "draft"

    if (!tenderId || isNaN(bidAmount) || !currency || isNaN(validityPeriod) || isNaN(deliveryPeriod)) {
      return NextResponse.json({ error: "Required fields missing or invalid" }, { status: 400 })
    }

    const files = formData.getAll('documents') as File[]
    const paymentTerms = (formData.get('paymentTerms') as string) || ''

    const buildPayload = (resolvedTenderId: string, currentStatus: "draft" | "submitted") => {
      const payload = new FormData()
      payload.append('tender_id', resolvedTenderId)
      payload.append('bid_amount', bidAmount.toString())
      payload.append('currency', currency)
      payload.append('validity_period', validityPeriod.toString())
      payload.append('delivery_period', deliveryPeriod.toString())
      payload.append('payment_terms', paymentTerms)
      payload.append('status', currentStatus)
      files.forEach((file) => {
        payload.append('bid_documents[]', file)
      })
      return payload
    }

    const postToApi = (payload: FormData) =>
      fetch(`${EXTERNAL_API_URL}/api/v1/supplier/bid-submissions`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${session.accessToken}`,
          Accept: "application/json",
        },
        body: payload,
        signal: AbortSignal.timeout(30000),
      })

    if (EXTERNAL_API_URL) {
      let resolvedTenderId = tenderId
      let effectiveStatus: "draft" | "submitted" = requestedStatus
      let response = await postToApi(buildPayload(resolvedTenderId, effectiveStatus))
      let result = await response.json().catch(() => null)

      if (!response.ok && response.status === 422 && isInvalidTenderIdError(result) && tenderNo) {
        const fallbackTenderId = await resolveTenderIdByNo(session.accessToken as string, tenderNo)
        if (fallbackTenderId && fallbackTenderId !== resolvedTenderId) {
          resolvedTenderId = fallbackTenderId
          response = await postToApi(buildPayload(resolvedTenderId, effectiveStatus))
          result = await response.json().catch(() => null)
        }
      }

      // Backend may still require at least one document for final submit.
      // Gracefully save as draft when no files were provided.
      if (
        !response.ok &&
        response.status === 422 &&
        effectiveStatus === "submitted" &&
        files.length === 0 &&
        hasBidDocumentValidationError(result)
      ) {
        effectiveStatus = "draft"
        response = await postToApi(buildPayload(resolvedTenderId, effectiveStatus))
        result = await response.json().catch(() => null)
      }

      if (response.ok) {
        const responseStatus = String(result?.data?.status || result?.data?.bid_status || effectiveStatus || "").toLowerCase()
        const fallbackToDraft = requestedStatus === "submitted" && responseStatus === "draft"
        return NextResponse.json({
          success: result?.success ?? true,
          message: result?.message || "Success",
          data: result?.data ?? result,
          requested_status: requestedStatus,
          effective_status: responseStatus || effectiveStatus,
          fallback_to_draft: fallbackToDraft,
        })
      }

      return NextResponse.json({
        error: result?.error || "ERP Error",
        message: result?.message || "Request failed",
        errors: result?.errors,
        invitation_status: result?.invitation_status,
        tender_status: result?.tender_status,
        submission_deadline: result?.submission_deadline,
        data: result?.data,
      }, { status: response.status })
    }

    return NextResponse.json({ error: "API not configured" }, { status: 502 })
  } catch (error) {
    return NextResponse.json(
      { error: "Process failed", message: error instanceof Error ? error.message : "Unknown error" },
      { status: 500 }
    )
  }
}

export async function PUT(request: NextRequest) {
  try {
    const session = await getAuthSession()
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

    const body: UpdateBidRequest = await request.json()
    const { bidId, ...updateData } = body

    if (!bidId || !EXTERNAL_API_URL) {
      return NextResponse.json({ error: "Missing ID or Configuration" }, { status: 400 })
    }

    const actorId = session.user.userId ?? session.user.thirdPartyId ?? null

    const response = await fetch(`${EXTERNAL_API_URL}/api/tender-bids/${bidId}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${session.accessToken}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        ...updateData,
        modifiedBy: actorId,
        modifiedOn: new Date().toISOString(),
      }),
    })

    const result = await response.json()
    if (!response.ok) throw new Error(result.message || "Update failed")

    return NextResponse.json({ message: "Updated", data: result })
  } catch (error) {
    return NextResponse.json(
      { error: "Update failed", message: error instanceof Error ? error.message : "Unknown error" },
      { status: 500 }
    )
  }
}
