'use client'

import { useProcurementStore } from "@/store/use-procurement-store"
import { Badge } from "@/components/common/badge"
import { FileText, ExternalLink, Clock, AlertCircle } from "lucide-react"
import { format } from "date-fns"
import { resolveProcurementDocumentName } from "@/lib/procurement-document-name"

export default function ApplicationTracking() {
    const { applicationStatus, rounds } = useProcurementStore()
    const applications = Object.values(applicationStatus)

    const getStatusStyles = (status: string) => {
        switch (status.toLowerCase()) {
            case 'submitted': return 'bg-blue-100 text-blue-700 border-blue-400'
            case 'approved': return 'bg-green-100 text-green-700 border-green-400'
            case 'rejected': return 'bg-red-100 text-red-700 border-red-400'
            default: return 'bg-zinc-100 text-zinc-700 border-zinc-400'
        }
    }

    if (applications.length === 0) {
        return (
            <div className="p-12 border-4 border-black border-dashed text-center bg-white">
                <AlertCircle className="mx-auto h-12 w-12 mb-4" />
                <p className="font-black uppercase italic">No active applications found.</p>
            </div>
        )
    }

    return (
        <div className="space-y-6">
            <h2 className="text-3xl font-black uppercase italic tracking-tighter">Your Applications</h2>

            <div className="grid gap-6">
                {applications.map((app: any) => {
                    const round = rounds.find(r => r.id === app.RoundID)

                    return (
                        <div key={app.ApplicationID} className="border-4 border-black bg-white p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                            <div className="flex flex-col md:flex-row justify-between gap-4 mb-6">
                                <div>
                                    <p className="text-[10px] font-black uppercase text-zinc-500">Round #{app.RoundID}</p>
                                    <h3 className="text-xl font-black uppercase italic">{round?.title || 'Procurement Round'}</h3>
                                    <p className="font-bold text-sm text-zinc-600 uppercase italic">
                                        Category: {app.CategoryName || 'Not Selected'}
                                    </p>
                                </div>
                                <div className="flex items-start md:items-end flex-col gap-2">
                                    <Badge className={`rounded-none border-2 font-black uppercase italic px-4 py-1 ${getStatusStyles(app.Status)}`}>
                                        {app.Status}
                                    </Badge>
                                    <div className="flex items-center gap-2 text-[10px] font-bold uppercase">
                                        <Clock className="h-3 w-3" />
                                        Created: {format(new Date(app.CreatedOn || Date.now()), 'PPP')}
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-3">
                                <p className="text-xs font-black uppercase border-b-2 border-black pb-1">Submitted Documents</p>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    {app.Documents?.map((doc: any) => (
                                        <a
                                            key={doc.Id}
                                            href={doc.Url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="flex items-center justify-between p-3 border-2 border-black bg-zinc-50 hover:bg-yellow-400 transition-colors group"
                                        >
                                            <div className="flex items-center gap-3">
                                                <FileText className="h-4 w-4" />
                                                <div className="min-w-0">
                                                    <span className="block max-w-[150px] truncate text-[10px] font-black uppercase">
                                                        {resolveProcurementDocumentName(doc)}
                                                    </span>
                                                    {doc.Description ? (
                                                        <span className="block max-w-[150px] truncate text-[9px] font-bold text-zinc-500">
                                                            {String(doc.Description).trim()}
                                                        </span>
                                                    ) : null}
                                                </div>
                                            </div>
                                            <ExternalLink className="h-3 w-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                                        </a>
                                    ))}
                                    {(!app.Documents || app.Documents.length === 0) && (
                                        <p className="text-[10px] font-bold italic text-zinc-400">No documents uploaded yet.</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    )
                })}
            </div>
        </div>
    )
}