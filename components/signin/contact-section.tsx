import { Shield } from "lucide-react"

export function ContactSection() {
  return (
    <div className="border-t border-border/70 pt-6 text-center">
      <div className="mx-auto max-w-xl space-y-3">
        <h3 className="text-base font-semibold tracking-tight text-foreground">Contact us</h3>
        <div className="flex justify-center gap-4 text-sm text-primary">
          <a href="tel:+254202770222" className="hover:text-[var(--primary-hover)] hover:underline">
            +254 02 2770 222
          </a>
          <span>|</span>
          <a href="tel:+254714011009" className="hover:text-[var(--primary-hover)] hover:underline">
            +254 714 011 009
          </a>
        </div>

        <div className="flex items-center justify-center gap-1 pt-2 text-xs text-muted-foreground">
          <Shield className="w-3 h-3" />
          <span>Your privacy is protected.</span>
          <a href="/privacy-policy" className="font-medium text-primary hover:text-[var(--primary-hover)] hover:underline">
            Privacy Policy
          </a>
        </div>
      </div>
    </div>
  )
}
