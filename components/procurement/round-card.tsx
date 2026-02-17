"use client"

import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/common/card"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { CalendarDays, ArrowRight, Clock } from "lucide-react"
import { PrequalificationRound } from "@/types/procurement/types"
import { useProcurementStore } from "@/store/use-procurement-store"
import Link from "next/link"

export function RoundCard({ round }: { round: PrequalificationRound }) {
    const setSelectedRound = useProcurementStore((state) => state.setSelectedRound)

    return (
        <Card className="flex flex-col h-full hover:shadow-md transition-all border-l-4 border-l-primary">
            <CardHeader>
                <div className="flex justify-between items-start gap-2">
                    <CardTitle className="text-xl font-bold line-clamp-1">{round.title}</CardTitle>
                    <Badge variant={round.status === 'O' ? "default" : "secondary"}>
                        {round.status === 'O' ? 'Open' : 'Closed'}
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
                        Ends: {new Date(round.dates.end).toLocaleDateString('en-KE')}
                    </div>
                    {round.dates.isClosingSoon && (
                        <div className="flex items-center text-xs font-medium text-destructive animate-pulse">
                            <Clock className="mr-1 h-3 w-3" />
                            Closing Soon
                        </div>
                    )}
                </div>

                <div className="flex flex-wrap gap-2">
                    {round.targetedCategories?.map((cat) => (
                        <Badge key={cat.id} variant="outline" className="text-[10px]">
                            {cat.name}
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
