import { Shield } from "lucide-react"

export function ContactSection() {
  return (
    <div className="text-center pt-6 border-t border-gray-100">
      <div className="max-w-xl mx-auto space-y-3">
        <h3 className="text-lg font-bold text-gray-900">Contact us</h3>
        <div className="flex justify-center gap-4 text-sm text-blue-600">
          <a href="tel:+254202770222" className="hover:underline">
            +254 02 2770 222
          </a>
          <span>|</span>
          <a href="tel:+254714011009" className="hover:underline">
            +254 714 011 009
          </a>
        </div>

        <div className="flex items-center justify-center gap-1 text-xs text-gray-500 pt-2">
          <Shield className="w-3 h-3" />
          <span>Your privacy is protected.</span>
          <a href="/privacy-policy" className="text-blue-600 hover:underline font-medium">
            Privacy Policy
          </a>
        </div>
      </div>
    </div>
  )
}
