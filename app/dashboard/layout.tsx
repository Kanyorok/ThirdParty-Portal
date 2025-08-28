import { ReactNode } from "react";
import { cookies } from "next/headers";
import { Separator } from "@/components/common/separator";
import { SidebarInset, SidebarProvider, SidebarTrigger } from "@/components/common/sidebar";
import { getSidebarVariant, getSidebarCollapsible, getContentLayout } from "@/lib/layout-preferences";
import { cn } from "@/lib/utils";
import { NextAuthProvider } from "@/app/providers";
import { AppSidebar } from "@/app/dashboard/side-nav/app-sidebar";
import { HeaderActions } from "@/app/dashboard/header-actions";
import { LayoutControls } from "@/app/dashboard/layout-controls";
import { SearchDialog } from "@/app/dashboard/search-dialog";
import { getServerSession } from "next-auth";
import { authOptions } from "../api/auth/[...nextauth]/route";

export default async function Layout({ children }: Readonly<{ children: ReactNode }>) {
    const cookieStore = await cookies();
    const defaultOpen = cookieStore.get("sidebar_state")?.value === "true";

    const sidebarVariant = await getSidebarVariant();
    const sidebarCollapsible = await getSidebarCollapsible();
    const contentLayout = await getContentLayout();

    const session = await getServerSession(authOptions);

    return (
        <NextAuthProvider session={session} attribute={"data-theme"} defaultTheme="dark" enableSystem={true}>
            <SidebarProvider defaultOpen={defaultOpen}>
                <AppSidebar variant={sidebarVariant} collapsible={sidebarCollapsible} />
                <SidebarInset
                    className={cn(
                        contentLayout === "centered" && "!mx-auto max-w-7xl",
                        "max-[113rem]:peer-data-[variant=inset]:!mr-2 min-[101rem]:peer-data-[variant=inset]:peer-data-[state=collapsed]:!mr-auto",
                    )}
                >
                    <header className="flex h-14 shrink-0 items-center gap-2 border-b px-4 lg:px-6">
                        <div className="flex w-full items-center justify-between">
                            <div className="flex items-center gap-1 lg:gap-2">
                                <SidebarTrigger className="-ml-1" />
                                <Separator orientation="vertical" className="mx-2 h-6" />
                                <SearchDialog />
                            </div>
                            <div className="flex items-center gap-2">
                                <LayoutControls
                                    contentLayout={contentLayout}
                                    variant={sidebarVariant}
                                    collapsible={sidebarCollapsible}
                                />
                                <HeaderActions />
                            </div>
                        </div>
                    </header>
                    <main className="p-4 md:p-6">{children}</main>
                </SidebarInset>
            </SidebarProvider>
        </NextAuthProvider>
    );
}
