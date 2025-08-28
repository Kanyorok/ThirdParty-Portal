"use client";

import { useState, useTransition, useCallback } from "react";
import { signOut, useSession } from "next-auth/react";
import { UserNavUI } from "@/app/dashboard/side-nav/user-menu"

export const UserNav = () => {
    const { data: session, status } = useSession();
    const [isPending, startTransition] = useTransition();
    const [isOpen, setIsOpen] = useState(false);

    const handleLogout = useCallback(() => {
        startTransition(() => {
            signOut();
        });
    }, []);

    const isLoading = status === 'loading';

    return (
        <UserNavUI
            user={session?.user}
            isLoading={isLoading}
            isPending={isPending}
            isOpen={isOpen}
            onLogout={handleLogout}
            onOpenChange={setIsOpen}
        />
    );
};