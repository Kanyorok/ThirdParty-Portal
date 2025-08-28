import { Metadata } from "next"
import { CLIENT_APP_NAME, CLIENT_APP_NAME_STRING } from "@/config/client-config"

const appTitleWithVersion = `${CLIENT_APP_NAME_STRING} v${CLIENT_APP_NAME.version}`

export function createPageMetadata(
    pageTitle?: string,
    pageDescription?: string
): Metadata {
    return {
        title: pageTitle ? `${pageTitle} | ${appTitleWithVersion}` : appTitleWithVersion,
        description: pageDescription ?? CLIENT_APP_NAME.meta.description,
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
            title: pageTitle ? `${pageTitle} | ${appTitleWithVersion}` : appTitleWithVersion,
            description: pageDescription ?? CLIENT_APP_NAME.meta.description,
            siteName: CLIENT_APP_NAME.name,
        },
    }
}
