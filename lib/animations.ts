"use client"

import type { Variants } from "framer-motion"

export const containerVariants: Variants = {
    hidden: { opacity: 0 },
    show: {
        opacity: 1,
        transition: { staggerChildren: 0.12, delayChildren: 0.1 },
    },
}

export const itemVariants: Variants = {
    hidden: { y: 30, opacity: 0 },
    show: {
        y: 0,
        opacity: 1,
        transition: { type: "spring", stiffness: 280, damping: 35, mass: 1.2 },
    },
}

export const cardHoverVariants: Variants = {
    rest: { scale: 1, rotateZ: 0 },
    hover: {
        scale: 1.02,
        transition: { type: "spring", stiffness: 400, damping: 25 },
    },
}
