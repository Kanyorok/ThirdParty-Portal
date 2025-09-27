"use client"
import React, { useEffect, useState } from 'react'
import { Input } from '@/components/common/input'
import { Button } from '@/components/common/button'
import { Card, CardContent, CardHeader } from '@/components/common/card'
import { Separator } from '@/components/common/separator'

type DocItem = {
  id: number
  name: string
  repository?: string
  visibility?: string
  size?: number
  version?: number
  mimeType?: string
  createdOn?: string
  modifiedOn?: string
  previewUrl?: string
}

export default function DocumentsPage() {
  const [docs, setDocs] = useState<DocItem[]>([])
  const [q, setQ] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string|null>(null)

  const fetchDocs = async () => {
    setLoading(true)
    setError(null)
    try {
      const url = new URL('/api/dms/documents', window.location.origin)
      if (q) url.searchParams.set('q', q)
      const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } })
      if (!res.ok) {
        const err = await res.json().catch(() => ({} as any))
        throw new Error(err?.error || `HTTP ${res.status}`)
      }
      const data = await res.json()
      setDocs(Array.isArray(data?.data) ? data.data : [])
    } catch (e: any) {
      setError(e?.message || 'Failed to load documents')
      setDocs([])
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { fetchDocs() }, [])

  return (
    <div className="min-h-screen bg-white dark:bg-black text-gray-900 dark:text-gray-50">
      <div className="container mx-auto px-4 py-8 md:py-12">
        <div className="mb-8 text-sm text-gray-500 dark:text-gray-400">Dashboard / <span className="font-semibold">Documents</span></div>

        <Card className="mb-6 border border-gray-200 dark:border-gray-800 rounded-2xl">
          <CardHeader>
            <h1 className="text-2xl font-bold">My Documents (Visible to me)</h1>
          </CardHeader>
          <CardContent>
            <div className="flex gap-3 items-center">
              <Input
                placeholder="Search documents..."
                value={q}
                onChange={(e) => setQ(e.target.value)}
                onKeyUp={(e) => { if (e.key === 'Enter') fetchDocs() }}
              />
              <Button onClick={fetchDocs}>Search</Button>
            </div>
          </CardContent>
        </Card>

        <Separator className="mb-6" />

        {loading ? (
          <div className="text-center py-16">Loading documents...</div>
        ) : error ? (
          <div className="text-center py-16 text-red-600">{error}</div>
        ) : docs.length === 0 ? (
          <div className="text-center py-16">No documents found.</div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            {docs.map((d) => (
              <Card key={d.id} className="border border-gray-200 dark:border-gray-800 rounded-2xl">
                <CardHeader>
                  <div className="font-semibold text-lg line-clamp-2">{d.name}</div>
                  <div className="text-xs text-gray-500 mt-1">{d.repository || '—'} · v{d.version ?? 1}</div>
                </CardHeader>
                <CardContent>
                  <div className="text-sm text-gray-600 dark:text-gray-300 flex flex-col gap-1">
                    <div>Size: {typeof d.size === 'number' ? `${(d.size / 1024).toFixed(1)} KB` : '—'}</div>
                    <div>Modified: {d.modifiedOn ? new Date(d.modifiedOn).toLocaleString() : '—'}</div>
                  </div>
                  <div className="mt-4 flex gap-3">
                    {d.previewUrl && (
                      <a className="text-blue-600 hover:underline" href={d.previewUrl} target="_blank" rel="noreferrer">Preview</a>
                    )}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}

 
