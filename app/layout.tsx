import type { Metadata, Viewport } from "next"
import { Geist } from "next/font/google"
import "@/styles/globals.css"
import { NextAuthProvider } from "@/components/providers/providers"
import { CLIENT_APP_NAME, CLIENT_APP_NAME_STRING } from "@/config/client-config"
import { ProfileSyncWatcher } from "@/components/profiles/profile-watcher"
import { OnboardingWatcher } from "@/components/common/onboarding-tooltip"
import { ProfileTransitionOverlay } from "@/components/common/profile-switch-overlay"

const geist = Geist({
  weight: ['100', '400', '900'],
  style: ['normal'],
  variable: "--font-geist",
  subsets: ["latin", "latin-ext"],
  display: "swap",
  preload: true,
  fallback: ['system-ui', 'arial'],
  adjustFontFallback: false,
})

const appVersion = CLIENT_APP_NAME?.version ?? "0.1.0"
const appTitleWithVersion = `${CLIENT_APP_NAME_STRING} v${appVersion}`

export const viewport: Viewport = {
  themeColor: "#ffffff",
}

export const metadata: Metadata = {
  metadataBase: new URL("https://portal.com"),
  title: {
    default: appTitleWithVersion,
    template: `%s | ${appTitleWithVersion}`,
  },
  description: CLIENT_APP_NAME.meta.description,
  applicationName: CLIENT_APP_NAME.name,
  keywords: [
    "third parties portal",
    "self service",
    "supplier management",
    "BR Portal",
    "partners",
    "third party",
    "tenant",
  ],
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
    <html lang="en" suppressHydrationWarning>
      <body
        suppressHydrationWarning
        className={`${geist.variable} smooth-scroll antialiased min-h-screen`}
      >
        <NextAuthProvider
          attribute="class"
          enableSystem
          disableTransitionOnChange
        >
          <ProfileSyncWatcher />
          <OnboardingWatcher />
          <ProfileTransitionOverlay />
          {children}
        </NextAuthProvider>
      </body>
    </html>
  )
}
