"use client"

import { useEffect, useState } from "react"
import { useParams } from "next/navigation"

type POLine = { id: number; item_id: number; description: string; qty: number; unit_price: number; line_total: number }
type PO = { id: number; po_no: string; po_date: string; display_ref: string | null; total_incl: number; total_excl: number; total_tax: number }

export default function SupplierPOViewPage() {
  const params = useParams()
  const poId = params?.id as string
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [order, setOrder] = useState<PO | null>(null)
  const [lines, setLines] = useState<POLine[]>([])

  useEffect(() => {
    async function load() {
      setLoading(true)
      setError(null)
      try {
        const res = await fetch(`/api/supplier-pos/${encodeURIComponent(poId)}`, { cache: "no-store" })
        const json = await res.json()
        if (!res.ok) throw new Error(json?.error || `Failed ${res.status}`)
        setOrder(json?.data?.order || null)
        setLines(json?.data?.lines || [])
      } catch (e: any) {
        setError(e?.message || "Failed to load PO")
      } finally {
        setLoading(false)
      }
    }
    if (poId) load()
  }, [poId])

  const handlePrint = () => {
    window.print()
  }

  return (
    <div className="px-4 py-6 md:px-8">
      <div className="mb-6 flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Purchase Order</h1>
          <p className="text-muted-foreground">PO details and print view</p>
        </div>
        <button onClick={handlePrint} className="px-4 py-2 border rounded">Print</button>
      </div>
      {loading && <div>Loading...</div>}
      {error && <div className="text-red-600">{error}</div>}
      {!loading && !error && order && (
        <div className="space-y-6 bg-white p-4 border rounded">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <div><span className="font-medium">PO No:</span> {order.po_no}</div>
              <div><span className="font-medium">Date:</span> {order.po_date ? new Date(order.po_date).toLocaleDateString() : ''}</div>
            </div>
            <div>
              <div><span className="font-medium">Source Ref:</span> {order.display_ref || '-'}</div>
              <div><span className="font-medium">Total (Incl):</span> {Number(order.total_incl || 0).toLocaleString()}</div>
            </div>
          </div>
          <div className="overflow-x-auto">
            <table className="min-w-full border">
              <thead>
                <tr className="bg-gray-50">
                  <th className="p-2 text-left">Item</th>
                  <th className="p-2 text-right">Qty</th>
                  <th className="p-2 text-right">Unit Price</th>
                  <th className="p-2 text-right">Line Total</th>
                </tr>
              </thead>
              <tbody>
                {lines.length === 0 ? (
                  <tr><td className="p-4 text-center" colSpan={4}>No items</td></tr>
                ) : lines.map((ln) => (
                  <tr key={ln.id} className="border-t">
                    <td className="p-2">{ln.description}</td>
                    <td className="p-2 text-right">{Number(ln.qty).toLocaleString()}</td>
                    <td className="p-2 text-right">{Number(ln.unit_price).toLocaleString()}</td>
                    <td className="p-2 text-right">{Number(ln.line_total).toLocaleString()}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  )
}



