/**
 * Centralized configuration for environment variables
 * Validates required variables and provides typed access
 */

interface Config {
  apiUrl: string;
  nextAuthSecret: string;
  nodeEnv: string;
  isDevelopment: boolean;
  isProduction: boolean;
}

function getConfig(): Config {
  let apiUrl = ""
  if (typeof window !== "undefined") {
    try {
      const win: any = window as any
      const runtime = win.__ENV__ || {}
      apiUrl = runtime.NEXT_PUBLIC_API_URL || runtime.API_BASE_URL || runtime.EXTERNAL_API_URL || process.env.NEXT_PUBLIC_API_URL || ""
    } catch {
      apiUrl = process.env.NEXT_PUBLIC_API_URL || ""
    }
  } else {
    apiUrl = process.env.NEXT_PUBLIC_API_URL || ""
  }
  const nextAuthSecret = process.env.NEXTAUTH_SECRET || "";
  const nodeEnv = process.env.NODE_ENV || "development";

  // Only validate in server-side runtime (not during build)
  if (typeof window === "undefined" && nodeEnv !== "test") {
    if (!apiUrl) {
      console.warn("⚠️  NEXT_PUBLIC_API_URL is not set. API calls may use relative paths or runtime overrides.")
    }

    if (!nextAuthSecret) {
      console.warn(
        "⚠️  NEXTAUTH_SECRET is not set. Authentication may fail.\n" +
        "   Please add NEXTAUTH_SECRET to your .env.local file."
      );
    }
  }

  return {
    apiUrl,
    nextAuthSecret,
    nodeEnv,
    isDevelopment: nodeEnv === "development",
    isProduction: nodeEnv === "production",
  };
}

export const config = getConfig();

export const getApiUrl = (): string => {
  if (!config.apiUrl) {
    // Prefer relative paths instead of throwing in runtime environments
    return ""
  }
  return config.apiUrl;
};
