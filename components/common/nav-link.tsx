'use client'

import React from 'react'
import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { cn } from '@/lib/utils'

interface NavLinkProps extends React.ComponentPropsWithoutRef<typeof Link> {
    activeClassName?: string
    children: React.ReactNode
}

const NavLink: React.FC<NavLinkProps> = ({ href, activeClassName, className, children, ...props }) => {
    const pathname = usePathname()
    const isActive = pathname === href

    return (
        <Link
            href={href}
            className={cn(className, isActive && activeClassName)}
            {...props}
        >
            {children}
        </Link>
    )
}

export { NavLink }
