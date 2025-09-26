"use client"

import { useEffect, useState } from "react"
import Link from "next/link"

type SupplierPO = {
  id: number
  po_no: string
  po_date: string
  source_ref: string | null
  source_type: string | null
  total_incl: number
  total_tax: number
  total_excl: number
  display_ref: string | null
}

export default function SupplierPOListPage() {
  const [data, setData] = useState<SupplierPO[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    async function load() {
      setLoading(true)
      setError(null)
      try {
        const res = await fetch("/api/supplier-pos", { cache: "no-store" })
        const json = await res.json()
        if (!res.ok) throw new Error(json?.error || `Failed ${res.status}`)
        setData(json?.data || [])
      } catch (e: any) {
        setError(e?.message || "Failed to load POs")
      } finally {
        setLoading(false)
      }
    }
    load()
  }, [])

  return (
    <div className="px-4 py-6 md:px-8">
      <div className="mb-6 flex flex-col gap-2">
        <h1 className="text-2xl font-semibold tracking-tight">Purchase Orders</h1>
        <p className="text-muted-foreground">Your LPOs from RFQs or Tenders</p>
      </div>
      {loading && <div>Loading...</div>}
      {error && <div className="text-red-600">{error}</div>}
      {!loading && !error && (
        <div className="overflow-x-auto">
          <table className="min-w-full border">
            <thead>
              <tr className="bg-gray-50">
                <th className="p-2 text-left">PO No</th>
                <th className="p-2 text-left">Date</th>
                <th className="p-2 text-left">Source</th>
                <th className="p-2 text-right">Total (Incl)</th>
                <th className="p-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              {data.length === 0 ? (
                <tr><td className="p-4 text-center" colSpan={5}>No POs found</td></tr>
              ) : data.map((row) => (
                <tr key={row.id} className="border-t">
                  <td className="p-2">{row.po_no}</td>
                  <td className="p-2">{row.po_date ? new Date(row.po_date).toLocaleDateString() : ''}</td>
                  <td className="p-2">{row.display_ref || row.source_ref || '-'}</td>
                  <td className="p-2 text-right">{Number(row.total_incl || 0).toLocaleString()}</td>
                  <td className="p-2 text-center">
                    <Link href={`/dashboard/po/${row.id}`} className="underline">View</Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}



