"use client"

import { useEffect, useMemo, useState } from "react"
import { useRouter, useSearchParams } from "next/navigation"
import ApplicationForm from "./application-form"

export default function ApplicationPageClient() {
    const searchParams = useSearchParams()
    const router = useRouter()
    const roundId = useMemo(() => searchParams?.get("roundId") ?? undefined, [searchParams])
    const [open, setOpen] = useState(true)

    useEffect(() => {
        setOpen(true)
    }, [roundId])

    const handleOpenChange = (value: boolean) => {
        setOpen(value)
        if (!value) {
            router.replace("/dashboard/supplier/prequalification")
        }
    }

    return (
        <div className="min-h-screen bg-slate-50 py-8 px-4 md:px-6">
            <ApplicationForm
                open={open}
                onOpenChange={handleOpenChange}
                defaultRoundId={roundId}
            >
            </ApplicationForm>
        </div>
    )
}
