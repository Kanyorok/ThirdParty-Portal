import { Badge } from "@/components/common/badge"
import { cn } from "@/lib/utils"
import { applicationStatusClasses, applicationStatusLabel, type ApplicationStatusCode } from "@/lib/prequalification"

export default function ApplicationStatusBadge({
    status = "S",
    className,
}: {
    status?: ApplicationStatusCode
    className?: string
}) {
    return (
        <Badge className={cn("border-0 font-normal", applicationStatusClasses(status), className)}>
            {applicationStatusLabel(status)}
        </Badge>
    )
}
