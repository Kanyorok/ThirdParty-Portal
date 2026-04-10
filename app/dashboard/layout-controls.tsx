"use client"

import { useState, useTransition, useCallback } from "react"
import { Settings } from "lucide-react"
import { toast } from "sonner"
import { Button } from "@/components/common/button"
import { Label } from "@/components/common/label"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/common/popover"
import { ToggleGroup, ToggleGroupItem } from "@/components/common/toggle-group"
import { cn } from "@/lib/utils"
import {
    LayoutKeys,
    type SidebarVariant,
    type SidebarCollapsible,
    type ContentLayout,
    type ToggleOption,
} from "@/lib/layout-constants"
import { updateLayoutPreference } from "@/actions/dashboard-layout"

type LayoutControlsProps = {
    readonly variant: SidebarVariant
    readonly collapsible: SidebarCollapsible
    readonly contentLayout: ContentLayout
}

type LayoutToggleProps<T extends string> = {
    label: string
    value: T
    onChange: (value: T) => void
    options: ToggleOption<T>[]
    disabled?: boolean
}

function LayoutToggle<T extends string>({
    label,
    value,
    onChange,
    options,
    disabled,
}: LayoutToggleProps<T>) {
    return (
        <div className="space-y-2">
            <Label className="text-[11px] font-semibold tracking-tight text-muted-foreground">{label}</Label>
            <ToggleGroup
                className="w-full justify-start gap-1"
                size="sm"
                variant="outline"
                type="single"
                value={value}
                onValueChange={(val) => val && onChange(val as T)}
                disabled={disabled}
            >
                {options.map((opt) => (
                    <ToggleGroupItem
                        key={opt.value}
                        className="flex-1 h-8 text-[11px] font-semibold tracking-tight data-[state=on]:bg-primary/10 data-[state=on]:text-primary data-[state=on]:border-primary/20"
                        value={opt.value}
                        aria-label={opt.aria}
                    >
                        {opt.label}
                    </ToggleGroupItem>
                ))}
            </ToggleGroup>
        </div>
    )
}

const sidebarVariantOptions: ToggleOption<SidebarVariant>[] = [
    { value: "inset", label: "Inset", aria: "Toggle inset sidebar variant" },
    { value: "sidebar", label: "Sidebar", aria: "Toggle classic sidebar variant" },
    { value: "floating", label: "Floating", aria: "Toggle floating sidebar variant" },
]

const sidebarCollapsibleOptions: ToggleOption<SidebarCollapsible>[] = [
    { value: "icon", label: "Icon", aria: "Toggle sidebar collapsible to icon" },
    { value: "offcanvas", label: "OffCanvas", aria: "Toggle sidebar collapsible to offcanvas" },
]

const contentLayoutOptions: ToggleOption<ContentLayout>[] = [
    { value: "centered", label: "Centered", aria: "Toggle content layout to centered" },
    { value: "full-width", label: "Full Width", aria: "Toggle content layout to full width" },
]

export function LayoutControls({ variant, collapsible, contentLayout }: LayoutControlsProps) {
    const [isPending, startTransition] = useTransition()

    const [currentVariant, setCurrentVariant] = useState<SidebarVariant>(variant)
    const [currentCollapsible, setCurrentCollapsible] = useState<SidebarCollapsible>(collapsible)
    const [currentContentLayout, setCurrentContentLayout] = useState<ContentLayout>(contentLayout)

    const handleValueChange = useCallback(
        <T extends string>(
            key: LayoutKeys,
            newValue: T,
            setValueFn: React.Dispatch<React.SetStateAction<T>>,
        ) => {
            startTransition(async () => {
                setValueFn(newValue)
                try {
                    await updateLayoutPreference(key, newValue)
                    toast.success("Preference updated", {
                        description: `${key.replace(/_/g, " ").toLowerCase()} saved.`,
                        className: "text-[12px] font-medium tracking-tight",
                    })
                } catch {
                    toast.error("Update failed", {
                        description: "Couldn't save your layout preference. Please try again.",
                        className: "text-[12px] font-medium tracking-tight",
                    })
                }
            })
        },
        [],
    )

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    className="size-9 rounded-full text-muted-foreground shadow-none hover:bg-accent/70 hover:text-foreground"
                    aria-label="Open layout settings"
                >
                    <Settings className={cn("size-3.5 transition-transform duration-500", isPending && "animate-spin text-primary")} />
                </Button>
            </PopoverTrigger>
            <PopoverContent
                align="end"
                className="w-[320px] overflow-hidden rounded-2xl border border-border/70 bg-popover p-0 shadow-none backdrop-blur-xl"
            >
                <div className="flex flex-col">
                    <div className="bg-muted/20 p-4 border-b border-border/40">
                        <h4 className="text-[13px] font-semibold tracking-tight text-foreground">Layout</h4>
                        <p className="text-[12px] text-muted-foreground mt-1">
                            Customize sidebar behavior and content width.
                        </p>
                    </div>

                    <div className="p-4 space-y-6">
                        <LayoutToggle<SidebarVariant>
                            label="Sidebar style"
                            value={currentVariant}
                            onChange={(value) =>
                                handleValueChange(LayoutKeys.SIDEBAR_VARIANT, value, setCurrentVariant)
                            }
                            options={sidebarVariantOptions}
                            disabled={isPending}
                        />

                        <LayoutToggle<SidebarCollapsible>
                            label="Sidebar collapse"
                            value={currentCollapsible}
                            onChange={(value) =>
                                handleValueChange(LayoutKeys.SIDEBAR_COLLAPSIBLE, value, setCurrentCollapsible)
                            }
                            options={sidebarCollapsibleOptions}
                            disabled={isPending}
                        />

                        <LayoutToggle<ContentLayout>
                            label="Content width"
                            value={currentContentLayout}
                            onChange={(value) =>
                                handleValueChange(LayoutKeys.CONTENT_LAYOUT, value, setCurrentContentLayout)
                            }
                            options={contentLayoutOptions}
                            disabled={isPending}
                        />
                    </div>

                    <div className="bg-primary/5 px-4 py-2 border-t border-border/40 flex items-center justify-between">
                        {isPending && <span className="text-[11px] font-medium tracking-tight text-primary animate-pulse">Saving…</span>}
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    )
}