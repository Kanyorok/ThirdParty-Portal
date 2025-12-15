import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";
import { getToken } from "next-auth/jwt";

const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL || process.env.API_BASE_URL;
const API_PROXY_PREFIX = "/api/v1";

const PUBLIC_API_ROUTES = new Set([
  "/api/v1/countries",
  "/api/v1/third-party-details",
  "/api/v1/currencies",
]);

export async function proxy(req: NextRequest) {
  const { pathname } = req.nextUrl;

  if (
    pathname.startsWith("/_next") ||
    pathname.startsWith("/static") ||
    pathname.startsWith("/images") ||
    pathname.startsWith("/favicon") ||
    pathname.startsWith("/robots.txt") ||
    pathname.startsWith("/sitemap.xml") ||
    pathname.startsWith("/api/auth")
  ) {
    return NextResponse.next();
  }

  if (pathname.startsWith(API_PROXY_PREFIX)) {

    if (PUBLIC_API_ROUTES.has(pathname)) {
      return NextResponse.next();
    }

    if (!EXTERNAL_API_BASE) {
      return NextResponse.json({ error: "Configuration Error" }, { status: 500 });
    }

    const token = await getToken({ req, secret: process.env.NEXTAUTH_SECRET });
    const accessToken = token?.accessToken as string | undefined;

    if (!accessToken) {
      return NextResponse.json(
        {
          error: "Unauthorized",
          message: "Authentication token missing or invalid."
        },
        { status: 401 }
      );
    }

    const apiPath = pathname.replace(API_PROXY_PREFIX, "");
    const targetUrl = new URL(`${EXTERNAL_API_BASE}${API_PROXY_PREFIX}${apiPath}${req.nextUrl.search}`);

    const headers = new Headers(req.headers);
    headers.set("Authorization", `Bearer ${accessToken}`);

    const forwardedRequest = new Request(targetUrl.toString(), {
      headers: headers,
      method: req.method,
      body: req.body,
      // @ts-ignore
      duplex: 'half',
    });

    try {
      const response = await fetch(forwardedRequest);

      const responseHeaders = new Headers(response.headers);

      return new NextResponse(response.body, {
        status: response.status,
        statusText: response.statusText,
        headers: responseHeaders,
      });

    } catch (error) {
      return NextResponse.json(
        { error: "Proxy Error", message: "Failed to connect to the external API." },
        { status: 500 }
      );
    }
  }

  if (pathname.startsWith("/dashboard")) {
    const token = await getToken({ req, secret: process.env.NEXTAUTH_SECRET });

    if (!token) {
      const url = req.nextUrl.clone();
      url.pathname = "/signin";
      url.searchParams.set("callbackUrl", req.nextUrl.pathname + req.nextUrl.search);
      return NextResponse.redirect(url);
    }
  }

  if (pathname === "/signin") {
    const token = await getToken({ req, secret: process.env.NEXTAUTH_SECRET });
    if (token) {
      const url = req.nextUrl.clone();
      url.pathname = "/dashboard";
      return NextResponse.redirect(url);
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    "/api/v1/:path*",
    "/dashboard/:path*",
    "/signin",
    "/",
  ],
};