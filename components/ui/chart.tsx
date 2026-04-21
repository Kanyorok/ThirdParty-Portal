"use client"

import * as React from "react"
import {
    ResponsiveContainer,
    Tooltip as RechartsTooltip,
} from "recharts"
import { cn } from "@/lib/utils"

type ChartConfigEntry = {
    label: string
    color?: string
}

export type ChartConfig = Record<string, ChartConfigEntry>

const ChartContext = React.createContext<{ config: ChartConfig } | null>(null)

function useChart() {
    const context = React.useContext(ChartContext)
    if (!context) {
        throw new Error("Chart components must be used inside <ChartContainer />")
    }
    return context
}

type ChartContainerProps = React.ComponentProps<"div"> & {
    config: ChartConfig
    children: React.ReactElement
}

function ChartContainer({ config, className, children, ...props }: ChartContainerProps) {
    const containerRef = React.useRef<HTMLDivElement | null>(null)
    const [containerSize, setContainerSize] = React.useState({ width: 0, height: 0 })

    const cssVars = Object.entries(config).reduce<Record<string, string>>((acc, [key, value]) => {
        if (value.color) {
            acc[`--color-${key}`] = value.color
        }
        return acc
    }, {})

    React.useEffect(() => {
        const element = containerRef.current
        if (!element) return

        const updateSize = () => {
            const nextWidth = element.clientWidth
            const nextHeight = element.clientHeight

            setContainerSize((current) => {
                if (current.width === nextWidth && current.height === nextHeight) {
                    return current
                }
                return { width: nextWidth, height: nextHeight }
            })
        }

        updateSize()

        if (typeof ResizeObserver === "undefined") {
            return undefined
        }

        const observer = new ResizeObserver(() => {
            updateSize()
        })

        observer.observe(element)
        return () => observer.disconnect()
    }, [])

    const isReady = containerSize.width > 0 && containerSize.height > 0

    return (
        <ChartContext.Provider value={{ config }}>
            <div
                ref={containerRef}
                data-slot="chart-container"
                className={cn("h-[260px] w-full", className)}
                style={cssVars as React.CSSProperties}
                {...props}
            >
                {isReady ? (
                    <ResponsiveContainer width="100%" height="100%" minWidth={0}>
                        {children}
                    </ResponsiveContainer>
                ) : (
                    <div className="h-full w-full" aria-hidden />
                )}
            </div>
        </ChartContext.Provider>
    )
}

const ChartTooltip = RechartsTooltip

type ChartTooltipPayloadItem = {
    color?: string
    dataKey?: string | number
    name?: string | number
    payload?: Record<string, unknown>
    value?: number | string | null
}

type ChartTooltipContentProps = React.ComponentProps<"div"> &
{
    active?: boolean
    payload?: ChartTooltipPayloadItem[]
    label?: string | number
    hideLabel?: boolean
    formatter?: (
        value: number | string,
        name: string,
        item: ChartTooltipPayloadItem,
        index: number
    ) => React.ReactNode
}

function ChartTooltipContent({
    active,
    payload,
    label,
    hideLabel = false,
    formatter,
    className,
}: ChartTooltipContentProps) {
    const { config } = useChart()

    if (!active || !payload?.length) return null

    return (
        <div
            data-slot="chart-tooltip"
            className={cn("rounded-lg border border-border/70 bg-popover px-3 py-2 text-xs shadow-sm", className)}
        >
            {!hideLabel && label != null ? (
                <p className="mb-1 font-medium text-foreground">{String(label)}</p>
            ) : null}
            <div className="space-y-1">
                {payload.map((entry, index) => {
                    const key = String(entry.dataKey ?? "")
                    const conf = config[key]
                    const markerColor = entry.color ?? conf?.color ?? "hsl(var(--muted-foreground))"
                    const value = typeof entry.value === "number" ? entry.value.toLocaleString() : String(entry.value ?? "-")

                    if (formatter) {
                        return (
                            <div key={`${key}-${entry.name}`} className="flex flex-wrap items-center gap-2">
                                {formatter(
                                    typeof entry.value === "number" ? entry.value : String(entry.value ?? "-"),
                                    key,
                                    entry,
                                    index
                                )}
                            </div>
                        )
                    }

                    return (
                        <div key={`${key}-${entry.name}`} className="flex items-center justify-between gap-3">
                            <div className="flex items-center gap-2">
                                <span className="h-2 w-2 rounded-full" style={{ backgroundColor: markerColor }} />
                                <span className="text-muted-foreground">{conf?.label ?? key}</span>
                            </div>
                            <span className="font-semibold text-foreground">{value}</span>
                        </div>
                    )
                })}
            </div>
        </div>
    )
}

export { ChartContainer, ChartTooltip, ChartTooltipContent }
