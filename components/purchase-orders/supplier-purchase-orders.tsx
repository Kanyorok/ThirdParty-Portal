"use client"

import { useCallback, useEffect, useMemo, useState } from "react"
import { CalendarDays, Eye, FileText, Loader2, RefreshCw, Search, ShoppingBag } from "lucide-react"

import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/common/dialog"
import { Input } from "@/components/common/input"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table"
import { parseJsonResponse } from "@/lib/parse-json-response"
import { cn } from "@/lib/utils"
import type {
    SupplierPurchaseOrderDetail,
    SupplierPurchaseOrderDetailResponse,
    SupplierPurchaseOrderListResponse,
    SupplierPurchaseOrderSummary,
} from "@/types/purchase-orders"

function money(value: number, currency: string) {
    return new Intl.NumberFormat("en-KE", {
        style: "currency",
        currency: currency || "KES",
        minimumFractionDigits: 2,
    }).format(Number(value || 0))
}

function displayDate(value: string | null) {
    if (!value) return "—"
    const date = new Date(`${value}T00:00:00`)
    return Number.isNaN(date.getTime())
        ? value
        : new Intl.DateTimeFormat("en-KE", { day: "2-digit", month: "short", year: "numeric" }).format(date)
}

export function SupplierPurchaseOrders() {
    const [orders, setOrders] = useState<SupplierPurchaseOrderSummary[]>([])
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState<string | null>(null)
    const [search, setSearch] = useState("")
    const [selectedOrder, setSelectedOrder] = useState<SupplierPurchaseOrderDetail | null>(null)
    const [detailOpen, setDetailOpen] = useState(false)
    const [detailLoading, setDetailLoading] = useState(false)

    const loadOrders = useCallback(async () => {
        setLoading(true)
        setError(null)
        try {
            const response = await fetch("/api/procurement/purchase-orders", { cache: "no-store" })
            const payload = await parseJsonResponse<SupplierPurchaseOrderListResponse & { message?: string }>(response)
            if (!response.ok) throw new Error(payload?.message || "Unable to load purchase orders")
            setOrders(Array.isArray(payload?.data) ? payload.data : [])
        } catch (loadError) {
            setError(loadError instanceof Error ? loadError.message : "Unable to load purchase orders")
        } finally {
            setLoading(false)
        }
    }, [])

    useEffect(() => {
        void loadOrders()
    }, [loadOrders])

    const filteredOrders = useMemo(() => {
        const term = search.trim().toLowerCase()
        if (!term) return orders
        return orders.filter((order) => [order.orderNumber, order.rfqReference, order.description]
            .some((value) => String(value ?? "").toLowerCase().includes(term)))
    }, [orders, search])

    const openOrder = async (order: SupplierPurchaseOrderSummary) => {
        setDetailOpen(true)
        setDetailLoading(true)
        setSelectedOrder(null)
        try {
            const response = await fetch(`/api/procurement/purchase-orders/${order.id}`, { cache: "no-store" })
            const payload = await parseJsonResponse<SupplierPurchaseOrderDetailResponse & { message?: string }>(response)
            if (!response.ok || !payload?.data) throw new Error(payload?.message || "Unable to load purchase order")
            setSelectedOrder(payload.data)
        } catch (loadError) {
            setError(loadError instanceof Error ? loadError.message : "Unable to load purchase order")
            setDetailOpen(false)
        } finally {
            setDetailLoading(false)
        }
    }

    return (
        <div className="w-full space-y-6 py-4">
            <header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div className="space-y-1">
                    <div className="flex items-center gap-2.5">
                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary"><ShoppingBag className="h-4.5 w-4.5" /></div>
                        <h1 className="text-xl font-semibold tracking-tight sm:text-2xl">Purchase Orders</h1>
                    </div>
                    <p className="pl-11 text-sm text-muted-foreground">View fully approved purchase orders issued to your company.</p>
                </div>
                <div className="flex items-center gap-2">
                    <Badge variant="outline" className="rounded-full px-3 py-1 text-xs">{orders.length} approved</Badge>
                    <Button variant="outline" size="sm" onClick={() => void loadOrders()} disabled={loading} className="gap-2 rounded-xl">
                        <RefreshCw className={cn("h-3.5 w-3.5", loading && "animate-spin")} /> Refresh
                    </Button>
                </div>
            </header>

            <div className="flex items-center gap-3 rounded-2xl border border-border/60 bg-card px-4 shadow-sm">
                <Search className="h-4 w-4 text-muted-foreground" />
                <Input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Search by PO number, RFQ reference or description" className="h-12 border-0 bg-transparent px-0 shadow-none focus-visible:ring-0" />
            </div>

            {error ? <div className="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{error}</div> : null}

            <OrdersTable orders={filteredOrders} loading={loading} onOpen={openOrder} />
            <OrderDialog open={detailOpen} onOpenChange={setDetailOpen} loading={detailLoading} order={selectedOrder} />
        </div>
    )
}

function OrdersTable({
    orders,
    loading,
    onOpen,
}: {
    orders: SupplierPurchaseOrderSummary[]
    loading: boolean
    onOpen: (order: SupplierPurchaseOrderSummary) => void
}) {
    return (
        <div className="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div className="overflow-x-auto">
                <Table>
                    <TableHeader className="bg-muted/35">
                        <TableRow>
                            <TableHead className="min-w-[170px] pl-6">Purchase order</TableHead>
                            <TableHead className="min-w-[140px]">RFQ reference</TableHead>
                            <TableHead className="min-w-[130px]">Order date</TableHead>
                            <TableHead className="min-w-[90px]">Items</TableHead>
                            <TableHead className="min-w-[160px] text-right">Total payable</TableHead>
                            <TableHead className="min-w-[110px] text-center">Status</TableHead>
                            <TableHead className="w-[100px] pr-6 text-right">Action</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {loading ? (
                            <TableRow><TableCell colSpan={7} className="h-40 text-center"><span className="inline-flex items-center gap-2 text-sm text-muted-foreground"><Loader2 className="h-4 w-4 animate-spin" /> Loading purchase orders</span></TableCell></TableRow>
                        ) : orders.length === 0 ? (
                            <TableRow>
                                <TableCell colSpan={7} className="h-48 text-center">
                                    <div className="mx-auto flex max-w-sm flex-col items-center gap-2 text-muted-foreground">
                                        <FileText className="h-8 w-8 opacity-50" />
                                        <div className="font-medium text-foreground">No purchase orders found</div>
                                        <p className="text-xs">Approved orders issued to your supplier account will appear here.</p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        ) : orders.map((order) => (
                            <TableRow key={order.id} className="hover:bg-muted/25">
                                <TableCell className="pl-6">
                                    <div className="font-semibold text-foreground">{order.orderNumber || `PO #${order.id}`}</div>
                                    {order.description ? <div className="mt-1 max-w-[260px] truncate text-xs text-muted-foreground">{order.description}</div> : null}
                                </TableCell>
                                <TableCell className="text-sm">{order.rfqReference || "—"}</TableCell>
                                <TableCell><span className="inline-flex items-center gap-2 text-sm"><CalendarDays className="h-3.5 w-3.5 text-muted-foreground" />{displayDate(order.orderDate)}</span></TableCell>
                                <TableCell className="text-sm tabular-nums">{order.lineCount}</TableCell>
                                <TableCell className="text-right font-semibold tabular-nums">{money(order.totals.includingTax, order.currency)}</TableCell>
                                <TableCell className="text-center"><Badge className="bg-emerald-100 text-emerald-700 hover:bg-emerald-100">Approved</Badge></TableCell>
                                <TableCell className="pr-6 text-right"><Button variant="outline" size="sm" onClick={() => onOpen(order)} className="gap-2 rounded-xl"><Eye className="h-3.5 w-3.5" /> View</Button></TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>
        </div>
    )
}

function OrderDialog({
    open,
    onOpenChange,
    loading,
    order,
}: {
    open: boolean
    onOpenChange: (open: boolean) => void
    loading: boolean
    order: SupplierPurchaseOrderDetail | null
}) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[92vh] w-[calc(100vw-2rem)] max-w-[calc(100vw-2rem)] overflow-y-auto p-0 sm:max-w-[calc(100vw-3rem)] lg:max-w-6xl xl:max-w-7xl">
                <DialogHeader className="border-b px-6 py-5"><DialogTitle>Purchase Order {order?.orderNumber || ""}</DialogTitle></DialogHeader>
                {loading ? (
                    <div className="flex h-64 items-center justify-center gap-2 text-sm text-muted-foreground"><Loader2 className="h-4 w-4 animate-spin" /> Loading purchase order</div>
                ) : order ? <OrderDetails order={order} /> : null}
            </DialogContent>
        </Dialog>
    )
}

function OrderDetails({ order }: { order: SupplierPurchaseOrderDetail }) {
    return (
        <div className="space-y-5 p-6">
            <div className="grid gap-3 rounded-2xl border bg-muted/20 p-4 sm:grid-cols-2 lg:grid-cols-4">
                <div><div className="text-xs text-muted-foreground">Order number</div><div className="mt-1 font-semibold">{order.orderNumber}</div></div>
                <div><div className="text-xs text-muted-foreground">RFQ reference</div><div className="mt-1 font-semibold">{order.rfqReference || "—"}</div></div>
                <div><div className="text-xs text-muted-foreground">Order date</div><div className="mt-1 font-semibold">{displayDate(order.orderDate)}</div></div>
                <div><div className="text-xs text-muted-foreground">Delivery date</div><div className="mt-1 font-semibold">{displayDate(order.deliveryDate)}</div></div>
                <div className="sm:col-span-2"><div className="text-xs text-muted-foreground">Supplier</div><div className="mt-1 font-semibold">{order.supplierName || "—"}</div></div>
                <div className="sm:col-span-2"><div className="text-xs text-muted-foreground">Payment terms</div><div className="mt-1 font-semibold">{order.paymentTerms || "—"}</div></div>
            </div>

            <div className="overflow-x-auto rounded-2xl border">
                <Table>
                    <TableHeader className="bg-muted/35">
                        <TableRow>
                            <TableHead>Item</TableHead>
                            <TableHead>Qty</TableHead>
                            <TableHead className="text-right">Unit price excl.</TableHead>
                            <TableHead className="text-right">Tax</TableHead>
                            <TableHead className="text-right">Net</TableHead>
                            <TableHead className="text-right">Gross</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {order.lines.map((line) => (
                            <TableRow key={line.id}>
                                <TableCell><div className="font-medium">{line.itemName || "Item"}</div><div className="text-xs text-muted-foreground">{line.itemCode || line.description || "—"}</div></TableCell>
                                <TableCell className="tabular-nums">{line.quantity}</TableCell>
                                <TableCell className="text-right tabular-nums">{money(line.unitPriceExcludingTax, order.currency)}</TableCell>
                                <TableCell className="text-right tabular-nums">{line.taxRate.toFixed(2)}%</TableCell>
                                <TableCell className="text-right tabular-nums">{money(line.netAmount, order.currency)}</TableCell>
                                <TableCell className="text-right font-semibold tabular-nums">{money(line.grossAmount, order.currency)}</TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>

            <div className="ml-auto w-full max-w-sm space-y-2 rounded-2xl border bg-muted/20 p-4 text-sm">
                <div className="flex justify-between"><span className="text-muted-foreground">Discount</span><span>{money(order.totals.discount, order.currency)}</span></div>
                <div className="flex justify-between"><span className="text-muted-foreground">Exclusive total</span><span>{money(order.totals.excludingTax, order.currency)}</span></div>
                <div className="flex justify-between"><span className="text-muted-foreground">Tax amount</span><span>{money(order.totals.tax, order.currency)}</span></div>
                <div className="flex justify-between border-t pt-2 text-base font-semibold"><span>Total payable</span><span>{money(order.totals.includingTax, order.currency)}</span></div>
            </div>
        </div>
    )
}
