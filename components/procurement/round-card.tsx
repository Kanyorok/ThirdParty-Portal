"use client"

import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/common/card"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { CalendarDays, ArrowRight, Clock } from "lucide-react"
import { useProcurementStore } from "@/store/use-procurement-store"
import type { Round } from "@/types/types"
import Link from "next/link"

export function RoundCard({ round }: { round: Round }) {
    const setSelectedRound = useProcurementStore((state) => state.setSelectedRound)
    const categories = round.categories ?? []
    const roundStatus = typeof round.status === "string" ? round.status : round.status?.value ?? ""
    const isOpen = String(roundStatus).toLowerCase() === "o" || String(roundStatus).toLowerCase() === "open"

    return (
        <Card className="flex flex-col h-full hover:shadow-md transition-all border-l-4 border-l-primary">
            <CardHeader>
                <div className="flex justify-between items-start gap-2">
                    <CardTitle className="text-xl font-bold line-clamp-1">{round.title}</CardTitle>
                    <Badge variant={isOpen ? "default" : "secondary"}>
                        {isOpen ? 'Open' : 'Closed'}
                    </Badge>
                </div>
                <CardDescription className="line-clamp-2 min-h-[40px]">
                    {round.description}
                </CardDescription>
            </CardHeader>

            <CardContent className="flex-grow space-y-4">
                <div className="space-y-2">
                    <div className="flex items-center text-sm text-muted-foreground">
                        <CalendarDays className="mr-2 h-4 w-4" />
                        Ends: {new Date(round.endDate).toLocaleDateString('en-KE')}
                    </div>
                    {round.isExpired === false && (
                        <div className="flex items-center text-xs font-medium text-destructive animate-pulse">
                            <Clock className="mr-1 h-3 w-3" />
                            Closing Soon
                        </div>
                    )}
                </div>

                <div className="flex flex-wrap gap-2">
                    {categories.map((cat) => (
                        <Badge key={cat.category_id} variant="outline" className="text-[10px]">
                            {cat.category_name}
                        </Badge>
                    ))}
                </div>
            </CardContent>

            <CardFooter>
                <Button
                    className="w-full group"
                    asChild
                    onClick={() => setSelectedRound(round)}
                >
                    <Link href={`/dashboard/supplier/prequalification/application?roundId=${round.id}`}>
                        View Details
                        <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </Link>
                </Button>
            </CardFooter>
        </Card>
    )
}
