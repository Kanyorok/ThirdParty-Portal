"use client"

import { useEffect, useState } from "react"
import { useProcurementStore } from "@/store/use-procurement-store"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from "@/components/common/select"
import { Button } from "@/components/common/button"
import { toast } from "sonner"
import { CheckCircle, X } from "lucide-react"
import { Spinner } from "@/components/common/spinner"

export default function RoundApplication({ roundId }: { roundId: number }) {
    const selectedRound = useProcurementStore(s => s.selectedRound)
    const fetchRoundDetails = useProcurementStore(s => s.fetchRoundDetails)
    const submitApplication = useProcurementStore(s => s.submitApplication)
    const fetchApplicationProgress = useProcurementStore(s => s.fetchApplicationProgress)
    const isLoading = useProcurementStore(s => s.isLoading)

    const [selectedCategoryId, setSelectedCategoryId] = useState("")
    const [files, setFiles] = useState<File[]>([])
    const [hasSubmitted, setHasSubmitted] = useState(false)

    useEffect(() => {
        fetchRoundDetails(roundId)
    }, [roundId, fetchRoundDetails])

    const handleSubmit = async () => {
        if (!selectedCategoryId) {
            toast.error("Please select a category")
            return
        }

        try {
            await submitApplication({
                round_id: roundId,
                category_id: Number(selectedCategoryId),
                documents: files
            })
            await fetchApplicationProgress(roundId)
            setHasSubmitted(true)
            setFiles([])
            toast.success("Application submitted successfully")
        } catch (err: any) {
            toast.error(err?.message ?? "Submission failed")
        }
    }

    if (!selectedRound) {
        return (
            <div className="flex items-center justify-center p-20 border-4 border-black border-dashed">
                <Spinner className="h-8 w-8 animate-spin text-black" />
            </div>
        )
    }

    return (
        <div className="space-y-8 p-6 border-4 border-black bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
            <section className="space-y-4">
                <div className="flex items-center gap-2">
                    <div className="h-8 w-2 bg-black" />
                    <h2 className="font-black uppercase italic text-2xl tracking-tighter">
                        01. Select Category
                    </h2>
                </div>

                <Select value={selectedCategoryId} onValueChange={setSelectedCategoryId}>
                    <SelectTrigger className="rounded-none border-2 border-black font-black h-14 uppercase italic bg-zinc-50">
                        <SelectValue placeholder="CHOOSE TARGET CATEGORY" />
                    </SelectTrigger>
                    <SelectContent className="rounded-none border-2 border-black">
                        {selectedRound.categories?.map((cat: any) => (
                            <SelectItem
                                key={cat.id}
                                value={String(cat.id)}
                                className="font-bold uppercase italic"
                            >
                                {cat.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </section>

            <section className="space-y-4">
                <div className="flex items-center gap-2">
                    <div className="h-8 w-2 bg-black" />
                    <h2 className="font-black uppercase italic text-2xl tracking-tighter">
                        02. Upload Documents
                    </h2>
                </div>

                <div className="relative border-2 border-black border-dashed p-8 bg-zinc-50 text-center hover:bg-zinc-100 transition-colors">
                    <input
                        type="file"
                        multiple
                        onChange={(e) => {
                            const selected = Array.from(e.target.files || [])
                            setFiles(prev => [...prev, ...selected])
                        }}
                        className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    />
                    <p className="font-black uppercase italic text-sm">
                        Click or drag files here
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                    {files.map((file, i) => (
                        <div
                            key={i}
                            className="flex items-center justify-between p-3 border-2 border-black bg-white"
                        >
                            <span className="text-[10px] font-black uppercase truncate pr-4">
                                {file.name}
                            </span>
                            <button
                                onClick={() =>
                                    setFiles(prev => prev.filter((_, idx) => idx !== i))
                                }
                                className="text-red-600 p-1"
                            >
                                <X className="h-4 w-4 stroke-[3px]" />
                            </button>
                        </div>
                    ))}
                </div>
            </section>

            <div className="flex flex-wrap gap-4 pt-6 border-t-4 border-black">
                <Button
                    onClick={handleSubmit}
                    disabled={isLoading || hasSubmitted}
                    className="rounded-none bg-black text-white border-2 border-black font-black uppercase italic h-12 px-8 shadow-[4px_4px_0px_0px_rgba(255,255,0,1)]"
                >
                    {isLoading ? (
                        <Spinner className="mr-2 h-4 w-4 animate-spin" />
                    ) : (
                        <CheckCircle className="mr-2 h-4 w-4" />
                    )}
                    Submit Application
                </Button>
            </div>
        </div>
    )
}
