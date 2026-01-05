"use client"

import { motion } from "framer-motion"

export function AnimatedBackground() {
    return (
        <div className="pointer-events-none fixed inset-0 -z-10 overflow-hidden bg-background">
            <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 0.5 }}
                transition={{ duration: 2 }}
                className="absolute -left-1/4 -top-1/4 h-[600px] w-[600px] bg-gradient-to-br from-primary/15 to-transparent blur-3xl md:h-[800px] md:w-[800px]"
            />
            <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 0.3 }}
                transition={{ duration: 2, delay: 0.5 }}
                className="absolute -bottom-1/4 -right-1/4 h-[500px] w-[500px] bg-gradient-to-tl from-primary/10 to-transparent blur-3xl md:h-[700px] md:w-[700px]"
            />
        </div>
    )
}
