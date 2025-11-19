import { Spinner } from "@/components/common/spinner";

export default function Loading() {
    return (
        <div className="">
            <Spinner className="inline-block mr-2 w-5 h-5 animate-spin" />Loading...
        </div>
    )
}