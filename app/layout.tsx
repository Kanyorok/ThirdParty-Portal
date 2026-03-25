import type { Metadata, Viewport } from "next"
import { Geist } from "next/font/google"
import "@/styles/globals.css"
import { NextAuthProvider } from "@/components/providers/providers"
import { CLIENT_APP_NAME, CLIENT_APP_NAME_STRING, LINKS } from "@/config/client-config"
import { ProfileSyncWatcher } from "@/components/profiles/profile-watcher"
import { OnboardingWatcher } from "@/components/common/onboarding-tooltip"
import { ProfileTransitionOverlay } from "@/components/common/profile-switch-overlay"
import { ThemeProvider } from "@/components/common/theme-provider"
import { Toaster } from "@/components/common/sonner"

const geist = Geist({
  weight: ["400", "700"],
  style: ["normal"],
  variable: "--font-geist",
  subsets: ["latin"],
  display: "swap",
  fallback: ["system-ui", "arial"],
})

const appTitleWithVersion = `${CLIENT_APP_NAME_STRING}`

export const viewport: Viewport = {
  themeColor: [
    { media: "(prefers-color-scheme: light)", color: "#ffffff" },
    { media: "(prefers-color-scheme: dark)", color: "#0a0a0a" },
  ],
}

export const metadata: Metadata = {
  metadataBase: new URL(LINKS.SITE_URL),
  title: {
    default: appTitleWithVersion,
    template: `%s | ${appTitleWithVersion}`,
  },
  description: CLIENT_APP_NAME.meta.description,
  applicationName: CLIENT_APP_NAME.name,
  authors: [{ name: "Craft Silicon" }],
  generator: "@Craft",
  openGraph: {
    title: appTitleWithVersion,
    description: CLIENT_APP_NAME.meta.description,
    siteName: CLIENT_APP_NAME.name,
    type: "website",
    locale: "en_US",
    images: [
      {
        url: "/og-image.png",
        width: 1200,
        height: 630,
        alt: CLIENT_APP_NAME.name,
      },
    ],
  },
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en" suppressHydrationWarning className="overflow-x-hidden">
      <body
        suppressHydrationWarning
        className={`${geist.variable} antialiased min-h-screen overflow-x-hidden`}
      >
        <ThemeProvider
          attribute="class"
          defaultTheme="system"
          enableSystem
          disableTransitionOnChange
        >
          <NextAuthProvider>
            <ProfileSyncWatcher />
            <OnboardingWatcher />
            <ProfileTransitionOverlay />
            {children}
            <Toaster position="top-right" richColors closeButton />
          </NextAuthProvider>
        </ThemeProvider>
      </body>
    </html>
  )
}
