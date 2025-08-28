export enum LayoutKeys {
    SIDEBAR_VARIANT = "sidebar_variant",
    SIDEBAR_COLLAPSIBLE = "sidebar_collapsible",
    CONTENT_LAYOUT = "content_layout",
}

export type SidebarVariant = "inset" | "sidebar" | "floating"
export type SidebarCollapsible = "icon" | "offcanvas"
export type ContentLayout = "centered" | "full-width"

export type ToggleOption<T extends string> = {
    value: T
    label: string
    aria: string
}