"use client"

import { useState, Suspense } from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import { signIn } from "next-auth/react"
import { motion, AnimatePresence } from "framer-motion"
import { Mail, Lock, ArrowRight, ShieldCheck, AlertCircle } from "lucide-react"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import {
    Form,
    FormControl,
    FormField,
    FormItem,
    FormMessage,
} from "@/components/common/form"

const loginSchema = z.object({
    email: z.string().min(1, "Email is required").email("Invalid email address"),
    password: z.string().min(1, "Password is required"),
})

type LoginFormValues = z.infer<typeof loginSchema>

function SignInForm() {
    const router = useRouter()
    const [error, setError] = useState<string | null>(null)

    const form = useForm<LoginFormValues>({
        resolver: zodResolver(loginSchema),
        defaultValues: { email: "", password: "" },
    })

    const { isSubmitting } = form.formState

    async function onSubmit(data: LoginFormValues) {
        setError(null)
        try {
            const result = await signIn("credentials", {
                ...data,
                redirect: false,
            })

            if (result?.error) {
                setError("Invalid email or password")
            } else {
                router.push("/dashboard")
                router.refresh()
            }
        } catch {
            setError("Portal connection failed")
        }
    }

    return (
        <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
                <AnimatePresence mode="wait">
                    {error && (
                        <motion.div
                            initial={{ opacity: 0, y: -4 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0 }}
                            className="flex items-center gap-3 bg-destructive/5 p-4 rounded-sm border border-destructive/10"
                        >
                            <AlertCircle className="h-4 w-4 text-destructive" />
                            <p className="text-[11px] font-black uppercase tracking-widest text-destructive">
                                {error}
                            </p>
                        </motion.div>
                    )}
                </AnimatePresence>

                <div className="space-y-5">
                    <FormField
                        control={form.control}
                        name="email"
                        render={({ field }) => (
                            <FormItem>
                                <FormControl>
                                    <div className="relative group">
                                        <Input
                                            placeholder="Email Address"
                                            className="h-14 bg-muted/20 border-border rounded-sm pl-5 pr-12 text-base transition-all focus-visible:ring-1 focus-visible:ring-primary/40 focus-visible:bg-background shadow-none border-none"
                                            {...field}
                                        />
                                        <Mail className="absolute right-5 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground/30 group-focus-within:text-primary transition-colors" />
                                    </div>
                                </FormControl>
                                <FormMessage className="text-[10px] font-black uppercase tracking-tight" />
                            </FormItem>
                        )}
                    />

                    <FormField
                        control={form.control}
                        name="password"
                        render={({ field }) => (
                            <FormItem>
                                <FormControl>
                                    <div className="relative group">
                                        <Input
                                            type="password"
                                            placeholder="Password"
                                            className="h-14 bg-muted/20 border-border rounded-sm pl-5 pr-12 text-base transition-all focus-visible:ring-1 focus-visible:ring-primary/40 focus-visible:bg-background shadow-none border-none"
                                            {...field}
                                        />
                                        <Lock className="absolute right-5 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground/30 group-focus-within:text-primary transition-colors" />
                                    </div>
                                </FormControl>
                                <div className="flex items-center justify-between mt-2">
                                    <FormMessage className="text-[10px] font-black uppercase tracking-tight" />
                                    <Link
                                        href="/forgot-password"
                                        className="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60 hover:text-primary transition-colors ml-auto"
                                    >
                                        Forgot Password?
                                    </Link>
                                </div>
                            </FormItem>
                        )}
                    />
                </div>

                <Button
                    type="submit"
                    disabled={isSubmitting}
                    className="w-full h-14 bg-[#4CAF50] hover:bg-[#43A047] text-white font-black uppercase tracking-[0.3em] rounded-sm transition-all active:scale-[0.99] shadow-none"
                >
                    {isSubmitting ? (
                        <motion.div
                            animate={{ rotate: 360 }}
                            transition={{ repeat: Infinity, duration: 1, ease: "linear" }}
                        >
                            <ShieldCheck className="h-5 w-5" />
                        </motion.div>
                    ) : (
                        <div className="flex items-center gap-3">
                            Sign In <ArrowRight className="h-5 w-5" strokeWidth={3} />
                        </div>
                    )}
                </Button>
            </form>
        </Form>
    )
}

export default function SignInPage() {
    return (
        <div className="min-h-screen w-full flex flex-col items-center justify-center bg-background p-6 font-sans">
            <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ duration: 0.5 }}
                className="w-full max-w-[520px]"
            >
                <div className="flex flex-col items-center mb-12">
                    <div className="mb-8">
                        <div className="flex h-24 w-24 items-center justify-center rounded-sm bg-muted/10">
                            <div className="flex h-16 w-16 items-center justify-center rounded-sm border border-border/40 bg-background">
                                <ShieldCheck className="h-10 w-10 text-[#40C4FF]" strokeWidth={1} />
                            </div>
                        </div>
                    </div>

                    <div className="text-center space-y-4">
                        <h1 className="text-4xl font-bold tracking-tighter uppercase italic text-foreground">
                            Portal Access
                        </h1>
                        <div className="flex items-center justify-center gap-4">
                            <span className="h-px w-8 bg-border/60" />
                            <p className="text-[11px] font-black uppercase tracking-[0.4em] text-muted-foreground/40">
                                Supplier & Tenant Ecosystem
                            </p>
                            <span className="h-px w-8 bg-border/60" />
                        </div>
                    </div>
                </div>

                <div className="p-8 md:p-14">
                    <Suspense fallback={<div className="h-64 animate-pulse bg-muted/5 rounded-sm" />}>
                        <SignInForm />
                    </Suspense>
                </div>

                <footer className="mt-12 flex flex-col items-center space-y-12">
                    <p className="text-[12px] font-bold text-muted-foreground uppercase tracking-widest">
                        Don&apos;t have an account?{" "}
                        <Link
                            href="/signup"
                            className="text-primary hover:text-primary underline underline-offset-8 transition-all"
                        >
                            Register Here
                        </Link>
                    </p>

                    <div className="w-full pt-10 border-t border-border/40 flex flex-col items-center gap-6">
                        <span className="text-[11px] font-black uppercase tracking-[0.8em] text-muted-foreground/10 italic">
                            Craft Silicon
                        </span>
                        <div className="flex items-center gap-6 text-[10px] font-bold text-muted-foreground/20 uppercase tracking-[0.3em]">
                            <span>Build 2.5.0</span>
                            <span className="h-1 w-1 rounded-full bg-border/40" />
                            <span>Secure Session</span>
                        </div>
                    </div>
                </footer>
            </motion.div>
        </div>
    )
}