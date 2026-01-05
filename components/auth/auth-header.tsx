"use client"

import Link from "next/link"
import { motion } from "framer-motion"

export function AuthHeader() {
    return (
        <Link href="/" className="inline-flex items-center gap-2">
            <motion.div
                initial={{ rotate: -10, scale: 0.9 }}
                animate={{ rotate: 0, scale: 1 }}
                transition={{ type: "spring", stiffness: 200, damping: 15 }}
                className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary"
            >
                <span className="text-lg font-bold text-primary-foreground">C</span>
            </motion.div>
            <span className="text-xl font-bold tracking-tight text-foreground">Craft</span>
        </Link>
    )
}
