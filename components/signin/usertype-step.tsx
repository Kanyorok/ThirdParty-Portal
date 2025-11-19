"use client"

import { useCallback } from "react"
import { User, Building2 } from "lucide-react"
import { UserTypeValue } from "@/types/types"

interface Option {
    key: UserTypeValue
    display: {
        label: string
        description: string
        icon: typeof User
    }
}

const OPTIONS: readonly Option[] = [
    {
        key: { value: "tenant" },
        display: {
            label: "For Tenants",
            description: "Find your next home.",
            icon: User
        }
    },
    {
        key: { value: "supplier" },
        display: {
            label: "For Suppliers",
            description: "Supplier of goods and services.",
            icon: Building2
        }
    }
]

interface Props {
    onSelect: (t: UserTypeValue) => void
}

export default function UserTypeStep({ onSelect }: Props) {
    const handleSelect = useCallback(
        (key: UserTypeValue) => {
            onSelect(key)
        },
        [onSelect]
    )

    const render = useCallback(
        (o: Option) => {
            const Icon = o.display.icon
            return (
                <div
                    key={o.key.value}
                    onClick={() => handleSelect(o.key)}
                    role="button"
                    tabIndex={0}
                    onKeyDown={(e) => {
                        if (e.key === "Enter" || e.key === " ") handleSelect(o.key)
                    }}
                    className="p-6 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:border-blue-300 cursor-pointer transition-all"
                >
                    <div className="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                        <Icon className="w-5 h-5" />
                    </div>
                    <h3 className="text-lg font-semibold text-gray-900">{o.display.label}</h3>
                    <p className="text-sm text-gray-600 mt-1">{o.display.description}</p>
                    <span className="text-blue-600 font-medium mt-4 inline-block">Continue →</span>
                </div>
            )
        },
        [handleSelect]
    )

    return (
        <div className="space-y-6">
            <h1 className="text-center text-gray-600 text-lg">Join Craft Portal & Get Started</h1>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {OPTIONS.map(render)}
            </div>
        </div>
    )
}
