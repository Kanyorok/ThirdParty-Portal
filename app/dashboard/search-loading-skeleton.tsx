import { cn } from "@/lib/utils"

export function SearchSkeleton() {
    return (
        <div className="space-y-4 p-2 animate-in fade-in duration-500">
            <div className="space-y-2">
                <div className="h-3 w-24 rounded bg-primary/10 mx-2 mb-3" />
                {[...Array(3)].map((_, i) => (
                    <div
                        key={i}
                        className="flex items-center gap-3 rounded-lg p-2.5 bg-muted/20 border border-transparent"
                    >
                        <div className="size-8 shrink-0 rounded-md bg-muted/40 animate-pulse" />
                        <div className="flex flex-col gap-2 flex-1 min-w-0">
                            <div className="h-2.5 w-1/3 rounded bg-muted/60 animate-pulse" />
                            <div className="h-2 w-2/3 rounded bg-muted/30 animate-pulse" />
                        </div>
                    </div>
                ))}
            </div>
        </div>
    )
}