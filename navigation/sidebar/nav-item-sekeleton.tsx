export function NavItemSkeleton() {
    return (
        <div className="flex items-center gap-3 px-3.5 h-11 w-full">
            <div className="size-7 rounded-lg bg-muted animate-pulse shrink-0" />
            <div className="h-3 w-24 bg-muted animate-pulse rounded-md" />
            <div className="ml-auto h-3 w-8 bg-muted/50 animate-pulse rounded-md" />
        </div>
    )
}