import { Spinner } from "@/components/common/spinner"

export default function Loading() {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center px-4">
            <Spinner className="h-16 w-16" />
        </div>
    )
}