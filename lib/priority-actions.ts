import type {
  PreqBreakdown,
  RFQBreakdown,
  TenderBreakdown,
  TenantBreakdown,
} from "@/store/use-dashboard-store"
import type { ProfileType } from "@/store/use-profile-store"

export type PriorityActionTone = "danger" | "warning" | "info"
export type PriorityActionIconKey =
  | "alert_triangle"
  | "calendar_clock"
  | "file_text"
  | "clipboard_check"
  | "layers"

export type PriorityActionItem = {
  id: string
  title: string
  value: number
  description: string
  href: string
  tone: PriorityActionTone
  iconKey: PriorityActionIconKey
}

type BuildPriorityActionsInput = {
  profile?: ProfileType
  prequalification?: PreqBreakdown
  rfqs?: RFQBreakdown
  tenders?: TenderBreakdown
  tenant?: TenantBreakdown
}

export function buildPriorityActions({
  profile,
  prequalification,
  rfqs,
  tenders,
  tenant,
}: BuildPriorityActionsInput): PriorityActionItem[] {
  const isTenant = profile === "Tenant"
  const isSupplier = profile === "Supplier"

  const overdueInvoices = tenant?.invoices?.overdue ?? 0
  const pendingInvoices = tenant?.invoices?.pending ?? 0
  const expiringLeases = tenant?.leases?.expiringSoon ?? 0

  const preqReview = prequalification?.under_review ?? 0
  const rfqAwaiting = (rfqs?.invited ?? 0) + (rfqs?.draft ?? 0)
  const openTenders = tenders?.open ?? 0

  const actions: PriorityActionItem[] = []

  if (isTenant) {
    actions.push(
      {
        id: "overdue-invoices",
        title: "Overdue invoices",
        value: overdueInvoices,
        description: "Resolve overdue balances to avoid penalties.",
        href: "/dashboard/tenant/invoices",
        tone: "danger",
        iconKey: "alert_triangle",
      },
      {
        id: "expiring-leases",
        title: "Leases expiring soon",
        value: expiringLeases,
        description: "Renew expiring leases to avoid gaps.",
        href: "/dashboard/tenant/leases",
        tone: "warning",
        iconKey: "calendar_clock",
      },
      {
        id: "pending-invoices",
        title: "Pending payments",
        value: pendingInvoices,
        description: "Upcoming invoices awaiting payment.",
        href: "/dashboard/tenant/invoices",
        tone: "info",
        iconKey: "file_text",
      }
    )
  } else {
    actions.push({
      id: "preq-review",
      title: "Prequalification review",
      value: preqReview,
      description: "Rounds awaiting review or follow-up.",
      href: "/dashboard/supplier/prequalification",
      tone: "warning",
      iconKey: "clipboard_check",
    })

    if (isSupplier) {
      actions.push(
        {
          id: "rfq-awaiting",
          title: "RFQs awaiting response",
          value: rfqAwaiting,
          description: "Invites and drafts that need action.",
          href: "/dashboard/supplier/rfqs",
          tone: "info",
          iconKey: "file_text",
        },
        {
          id: "open-tenders",
          title: "Open tenders",
          value: openTenders,
          description: "Opportunities you can still submit.",
          href: "/dashboard/supplier/tenders",
          tone: "info",
          iconKey: "layers",
        }
      )
    }
  }

  return actions
    .filter((action) => action.value > 0)
    .sort((a, b) => b.value - a.value)
    .slice(0, 3)
}
