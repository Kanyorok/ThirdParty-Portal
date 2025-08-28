"use client";

import { Skeleton } from "@/components/common/skeleton";
import { Card, CardContent, CardHeader } from "@/components/common/card";

export default function ProfileLoadSkeleton() {
    return (
        <div className="space-y-8 p-4 md:p-8 animate-pulse">
            <div className="space-y-2">
                <Skeleton className="h-9 w-1/3" />
                <Skeleton className="h-5 w-1/2" />
            </div>

            <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <aside className="lg:col-span-1">
                    <Card className="h-full bg-card border-none shadow-sm">
                        <CardContent className="flex flex-col items-center gap-4 py-6">
                            <Skeleton className="h-28 w-28 rounded-full" />
                            <div className="text-center space-y-2">
                                <Skeleton className="h-6 w-40" />
                                <Skeleton className="h-4 w-24" />
                            </div>
                            <div className="mt-4 w-full space-y-2 px-4">
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-4 w-5/6" />
                            </div>
                            <Skeleton className="mt-6 h-10 w-full" />
                        </CardContent>
                    </Card>
                </aside>

                <main className="lg:col-span-2 space-y-8">
                    <Card className="bg-card border-none shadow-sm">
                        <CardHeader className="space-y-2">
                            <Skeleton className="h-7 w-1/4" />
                            <Skeleton className="h-5 w-2/5" />
                        </CardHeader>
                        <CardContent className="grid gap-6 sm:grid-cols-2 px-6">
                            {[...Array(6)].map((_, i) => (
                                <div key={i} className="space-y-2">
                                    <Skeleton className="h-5 w-28" />
                                    <Skeleton className="h-10 w-full" />
                                </div>
                            ))}
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-none shadow-sm">
                        <CardHeader className="space-y-2">
                            <Skeleton className="h-7 w-1/4" />
                            <Skeleton className="h-5 w-2/5" />
                        </CardHeader>
                        <CardContent className="px-6 space-y-4">
                            <Skeleton className="h-10 w-40" />
                            <Skeleton className="h-10 w-1/2" />
                        </CardContent>
                    </Card>

                    <Card className="bg-card border-none shadow-sm">
                        <CardHeader className="space-y-2">
                            <Skeleton className="h-7 w-1/4" />
                            <Skeleton className="h-5 w-2/5" />
                        </CardHeader>
                        <CardContent className="px-6 space-y-4">
                            <Skeleton className="h-10 w-40" />
                        </CardContent>
                    </Card>
                </main>
            </div>
        </div>
    );
}
