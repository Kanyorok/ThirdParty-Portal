'use client'

import { useState, useEffect } from 'react'
import { useProcurementStore } from "@/store/use-procurement-store"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { Button } from "@/components/common/button"
import { toast } from "sonner"
import { CheckCircle, Save, X } from "lucide-react"
import { Spinner } from "@/components/common/spinner"
import { PrequalificationRound } from "@/types/procurement/types"

export default function RoundApplication({ roundId }: { roundId: number }) {
    const { rounds, initializeApplication, saveApplicationProgress, submitApplication, isLoading } = useProcurementStore()
    const [appData, setAppData] = useState<any>(null)
    const [selectedCategoryId, setSelectedCategoryId] = useState<string>("")
    const [files, setFiles] = useState<{ file: File, description: string }[]>([])

    const currentRound = rounds.find((r: PrequalificationRound) => r.id === roundId)

    useEffect(() => {
        const init = async () => {
            try {
                const data = await initializeApplication(roundId)
                setAppData(data)
                if (data?.CategoryID) setSelectedCategoryId(String(data.CategoryID))
            } catch {
                toast.error("Failed to initialize application")
            }
        }
        init()
    }, [roundId, initializeApplication])

    const handleSave = async () => {
        if (!selectedCategoryId) return toast.error("Please select a category")

        const formData = new FormData()
        formData.append('CategoryID', selectedCategoryId)

        files.forEach((item, index) => {
            formData.append(`documents[${index}][file]`, item.file)
            formData.append(`documents[${index}][Description]`, item.description)
        })

        try {
            const result = await saveApplicationProgress(appData.ApplicationID, formData)
            setAppData(result)
            setFiles([])
            toast.success("Progress saved successfully")
        } catch {
            toast.error("Failed to save progress")
        }
    }

    const handleSubmit = async () => {
        try {
            await submitApplication(appData.ApplicationID)
            toast.success("Application submitted successfully")
        } catch (e: any) {
            toast.error(e.message || "Submission failed")
        }
    }

    if (!appData) return (
        <div className="flex items-center justify-center p-20 border-4 border-black border-dashed">
            <Spinner className="h-8 w-8 animate-spin text-black" />
        </div>
    )

    return (
        <div className="space-y-8 p-6 border-4 border-black bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
            <section className="space-y-4">
                <div className="flex items-center gap-2">
                    <div className="h-8 w-2 bg-black" />
                    <h2 className="font-black uppercase italic text-2xl tracking-tighter">01. Select Category</h2>
                </div>
                <Select value={selectedCategoryId} onValueChange={setSelectedCategoryId}>
                    <SelectTrigger className="rounded-none border-2 border-black font-black h-14 uppercase italic bg-zinc-50">
                        <SelectValue placeholder="CHOOSE TARGET CATEGORY" />
                    </SelectTrigger>
                    <SelectContent className="rounded-none border-2 border-black">
                        {currentRound?.targetedCategories?.map((cat) => (
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
                    <h2 className="font-black uppercase italic text-2xl tracking-tighter">02. Upload Documents</h2>
                </div>

                <div className="relative border-2 border-black border-dashed p-8 bg-zinc-50 text-center group hover:bg-zinc-100 transition-colors">
                    <input
                        type="file"
                        multiple
                        onChange={(e) => {
                            const newFiles = Array.from(e.target.files || [])
                            const mapped = newFiles.map(file => ({ file, description: file.name }))
                            setFiles(prev => [...prev, ...mapped])
                        }}
                        className="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                    />
                    <p className="font-black uppercase italic text-sm">Click or Drag files here to upload</p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                    {files.map((f, i) => (
                        <div key={i} className="flex items-center justify-between p-3 border-2 border-black bg-white">
                            <span className="text-[10px] font-black uppercase truncate pr-4">{f.file.name}</span>
                            <button
                                onClick={() => setFiles(prev => prev.filter((_, idx) => idx !== i))}
                                className="text-red-600 hover:bg-red-50 p-1"
                            >
                                <X className="h-4 w-4 stroke-[3px]" />
                            </button>
                        </div>
                    ))}
                </div>
            </section>

            <div className="flex flex-wrap gap-4 pt-6 border-t-4 border-black">
                <Button
                    onClick={handleSave}
                    disabled={isLoading}
                    className="rounded-none bg-white text-black border-2 border-black hover:bg-zinc-100 font-black uppercase italic h-12 px-8 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all"
                >
                    {isLoading ? <Spinner className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                    Save Progress
                </Button>

                <Button
                    onClick={handleSubmit}
                    disabled={isLoading || !selectedCategoryId}
                    className="rounded-none bg-black text-white border-2 border-black hover:bg-zinc-900 font-black uppercase italic h-12 px-8 shadow-[4px_4px_0px_0px_rgba(255,255,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all"
                >
                    <CheckCircle className="mr-2 h-4 w-4" />
                    Submit Final Application
                </Button>
            </div>
        </div>
    )
}
