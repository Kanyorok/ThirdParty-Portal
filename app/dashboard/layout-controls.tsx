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
import { updateLayoutPreference } from "@/actions/server-actions";

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
        <div className="space-y-1">
            <Label className="text-xs font-medium">{label}</Label>
            <ToggleGroup
                className="w-full"
                size="sm"
                variant="outline"
                type="single"
                value={value}
                onValueChange={onChange}
                disabled={disabled}
            >
                {options.map((opt) => (
                    <ToggleGroupItem
                        key={opt.value}
                        className="text-xs"
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
                    toast.success("Layout preference updated!");
                } catch (error) {
                    toast.error("Failed to update preference. Please refresh and try again.");
                }
            });
        },
        [],
    );

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button size="icon" aria-label="Open layout settings">
                    <Settings className={cn(isPending && "animate-spin text-muted-foreground")} />
                </Button>
            </PopoverTrigger>
            <PopoverContent align="end" className="w-[280px]">
                <div className="flex flex-col gap-5">
                    <div className="space-y-1.5">
                        <h4 className="text-sm font-medium leading-none">Layout Settings</h4>
                        <p className="text-xs text-muted-foreground">
                            Customize your dashboard layout preferences.
                        </p>
                    </div>

                    <div className="flex flex-col gap-3">
                        <LayoutToggle<SidebarVariant>
                            label="Sidebar Variant"
                            value={currentVariant}
                            onChange={(value) =>
                                handleValueChange(LayoutKeys.SIDEBAR_VARIANT, value, setCurrentVariant)
                            }
                            options={sidebarVariantOptions}
                            disabled={isPending}
                        />

                        <LayoutToggle<SidebarCollapsible>
                            label="Sidebar Collapsible"
                            value={currentCollapsible}
                            onChange={(value) =>
                                handleValueChange(LayoutKeys.SIDEBAR_COLLAPSIBLE, value, setCurrentCollapsible)
                            }
                            options={sidebarCollapsibleOptions}
                            disabled={isPending}
                        />

                        <LayoutToggle<ContentLayout>
                            label="Content Layout"
                            value={currentContentLayout}
                            onChange={(value) =>
                                handleValueChange(LayoutKeys.CONTENT_LAYOUT, value, setCurrentContentLayout)
                            }
                            options={contentLayoutOptions}
                            disabled={isPending}
                        />
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    );
}