"use client";

import { useState, useTransition, useCallback } from "react";
import { Settings } from "lucide-react";
import { toast } from "sonner";
import { Button } from "@/components/common/button";
import { Label } from "@/components/common/label";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/common/popover";
import { ToggleGroup, ToggleGroupItem } from "@/components/common/toggle-group";
import { cn } from "@/lib/utils";
import {
    LayoutKeys,
    type SidebarVariant,
    type SidebarCollapsible,
    type ContentLayout,
    type ToggleOption,
} from "@/lib/layout-constants";
import { updateLayoutPreference } from "@/actions/dashboard-layout";

type LayoutControlsProps = {
    readonly variant: SidebarVariant;
    readonly collapsible: SidebarCollapsible;
    readonly contentLayout: ContentLayout;
};

type LayoutToggleProps<T extends string> = {
    label: string;
    value: T;
    onChange: (value: T) => void;
    options: ToggleOption<T>[];
    disabled?: boolean;
};

function LayoutToggle<T extends string>({
    label,
    value,
    onChange,
    options,
    disabled,
}: LayoutToggleProps<T>) {
    return (
        <div className="space-y-2">
            <Label className="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60">{label}</Label>
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
                        className="flex-1 text-[10px] font-bold uppercase tracking-tight h-8 data-[state=on]:bg-primary/10 data-[state=on]:text-primary data-[state=on]:border-primary/20"
                        value={opt.value}
                        aria-label={opt.aria}
                    >
                        {opt.label}
                    </ToggleGroupItem>
                ))}
            </ToggleGroup>
        </div>
    );
}

const sidebarVariantOptions: ToggleOption<SidebarVariant>[] = [
    { value: "inset", label: "Inset", aria: "Toggle inset sidebar variant" },
    { value: "sidebar", label: "Sidebar", aria: "Toggle classic sidebar variant" },
    { value: "floating", label: "Floating", aria: "Toggle floating sidebar variant" },
];

const sidebarCollapsibleOptions: ToggleOption<SidebarCollapsible>[] = [
    { value: "icon", label: "Icon", aria: "Toggle sidebar collapsible to icon" },
    { value: "offcanvas", label: "OffCanvas", aria: "Toggle sidebar collapsible to offcanvas" },
];

const contentLayoutOptions: ToggleOption<ContentLayout>[] = [
    { value: "centered", label: "Centered", aria: "Toggle content layout to centered" },
    { value: "full-width", label: "Full Width", aria: "Toggle content layout to full width" },
];

export function LayoutControls({ variant, collapsible, contentLayout }: LayoutControlsProps) {
    const [isPending, startTransition] = useTransition();

    const [currentVariant, setCurrentVariant] = useState<SidebarVariant>(variant);
    const [currentCollapsible, setCurrentCollapsible] = useState<SidebarCollapsible>(collapsible);
    const [currentContentLayout, setCurrentContentLayout] = useState<ContentLayout>(contentLayout);

    const handleValueChange = useCallback(
        <T extends string>(
            key: LayoutKeys,
            newValue: T,
            setValueFn: React.Dispatch<React.SetStateAction<T>>,
        ) => {
            startTransition(async () => {
                setValueFn(newValue);
                try {
                    await updateLayoutPreference(key, newValue);
                    toast.success("SYSTEM PREFERENCE UPDATED", {
                        description: `${key.replace(/_/g, ' ')} successfully synchronized.`,
                        className: "font-bold text-[10px] uppercase tracking-wider"
                    });
                } catch (error) {
                    toast.error("SYNCHRONIZATION ERROR", {
                        description: "Failed to persist layout state.",
                        className: "font-bold text-[10px] uppercase tracking-wider"
                    });
                }
            });
        },
        [],
    );

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    size="icon"
                    className="size-8 rounded-lg border-border/40 bg-background/50 hover:bg-accent"
                    aria-label="Open layout settings"
                >
                    <Settings className={cn("size-3.5 transition-transform duration-500", isPending && "animate-spin text-primary")} />
                </Button>
            </PopoverTrigger>
            <PopoverContent align="end" className="w-[320px] p-0 overflow-hidden border border-border/40 bg-background/95 shadow-none backdrop-blur-xl">
                <div className="flex flex-col">
                    <div className="bg-muted/30 p-4 border-b border-border/40">
                        <h4 className="text-[11px] font-black uppercase tracking-[0.2em] text-foreground">Interface Engine</h4>
                        <p className="text-[9px] font-bold uppercase tracking-widest text-muted-foreground/50 mt-1">
                            Hardware Accelerated Layout Controls
                        </p>
                    </div>

                    <div className="p-4 space-y-6">
                        <LayoutToggle<SidebarVariant>
                            label="Sidebar Architecture"
                            value={currentVariant}
                            onChange={(value) =>
                                handleValueChange(LayoutKeys.SIDEBAR_VARIANT, value, setCurrentVariant)
                            }
                            options={sidebarVariantOptions}
                            disabled={isPending}
                        />

                        <LayoutToggle<SidebarCollapsible>
                            label="State Behavior"
                            value={currentCollapsible}
                            onChange={(value) =>
                                handleValueChange(LayoutKeys.SIDEBAR_COLLAPSIBLE, value, setCurrentCollapsible)
                            }
                            options={sidebarCollapsibleOptions}
                            disabled={isPending}
                        />

                        <LayoutToggle<ContentLayout>
                            label="Viewport Mapping"
                            value={currentContentLayout}
                            onChange={(value) =>
                                handleValueChange(LayoutKeys.CONTENT_LAYOUT, value, setCurrentContentLayout)
                            }
                            options={contentLayoutOptions}
                            disabled={isPending}
                        />
                    </div>

                    <div className="bg-primary/5 px-4 py-2 border-t border-border/40 flex items-center justify-between">
                        {isPending && <span className="text-[8px] font-black uppercase tracking-widest text-primary animate-pulse">Syncing...</span>}
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    );
}
