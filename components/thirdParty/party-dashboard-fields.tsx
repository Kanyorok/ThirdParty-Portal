import React from "react"
import { motion } from "framer-motion"

export interface FieldRowProps {
    label: string
    value: string | number | null
    icon: React.ElementType
}

export const FieldRow: React.FC<FieldRowProps> = ({ label, value, icon: Icon }) => (
    <motion.div
        initial={{ opacity: 0, y: 8 }}
        animate={{ opacity: 1, y: 0 }}
        exit={{ opacity: 0, y: 8 }}
        transition={{ duration: 0.3 }}
        className="flex items-center gap-3 py-2 border-b last:border-b-0"
    >
        <Icon className="h-5 w-5 text-primary/70" />
        <div className="flex flex-col">
            <span className="text-sm text-muted-foreground">{label}</span>
            <span className="font-medium text-foreground">{value ?? "N/A"}</span>
        </div>
    </motion.div>
)
