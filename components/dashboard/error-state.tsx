'use client'

import React from 'react'
import { motion } from 'framer-motion'
import { AlertCircle, RefreshCw, Home } from 'lucide-react'
import { Alert, AlertDescription } from '@/components/common/alert'
import { Button } from '@/components/common/button'
import { Card, CardContent } from '@/components/common/card'

interface ErrorStateProps {
    message: string
    showActions?: boolean
}

export function ErrorState({ message, showActions = true }: ErrorStateProps) {
    const handleRefresh = () => {
        window.location.reload()
    }

    const handleGoHome = () => {
        window.location.href = '/'
    }

    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-background via-background to-muted/20 p-4">
            <motion.div
                initial={{ opacity: 0, scale: 0.95, y: 20 }}
                animate={{ opacity: 1, scale: 1, y: 0 }}
                transition={{
                    type: 'spring',
                    stiffness: 200,
                    damping: 25,
                    duration: 0.6
                }}
                className="max-w-md w-full"
            >
                <Card className="border border-destructive/20 bg-gradient-to-br from-destructive/5 to-transparent backdrop-blur-sm">
                    <CardContent className="p-8 text-center space-y-6">
                        <motion.div
                            initial={{ scale: 0 }}
                            animate={{ scale: 1 }}
                            transition={{ delay: 0.2, type: 'spring', stiffness: 500 }}
                            className="mx-auto w-16 h-16 rounded-full bg-destructive/10 flex items-center justify-center"
                        >
                            <AlertCircle className="h-8 w-8 text-destructive" />
                        </motion.div>

                        <div className="space-y-3">
                            <motion.h2
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: 0.3 }}
                                className="text-lg font-semibold text-foreground"
                            >
                                Something went wrong
                            </motion.h2>

                            <motion.div
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: 0.4 }}
                            >
                                <Alert variant="destructive" className="bg-transparent border-none text-center">
                                    <AlertDescription className="text-muted-foreground">
                                        {message}
                                    </AlertDescription>
                                </Alert>
                            </motion.div>
                        </div>

                        {showActions && (
                            <motion.div
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: 0.5 }}
                                className="flex flex-col sm:flex-row gap-3 pt-4"
                            >
                                <Button
                                    onClick={handleRefresh}
                                    variant="outline"
                                    className="flex items-center gap-2 hover:bg-primary/5"
                                >
                                    <RefreshCw className="h-4 w-4" />
                                    Try again
                                </Button>
                                <Button
                                    onClick={handleGoHome}
                                    className="flex items-center gap-2"
                                >
                                    <Home className="h-4 w-4" />
                                    Go home
                                </Button>
                            </motion.div>
                        )}
                    </CardContent>
                </Card>
            </motion.div>
        </div>
    )
}