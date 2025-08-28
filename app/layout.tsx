import type { Metadata } from "next"
import { Roboto } from "next/font/google"
import "@/styles/globals.css"
import { NextAuthProvider } from "@/app/providers"
import { CLIENT_APP_NAME, CLIENT_APP_NAME_STRING } from "@/config/client-config"

const roboto = Roboto({
  weight: ["300", "400", "500", "700"],
  style: ["normal", "italic"],
  variable: "--font-roboto",
  subsets: ["latin", "latin-ext"],
  display: "swap",
  preload: true,
})

const appTitleWithVersion = `${CLIENT_APP_NAME_STRING} v${CLIENT_APP_NAME.version}`

export const metadata: Metadata = {
  title: {
    default: appTitleWithVersion,
    template: `%s | ${appTitleWithVersion}`,
  },
  description: CLIENT_APP_NAME.meta.description,
  applicationName: CLIENT_APP_NAME.name,
  keywords: [
    "third parties portal",
    "self service",
    "vendor management",
    "BR Portal",
    "partners",
  ],
  authors: [{ name: "Craft Silicon" }],
  generator: "Next.js",
  openGraph: {
    title: appTitleWithVersion,
    description: CLIENT_APP_NAME.meta.description,
    siteName: CLIENT_APP_NAME.name,
  },
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body className={`${roboto.variable} smooth-scroll antialiased`}>
        <NextAuthProvider attribute="class" enableSystem disableTransitionOnChange>
          {children}
        </NextAuthProvider>
      </body>
    </html>
  )
}
