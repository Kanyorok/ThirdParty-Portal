import { MetadataRoute } from "next"
import { LINKS } from "@/config/client-config"

export default function sitemap(): MetadataRoute.Sitemap {
    const baseUrl = LINKS.SITE_URL

    return [
        {
            url: `${baseUrl}/`,
            lastModified: new Date(),
            changeFrequency: "weekly",
            priority: 1
        },
        {
            url: `${baseUrl}/auth/login`,
            lastModified: new Date(),
            changeFrequency: "monthly",
            priority: 0.9
        },
        {
            url: `${baseUrl}/auth/register`,
            lastModified: new Date(),
            changeFrequency: "monthly",
            priority: 0.9
        },
        {
            url: `${baseUrl}/dashboard`,
            lastModified: new Date(),
            changeFrequency: "daily",
            priority: 0.8
        },
        {
            url: `${baseUrl}/profile`,
            lastModified: new Date(),
            changeFrequency: "weekly",
            priority: 0.7
        },
        {
            url: `${baseUrl}/third-parties`,
            lastModified: new Date(),
            changeFrequency: "daily",
            priority: 0.8
        },
        {
            url: `${baseUrl}/settings`,
            lastModified: new Date(),
            changeFrequency: "monthly",
            priority: 0.5
        }
    ]
}
