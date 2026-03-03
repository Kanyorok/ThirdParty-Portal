"use client"

import { useEffect, useMemo, useState } from "react"
import { motion } from "framer-motion"
import {
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  LabelList,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts"
import {
  Activity,
  AlertCircle,
  Building,
  Coins,
  FileCheck2,
  FileSearch,
  FileText,
  Wallet,
} from "lucide-react"
import { Skeleton } from "@/components/common/skeleton"
import {
  useDashboardStore,
  type TenantBreakdown,
} from "@/store/use-dashboard-store"
import type { ProfileType } from "@/store/use-profile-store"
import { useShallow } from "zustand/react/shallow"

type PreqBreakdown = {
  approved: number
  submitted: number
  under_review: number
  rejected: number
  not_applied: number
}

type RFQBreakdown = {
  invited: number
  draft: number
  submitted: number
  closed: number
}

type TenderBreakdown = {
  open: number
  draft: number
  closed: number
}

type BidBreakdown = {
  draft: number
  submitted: number
  unknown: number
}

type SummaryChartsProps = {
  profile?: ProfileType
}

type ChartDatum = {
  name: string
  value: number
  color: string
}

const chartColors = {
  emerald: "#10B981",
  amber: "#F59E0B",
  sky: "#38BDF8",
  rose: "#F43F5E",
  slate: "#64748B",
  indigo: "#6366F1",
}

const percentOf = (value: number, total: number) =>
  total > 0 ? (value / total) * 100 : 0

const buildChartData = (data: ChartDatum[]) =>
  data.filter((item) => item.value > 0)

const truncateLabel = (value: string, maxLength: number) =>
  value.length > maxLength ? `${value.slice(0, maxLength)}...` : value

const useViewportWidth = () => {
  const [width, setWidth] = useState(0)

  useEffect(() => {
    const updateWidth = () => setWidth(window.innerWidth)
    updateWidth()
    window.addEventListener("resize", updateWidth)
    return () => window.removeEventListener("resize", updateWidth)
  }, [])

  return width
}

const SectionHeader = ({
  title,
  icon: Icon,
  total,
}: {
  title: string
  icon: any
  total: number
}) => (
  <div className="flex items-center justify-between gap-3 px-0.5 pb-2.5">
    <div className="flex min-w-0 items-center gap-2.5">
      <span className="h-8 w-1 rounded-full bg-indigo-500/70" />
      <div className="flex h-8 w-8 items-center justify-center rounded-lg border border-indigo-200/80 bg-indigo-50 text-indigo-600">
        <Icon className="h-4.5 w-4.5" />
      </div>
      <h3 className="truncate text-[14px] font-semibold tracking-tight text-foreground">
        {title}
      </h3>
    </div>
    <span className="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">
      {total.toLocaleString()}
    </span>
  </div>
)

const defaultValueFormatter = (value: number) => value.toLocaleString()
const formatLabelValue = (
  value: string | number | null | undefined,
  formatter: (value: number) => string
) => formatter(typeof value === "number" ? value : Number(value ?? 0))

type BarTooltipProps = {
  active?: boolean
  payload?: Array<{ value?: number; color?: string }>
  label?: string
  valueFormatter: (value: number) => string
}

const BarTooltip = ({
  active,
  payload,
  label,
  valueFormatter,
}: BarTooltipProps) => {
  if (!active || !payload?.length) return null

  const entry = payload[0]
  const rawValue =
    typeof entry?.value === "number" ? entry.value : Number(entry?.value ?? 0)

  return (
    <div className="rounded-lg border border-border/60 bg-popover px-3 py-2 text-xs shadow-sm">
      <p className="font-medium text-foreground">{label}</p>
      <div className="mt-1 flex items-center gap-2 text-muted-foreground">
        <span
          className="h-2 w-2 rounded-full"
          style={{ backgroundColor: entry?.color ?? chartColors.slate }}
        />
        <span className="font-semibold text-foreground">
          {valueFormatter(rawValue)}
        </span>
      </div>
    </div>
  )
}

const BarChartCard = ({
  title: _title,
  total: _total,
  data,
  valueFormatter = defaultValueFormatter,
  yDomain,
  size = "regular",
  yLabel,
}: {
  title: string
  total?: number
  data: ChartDatum[]
  valueFormatter?: (value: number) => string
  yDomain?: [number, number]
  size?: "compact" | "regular"
  yLabel?: string
}) => {
  const chartData = buildChartData(data)
  const isCompact = size === "compact"
  const barSize = isCompact ? (chartData.length > 4 ? 16 : 22) : 30
  const chartHeight = isCompact ? 156 : 196
  const tickAngle = isCompact ? -20 : 0
  const labelMaxLength = isCompact ? 9 : 12
  const yAxisWidth = isCompact ? 30 : 36
  const showLabels = true

  return (
    <div className="px-2 py-1.5">
      <div className="mb-1 flex items-center justify-between gap-2">
        <p className="truncate text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
          {_title}
        </p>
        {typeof _total === "number" ? (
          <span className="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
            {_total}
          </span>
        ) : null}
      </div>
      <div className="pt-1">
        {chartData.length === 0 ? (
          <div className="flex h-24 items-center justify-center text-xs text-muted-foreground">
            No data yet
          </div>
        ) : (
          <div style={{ height: chartHeight }}>
            <ResponsiveContainer width="100%" height="100%">
              <BarChart
                data={chartData}
                barCategoryGap={isCompact ? 12 : 20}
                barGap={isCompact ? 6 : 14}
                margin={{ top: 10, right: 8, left: -10, bottom: isCompact ? 10 : 6 }}
              >
                <CartesianGrid
                  vertical={false}
                  stroke="hsl(var(--border))"
                  strokeOpacity={0.45}
                  strokeDasharray="3 3"
                />
                <XAxis
                  dataKey="name"
                  tickLine={false}
                  axisLine={false}
                  tickMargin={isCompact ? 6 : 10}
                  interval={0}
                  tickFormatter={(value) => truncateLabel(value, labelMaxLength)}
                  angle={tickAngle}
                  textAnchor={isCompact ? "end" : "middle"}
                  height={isCompact ? 34 : 32}
                  tick={{ fontSize: 12, fill: "hsl(var(--muted-foreground))" }}
                />
                <YAxis
                  tickLine={false}
                  axisLine={false}
                  width={yAxisWidth}
                  allowDecimals={false}
                  tick={{ fontSize: 12, fill: "hsl(var(--muted-foreground))" }}
                  tickFormatter={valueFormatter}
                  domain={yDomain ?? (["auto", "auto"] as const)}
                  label={
                    yLabel
                      ? {
                        value: yLabel,
                        angle: -90,
                        position: "insideLeft",
                        fill: "hsl(var(--muted-foreground))",
                        style: { fontSize: 10, fontWeight: 600 },
                      }
                      : undefined
                  }
                />
                <Tooltip
                  cursor={false}
                  content={<BarTooltip valueFormatter={valueFormatter} />}
                />
                <Bar
                  dataKey="value"
                  barSize={barSize}
                  minPointSize={3}
                  radius={[10, 10, 6, 6]}
                  background={{ fill: "hsl(var(--muted) / 0.18)", radius: 10 }}
                >
                  {chartData.map((entry) => (
                    <Cell key={entry.name} fill={entry.color} />
                  ))}
                  {showLabels ? (
                    <LabelList
                      dataKey="value"
                      position="insideTop"
                      formatter={(value) => typeof value === 'boolean' ? '' : formatLabelValue(value, valueFormatter)}
                      fill="hsl(var(--foreground))"
                      fontSize={isCompact ? 10 : 11}
                      fontWeight={600}
                    />
                  ) : null}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          </div>
        )}
      </div>
    </div>
  )
}

export default function SummaryCharts({ profile }: SummaryChartsProps) {
  const viewportWidth = useViewportWidth()
  const chartSize = viewportWidth > 0 && viewportWidth < 640 ? "compact" : "regular"
  const { summary, loading, error } = useDashboardStore(
    useShallow((s) => ({
      summary: s.summary,
      loading: s.loading,
      error: s.error,
    }))
  )

  const preq = summary?.breakdowns?.prequalification as
    | PreqBreakdown
    | undefined
  const rfqs = summary?.breakdowns?.rfqs as RFQBreakdown | undefined

  const preqTotal = useMemo(
    () => Object.values(preq || {}).reduce((a, b) => a + b, 0),
    [preq]
  )

  const rfqTotal = useMemo(
    () => Object.values(rfqs || {}).reduce((a, b) => a + b, 0),
    [rfqs]
  )

  const tenderBreakdown = summary?.breakdowns?.tenders as
    | TenderBreakdown
    | undefined
  const tenderTotal = useMemo(
    () => Object.values(tenderBreakdown || {}).reduce((a, b) => a + b, 0),
    [tenderBreakdown]
  )
  const tenderCardTotal =
    summary?.summary?.openTenders ??
    summary?.summary?.tendersAvailable ??
    tenderBreakdown?.open ??
    tenderTotal
  const bidBreakdown = summary?.breakdowns?.bids as BidBreakdown | undefined
  const bidTotal = useMemo(
    () => Object.values(bidBreakdown || {}).reduce((a, b) => a + b, 0),
    [bidBreakdown]
  )
  const bidCardTotal = summary?.summary?.myBids ?? bidTotal

  const tenantBreakdown = summary?.breakdowns?.tenant as
    | TenantBreakdown
    | undefined
  const leaseSummary = tenantBreakdown?.leases
  const invoiceSummary = tenantBreakdown?.invoices

  const leaseTotal = leaseSummary
    ? leaseSummary.total ||
    leaseSummary.active +
    leaseSummary.expiringSoon +
    leaseSummary.inactive
    : 0

  const invoiceCountFallback =
    (invoiceSummary?.paid ?? 0) +
    (invoiceSummary?.pending ?? 0) +
    (invoiceSummary?.overdue ?? 0)

  const invoiceTotal = Math.max(invoiceSummary?.total ?? 0, invoiceCountFallback)

  const isTenantView = profile === "Tenant"

  const preqStatusData = useMemo(
    () =>
      buildChartData([
        {
          name: "Approved",
          value: preq?.approved || 0,
          color: chartColors.emerald,
        },
        {
          name: "In review",
          value: preq?.under_review || 0,
          color: chartColors.amber,
        },
        {
          name: "Submitted",
          value: preq?.submitted || 0,
          color: chartColors.sky,
        },
        {
          name: "Rejected",
          value: preq?.rejected || 0,
          color: chartColors.rose,
        },
        {
          name: "Not applied",
          value: preq?.not_applied || 0,
          color: chartColors.slate,
        },
      ]),
    [preq]
  )

  const rfqStatusData = useMemo(
    () =>
      buildChartData([
        {
          name: "Invited",
          value: rfqs?.invited || 0,
          color: chartColors.indigo,
        },
        {
          name: "Draft",
          value: rfqs?.draft || 0,
          color: chartColors.amber,
        },
        {
          name: "Submitted",
          value: rfqs?.submitted || 0,
          color: chartColors.emerald,
        },
        {
          name: "Closed",
          value: rfqs?.closed || 0,
          color: chartColors.rose,
        },
      ]),
    [rfqs]
  )

  const tenderStatusData = useMemo(
    () =>
      buildChartData([
        {
          name: "Open",
          value: tenderBreakdown?.open || 0,
          color: chartColors.emerald,
        },
        {
          name: "Draft",
          value: tenderBreakdown?.draft || 0,
          color: chartColors.amber,
        },
        {
          name: "Closed",
          value: tenderBreakdown?.closed || 0,
          color: chartColors.rose,
        },
      ]),
    [tenderBreakdown]
  )

  const bidStatusData = useMemo(
    () =>
      buildChartData([
        {
          name: "Submitted",
          value: bidBreakdown?.submitted || 0,
          color: chartColors.emerald,
        },
        {
          name: "Draft",
          value: bidBreakdown?.draft || 0,
          color: chartColors.amber,
        },
        {
          name: "Other",
          value: bidBreakdown?.unknown || 0,
          color: chartColors.slate,
        },
      ]),
    [bidBreakdown]
  )

  const leaseStatusData = useMemo(
    () =>
      buildChartData([
        {
          name: "Active",
          value: leaseSummary?.active || 0,
          color: chartColors.emerald,
        },
        {
          name: "Expiring",
          value: leaseSummary?.expiringSoon || 0,
          color: chartColors.amber,
        },
        {
          name: "Inactive",
          value: leaseSummary?.inactive || 0,
          color: chartColors.slate,
        },
      ]),
    [leaseSummary]
  )

  const leaseOccupancyData = useMemo(() => {
    const occupied = leaseSummary?.active || 0
    const vacant = Math.max(leaseTotal - occupied, 0)

    return buildChartData([
      {
        name: "Occupied",
        value: occupied,
        color: chartColors.emerald,
      },
      {
        name: "Vacant",
        value: vacant,
        color: chartColors.slate,
      },
    ])
  }, [leaseSummary, leaseTotal])

  const invoiceStatusData = useMemo(
    () =>
      buildChartData([
        {
          name: "Pending",
          value: invoiceSummary?.pending || 0,
          color: chartColors.amber,
        },
        {
          name: "Overdue",
          value: invoiceSummary?.overdue || 0,
          color: chartColors.rose,
        },
        {
          name: "Paid",
          value: invoiceSummary?.paid || 0,
          color: chartColors.emerald,
        },
      ]),
    [invoiceSummary]
  )

  const invoiceCollectionData = useMemo(() => {
    const paid = invoiceSummary?.paid || 0
    const unpaid = (invoiceSummary?.pending || 0) + (invoiceSummary?.overdue || 0)

    return buildChartData([
      {
        name: "Paid",
        value: paid,
        color: chartColors.emerald,
      },
      {
        name: "Unpaid",
        value: unpaid,
        color: chartColors.amber,
      },
    ])
  }, [invoiceSummary])

  const invoiceRiskData = useMemo(
    () =>
      buildChartData([
        {
          name: "Overdue",
          value: invoiceSummary?.overdue || 0,
          color: chartColors.rose,
        },
        {
          name: "Pending",
          value: invoiceSummary?.pending || 0,
          color: chartColors.amber,
        },
      ]),
    [invoiceSummary]
  )

  const invoicePercentData = useMemo(
    () =>
      buildChartData([
        {
          name: "Paid %",
          value: percentOf(invoiceSummary?.paid || 0, invoiceTotal),
          color: chartColors.emerald,
        },
        {
          name: "Pending %",
          value: percentOf(invoiceSummary?.pending || 0, invoiceTotal),
          color: chartColors.amber,
        },
        {
          name: "Overdue %",
          value: percentOf(invoiceSummary?.overdue || 0, invoiceTotal),
          color: chartColors.rose,
        },
      ]),
    [invoiceSummary, invoiceTotal]
  )

  if (loading)
    return (
      <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {[1, 2, 3].map((i) => (
          <div key={i} className="rounded-2xl border border-border/60 bg-white/90 p-3.5">
            <div className="flex justify-between items-center">
              <Skeleton className="h-6 w-40 rounded-lg" />
              <Skeleton className="h-6 w-20 rounded-full" />
            </div>
            <div className="mt-4 space-y-2.5">
              {[1, 2, 3].map((j) => (
                <Skeleton key={j} className="h-16 w-full rounded-xl" />
              ))}
            </div>
          </div>
        ))}
      </div>
    )

  if (error)
    return (
      <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border/60 bg-white px-6 py-10">
        <AlertCircle className="h-10 w-10 text-muted-foreground mb-4" />
        <p className="text-muted-foreground font-medium text-center">
          Failed to load dashboard analytics
        </p>
      </div>
    )

  if (isTenantView) {
    return (
      <div className="grid grid-cols-1 gap-3 lg:grid-cols-3">
        <motion.section className="rounded-2xl border border-slate-200/80 bg-white/90 p-3 md:p-3.5">
          <SectionHeader title="Lease health" icon={Building} total={leaseTotal} />
          <div className="mt-2.5 grid gap-0 divide-y divide-slate-200/70 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:divide-y-0 lg:divide-x lg:divide-slate-200/70">
            <BarChartCard
              title="Lease status"
              total={leaseTotal}
              data={leaseStatusData}
              size={chartSize}
              yLabel="Leases"
            />
            <BarChartCard
              title="Occupancy split"
              total={leaseTotal}
              data={leaseOccupancyData}
              size={chartSize}
              yLabel="Leases"
            />
          </div>
        </motion.section>

        <motion.section className="rounded-2xl border border-slate-200/80 bg-white/90 p-3 md:p-3.5">
          <SectionHeader
            title="Invoice performance"
            icon={Wallet}
            total={invoiceTotal}
          />
          <div className="mt-2.5 grid gap-0 divide-y divide-slate-200/70 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:divide-y-0 lg:divide-x lg:divide-slate-200/70">
            <BarChartCard
              title="Invoice status"
              total={invoiceTotal}
              data={invoiceStatusData}
              size={chartSize}
              yLabel="Invoices"
            />
            <BarChartCard
              title="Collections split"
              total={invoiceTotal}
              data={invoiceCollectionData}
              size={chartSize}
              yLabel="Invoices"
            />
          </div>
        </motion.section>

        <motion.section className="rounded-2xl border border-slate-200/80 bg-white/90 p-3 md:p-3.5">
          <SectionHeader
            title="Financial overview"
            icon={Coins}
            total={invoiceTotal}
          />
          <div className="mt-2.5 grid gap-0 divide-y divide-slate-200/70 lg:grid-cols-2 lg:divide-y-0 lg:divide-x lg:divide-slate-200/70">
            <BarChartCard
              title="Risk exposure"
              total={invoiceTotal}
              data={invoiceRiskData}
              size={chartSize}
              yLabel="Invoices"
            />
            <BarChartCard
              title="Receivables (%)"
              data={invoicePercentData}
              valueFormatter={(value) => `${Math.round(value)}%`}
              yDomain={[0, 100]}
              size={chartSize}
              yLabel="%"
            />
          </div>
        </motion.section>
      </div>
    )
  }

  return (
    <div className="grid grid-cols-1 gap-3 lg:grid-cols-2 2xl:grid-cols-4">
      <motion.section className="rounded-2xl border border-slate-200/80 bg-white/90 p-3 md:p-3.5">
        <SectionHeader title="Prequalification overview" icon={Activity} total={preqTotal} />
        <div className="mt-2.5">
          <BarChartCard
            title="Prequalification status"
            total={preqTotal}
            data={preqStatusData}
            size={chartSize}
            yLabel="Rounds"
          />
        </div>
      </motion.section>

      <motion.section className="rounded-2xl border border-slate-200/80 bg-white/90 p-3 md:p-3.5">
        <SectionHeader title="RFQ activity" icon={FileText} total={rfqTotal} />
        <div className="mt-2.5">
          <BarChartCard
            title="RFQ pipeline"
            total={rfqTotal}
            data={rfqStatusData}
            size={chartSize}
            yLabel="RFQs"
          />
        </div>
      </motion.section>

      <motion.section className="rounded-2xl border border-slate-200/80 bg-white/90 p-3 md:p-3.5">
        <SectionHeader title="Tender opportunities" icon={FileSearch} total={tenderCardTotal} />
        <div className="mt-2.5">
          <BarChartCard
            title="Tender status"
            total={tenderTotal}
            data={tenderStatusData}
            size={chartSize}
            yLabel="Tenders"
          />
        </div>
      </motion.section>

      <motion.section className="rounded-2xl border border-slate-200/80 bg-white/90 p-3 md:p-3.5">
        <SectionHeader title="Bid performance" icon={FileCheck2} total={bidCardTotal} />
        <div className="mt-2.5">
          <BarChartCard
            title="Bid status"
            total={bidTotal}
            data={bidStatusData}
            size={chartSize}
            yLabel="Bids"
          />
        </div>
      </motion.section>
    </div>
  )
}
