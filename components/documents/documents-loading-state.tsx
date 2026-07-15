import Loading from "@/components/common/custom-loader"
import { Spinner } from "@/components/common/spinner"
import { cn } from "@/lib/utils"

type DocumentsLoadingStateProps = {
    inline?: boolean
    className?: string
}

export function DocumentsLoadingState({ inline = false, className }: DocumentsLoadingStateProps) {
    if (inline) {
        return (
            <div className={cn("flex items-center justify-center px-4 py-6", className)}>
                <Spinner label="Loading documents" showLabel className="text-slate-600" />
            </div>
        )
    }

    return <Loading fullScreen={false} message="Loading documents" className={className} />
}