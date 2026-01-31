import { type MetadataRoute } from "next"
import { LINKS } from "@/config/client-config"

export default function robots(): MetadataRoute.Robots {
    return {
        rules: [
            {
                userAgent: "*",
                allow: ["/"],
                disallow: [
                    "/auth",
                    "/dashboard",
                    "/profile",
                    "/settings"
                ]
            }
        ],
        sitemap: `${LINKS.SITE_URL}/sitemap.xml`
    }
}
