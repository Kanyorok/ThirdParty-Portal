"use client";

import React, { useState, useEffect } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card"; // Assuming Shadcn Card components
import { DollarSign, Mail, CheckCircle, Loader2 } from "lucide-react"; // Icons for each card
import { motion, AnimatePresence } from "framer-motion";

interface RequestSummary {
    title: string;
    count: number;
    icon: React.ElementType;
    description: string;
    colorClass: string;
}

export function RequestSummaryCards() {
    const [summaryData, setSummaryData] = useState<RequestSummary[]>([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const fetchSummaryData = async () => {
            setIsLoading(true);
            await new Promise((resolve) => setTimeout(resolve, 1200)); // Simulate API call delay

            // Mock data for demonstration
            const mockData: RequestSummary[] = [
                {
                    title: "Active Requests",
                    count: Math.floor(Math.random() * 50) + 10, // 10-59 active requests
                    icon: DollarSign, // Using DollarSign as a generic request icon
                    description: "Currently open and awaiting action.",
                    colorClass: "text-blue-500 dark:text-blue-400",
                },
                {
                    title: "Direct Invites",
                    count: Math.floor(Math.random() * 20) + 5, // 5-24 direct invites
                    icon: Mail, // Icon for invites
                    description: "Requests sent directly to your organization.",
                    colorClass: "text-purple-500 dark:text-purple-400",
                },
                {
                    title: "Completed Requests",
                    count: Math.floor(Math.random() * 100) + 30, // 30-129 completed requests
                    icon: CheckCircle, // Icon for completed
                    description: "Requests that have been finalized.",
                    colorClass: "text-green-500 dark:text-green-400",
                },
            ];

            setSummaryData(mockData);
            setIsLoading(false);
        };

        fetchSummaryData();
    }, []);

    return (
        <div className="border border-border p-6 md:p-8 transition-colors duration-300">
            <h2 className="text-2xl font-bold mb-6">Overview</h2>

            <AnimatePresence mode="wait">
                {isLoading ? (
                    <motion.div
                        key="loading-cards"
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -10 }}
                        className="flex items-center justify-center h-48 text-muted-foreground"
                    >
                        <Loader2 className="h-6 w-6 animate-spin mr-2" />
                        <span>Loading...</span>
                    </motion.div>
                ) : (
                    <motion.div
                        key="summary-cards"
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -10 }}
                        className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                    >
                        {summaryData.map((item, index) => (
                            <motion.div
                                key={item.title}
                                initial={{ opacity: 0, scale: 0.9 }}
                                animate={{ opacity: 1, scale: 1 }}
                                transition={{ delay: index * 0.1 }}
                                className="h-full"
                            >
                                <Card className="h-full flex flex-col justify-between border border-border rounded-lg bg-background text-foreground transition-colors duration-300">
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-sm font-medium">
                                            {item.title}
                                        </CardTitle>
                                        <item.icon className={`h-5 w-5 ${item.colorClass}`} />
                                    </CardHeader>
                                    <CardContent>
                                        <div className="text-3xl font-bold">{item.count}</div>
                                        <p className="text-xs text-muted-foreground mt-1">
                                            {item.description}
                                        </p>
                                    </CardContent>
                                </Card>
                            </motion.div>
                        ))}
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
}
