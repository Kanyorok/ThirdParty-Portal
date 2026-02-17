import { ReactNode, Suspense } from "react"
import { cookies } from "next/headers"
import { redirect } from "next/navigation"
import { SidebarInset, SidebarProvider, SidebarTrigger } from "@/components/common/sidebar"
import { getSidebarVariant, getSidebarCollapsible, getContentLayout } from "@/lib/layout-preferences"
import { cn } from "@/lib/utils"
import { AppSidebar } from "@/app/dashboard/side-nav/app-sidebar"
import { HeaderActions } from "@/app/dashboard/header-actions"
import { LayoutControls } from "@/app/dashboard/layout-controls"
import { SearchDialog } from "@/app/dashboard/search-dialog"
import { SystemFooter } from "@/components/dashboard/system-footer"
import Loading from "@/components/common/custom-loader"

export const dynamic = "force-dynamic"

export default function Layout({ children }: { children: ReactNode }) {
    return (
        <Suspense fallback={<Loading />}>
            <AsyncDashboardLayout>{children}</AsyncDashboardLayout>
        </Suspense>
    )
}

async function AsyncDashboardLayout({ children }: { children: ReactNode }) {
    const { getServerSession } = await import("next-auth")
    const { authOptions } = await import("@/lib/auth-options")
    const session = await getServerSession(authOptions)

    if (!session || !session.user || !session.accessToken) {
        redirect("/signin?error=SessionExpired")
    }

    const cookieStore = await cookies()
    const sidebarState = cookieStore.get("sidebar_state")?.value
    const defaultOpen = sidebarState ? sidebarState === "true" : true

    const sidebarVariant = await getSidebarVariant()
    const sidebarCollapsible = await getSidebarCollapsible()
    const contentLayout = await getContentLayout()

    return (
        <SidebarProvider defaultOpen={defaultOpen}>
            <AppSidebar variant={sidebarVariant} collapsible={sidebarCollapsible} />
            <SidebarInset
                className={cn(
                    "flex flex-col transition-all duration-300 ease-in-out bg-background/50",
                    contentLayout === "centered" && "mx-auto w-full max-w-7xl border-x border-border/40 min-h-screen",
                    "peer-data-[variant=inset]:m-2 peer-data-[variant=inset]:rounded-xl peer-data-[variant=inset]:border"
                )}
            >
                <header className="sticky top-0 z-30 flex h-16 shrink-0 items-center border-b border-border/40 bg-background/70 px-4 backdrop-blur-xl lg:px-6">
                    <div className="flex w-full items-center justify-between">
                        <div className="flex items-center gap-2">
                            <SidebarTrigger className="-ml-1 size-9 rounded-xl hover:bg-accent transition-transform active:scale-95" />
                            <SearchDialog />
                        </div>
                        <div className="flex items-center gap-2">
                            <HeaderActions
                                layoutControls={(
                                    <LayoutControls
                                        contentLayout={contentLayout}
                                        variant={sidebarVariant}
                                        collapsible={sidebarCollapsible}
                                    />
                                )}
                            />
                        </div>
                    </div>
                </header>
                <main className={cn(
                    "flex-1 p-4 md:p-6 lg:p-8 overflow-x-hidden",
                    contentLayout === "centered" && "bg-card/30"
                )}>
                    {children}
                </main>
                <footer className="mt-auto border-t border-border/40 bg-muted/5 px-4 py-3">
                    <SystemFooter />
                </footer>
            </SidebarInset>
        </SidebarProvider>
    )
}
