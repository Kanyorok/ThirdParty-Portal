import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Produce standalone output for Docker multi-stage COPY (.next/standalone)
  output: "standalone",
  // Skip ESLint during production builds
  eslint: {
    ignoreDuringBuilds: true,
  },
  // Skip TypeScript type checking during production builds
  typescript: {
    ignoreBuildErrors: true,
  },
  reactStrictMode: true,
  compiler: {
    removeConsole: process.env.NODE_ENV === "production" ? { exclude: ["error"] } : false,
  },
  images: {
    remotePatterns: [
      {
        protocol: "https",
        hostname: "assets.example.com", // TODO: replace with actual
        port: "",
        pathname: "/logos/**", // TODO: To update in future with correct path
      },
      {
        protocol: "https",
        hostname: "images.unsplash.com" // TODO: Update with actual image storage location
      },
    ],
  },
  async headers() {
    return [
      {
        source: "/:path*",
        headers: [
          {
            key: "X-Content-Type-Options",
            value: "nosniff", // Prevents the browser from MIME-sniffing a response away from the declared content-type.
          },
          {
            key: "X-Frame-Options",
            value: "SAMEORIGIN", // Prevents clickjacking attacks by blocking the page from being rendered in a <frame>, <iframe>, <embed> or <object> on other sites.
          },
          {
            key: "X-XSS-Protection",
            value: "1; mode=block", // Enables the Cross-Site Scripting (XSS) filter built into most recent web browsers.
          },
          {
            key: "Referrer-Policy",
            value: "origin-when-cross-origin", // Controls how much referrer information is sent with requests.
          },
          {
            key: 'Permissions-Policy',
            value: 'camera=(), microphone=(), geolocation=(), interest-cohort=()', // Explicitly disables features that are not needed.
          },
        ]
      }
    ]
  }
};

export default nextConfig;
