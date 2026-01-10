'use client'

import { useParams, useRouter } from 'next/navigation'
import { useProcurementStore } from "@/store/use-procurement-store"
import RoundApplication from "@/components/procurement/round-application"
import { Button } from "@/components/common/button"
import { ChevronLeft, Calendar, ShieldCheck, Info } from "lucide-react"

export default function PrequalificationApplyPage() {
    const params = useParams()
    const router = useRouter()
    const roundId = Number(params.id)

    const { rounds } = useProcurementStore()
    const round = rounds.find(r => r.id === roundId)

    if (!round) {
        return (
            <div className="flex flex-col items-center justify-center min-h-[60vh] space-y-4">
                <Info className="h-12 w-12 text-zinc-400" />
                <p className="font-black uppercase italic text-xl">Round not found or has expired.</p>
                <Button onClick={() => router.back()} className="rounded-none border-2 border-black font-black uppercase italic">
                    Go Back
                </Button>
            </div>
        )
    }

    return (
        <div className="max-w-7xl mx-auto px-4 py-10">
            <div className="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div className="space-y-4">
                    <button
                        onClick={() => router.back()}
                        className="flex items-center gap-2 text-xs font-black uppercase italic hover:underline"
                    >
                        <ChevronLeft className="h-4 w-4" />
                        Back to Opportunities
                    </button>
                    <h1 className="text-4xl md:text-6xl font-black uppercase italic tracking-tighter leading-none">
                        Submit <br /> Application
                    </h1>
                </div>

                <div className="p-4 bg-yellow-400 border-4 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <p className="text-[10px] font-black uppercase">Closing Date</p>
                    <div className="flex items-center gap-2 font-black italic text-lg">
                        <Calendar className="h-5 w-5" />
                        {round.dates.end}
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <div className="lg:col-span-8">
                    <RoundApplication roundId={roundId} />
                </div>

                <aside className="lg:col-span-4 space-y-6">
                    <div className="border-4 border-black p-6 bg-zinc-900 text-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                        <h3 className="font-black uppercase italic text-xl mb-4 flex items-center gap-2">
                            <ShieldCheck className="h-6 w-6 text-yellow-400" />
                            Guidelines
                        </h3>
                        <ul className="space-y-4 text-xs font-bold uppercase italic leading-relaxed">
                            <li className="flex gap-3">
                                <span className="text-yellow-400">01.</span>
                                <span>Ensure all uploaded documents are clear and in PDF or Image format.</span>
                            </li>
                            <li className="flex gap-3">
                                <span className="text-yellow-400">02.</span>
                                <span>You can save a draft and return later to finish the application.</span>
                            </li>
                            <li className="flex gap-3">
                                <span className="text-yellow-400">03.</span>
                                <span>Once submitted, you cannot edit your category or documents.</span>
                            </li>
                        </ul>
                    </div>

                    <div className="border-4 border-black p-6 bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                        <h3 className="font-black uppercase italic text-sm mb-2">Round Details</h3>
                        <p className="text-sm font-medium text-zinc-600 mb-4">{round.description}</p>

                        <div className="pt-4 border-t-2 border-zinc-100">
                            <p className="text-[10px] font-black uppercase text-zinc-400 mb-2">Eligible Categories</p>
                            <div className="flex flex-wrap gap-2">
                                {round.targetedCategories.map(cat => (
                                    <span key={cat.id} className="text-[9px] font-black uppercase bg-zinc-100 px-2 py-1 border border-black">
                                        {cat.name}
                                    </span>
                                ))}
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    )
}