import { InlineLoading } from "@/components/common/custom-loader"

import { cn } from "@/lib/utils"

interface SpinnerProps extends React.ComponentProps<"div"> {
  label?: string
  showLabel?: boolean
}

function Spinner({ className, label = "Loading", showLabel = false, ...props }: SpinnerProps) {
  return (
    <InlineLoading
      role="status"
      aria-label={label}
      message={label}
      showLabel={showLabel}
      className={cn(showLabel ? undefined : "gap-0", className)}
      {...props}
    />
  )
}

export { Spinner }
