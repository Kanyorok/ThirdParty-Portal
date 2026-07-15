import * as React from "react"
import { cn } from "@/lib/utils"

const NativeSelect = React.forwardRef<HTMLSelectElement, React.SelectHTMLAttributes<HTMLSelectElement>>(
    ({ className, children, ...props }, ref) => (
        <select
            ref={ref}
            className={cn(
                "h-12 w-full appearance-none border border-input bg-transparent px-4 text-base text-foreground outline-none transition-colors focus:border-ring focus:ring-[3px] focus:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 lg:h-14",
                className,
            )}
            {...props}
        >
            {children}
        </select>
    ),
)
NativeSelect.displayName = "NativeSelect"

const NativeSelectOption = React.forwardRef<HTMLOptionElement, React.OptionHTMLAttributes<HTMLOptionElement>>(
    ({ className, ...props }, ref) => (
        <option ref={ref} className={cn("bg-background text-foreground", className)} {...props} />
    ),
)
NativeSelectOption.displayName = "NativeSelectOption"

export { NativeSelect, NativeSelectOption }
