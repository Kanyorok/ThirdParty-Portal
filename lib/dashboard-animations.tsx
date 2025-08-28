import { Variants } from 'framer-motion'

export const containerVariants: Variants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: {
            staggerChildren: 0.08,
            delayChildren: 0.12,
        },
    },
}

export const itemVariants: Variants = {
    hidden: {
        opacity: 0,
        y: 24,
        scale: 0.95,
    },
    visible: {
        opacity: 1,
        y: 0,
        scale: 1,
        transition: {
            type: 'spring',
            stiffness: 120,
            damping: 18,
            mass: 0.8,
        }
    },
}

export const headerVariants: Variants = {
    hidden: {
        opacity: 0,
        y: -32,
        scale: 0.98,
    },
    visible: {
        opacity: 1,
        y: 0,
        scale: 1,
        transition: {
            type: 'spring',
            stiffness: 140,
            damping: 22,
            mass: 0.9,
        },
    },
}

export const cardHoverVariants: Variants = {
    rest: {
        scale: 1,
        y: 0,
        boxShadow: '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
    },
    hover: {
        scale: 1.02,
        y: -2,
        boxShadow: '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
        transition: {
            type: 'spring',
            stiffness: 400,
            damping: 25,
        },
    },
}

export const shimmerVariants: Variants = {
    initial: { x: '-100%' },
    animate: {
        x: '100%',
        transition: {
            repeat: Infinity,
            duration: 1.5,
            ease: 'linear',
        },
    },
}

export const pulseVariants: Variants = {
    initial: { opacity: 1 },
    animate: {
        opacity: [1, 0.5, 1],
        transition: {
            duration: 2,
            repeat: Infinity,
            ease: 'easeInOut',
        },
    },
}

export const fadeSlideVariants: Variants = {
    hidden: {
        opacity: 0,
        x: -20,
    },
    visible: {
        opacity: 1,
        x: 0,
        transition: {
            duration: 0.5,
            ease: [0.25, 0.46, 0.45, 0.94],
        },
    },
}