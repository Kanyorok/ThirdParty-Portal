export default function Loading() {
    return (
        <div className="space-y-4">
            <div className="h-8 w-40 rounded-md bg-muted" />
            <div className="rounded-lg border">
                <div className="flex items-center justify-between gap-3 border-b p-4">
                    <div className="h-9 w-full max-w-sm rounded-md bg-muted" />
                    <div className="flex gap-2">
                        <div className="h-9 w-28 rounded-md bg-muted" />
                        <div className="h-9 w-28 rounded-md bg-muted" />
                    </div>
                </div>
                <div className="p-4">
                    <div className="h-10 w-full rounded-md bg-muted" />
                    <div className="mt-3 h-10 w-full rounded-md bg-muted" />
                    <div className="mt-3 h-10 w-full rounded-md bg-muted" />
                </div>
            </div>
        </div>
    )
}
