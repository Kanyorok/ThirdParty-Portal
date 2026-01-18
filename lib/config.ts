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
  const apiUrl = process.env.NEXT_PUBLIC_API_URL || "";
  const nextAuthSecret = process.env.NEXTAUTH_SECRET || "";
  const nodeEnv = process.env.NODE_ENV || "development";

  // Only validate in server-side runtime (not during build)
  if (typeof window === "undefined" && nodeEnv !== "test") {
    if (!apiUrl) {
      console.warn(
        "⚠️  NEXT_PUBLIC_API_URL is not set. API calls may fail.\n" +
        "   Please add NEXT_PUBLIC_API_URL to your .env.local file."
      );
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
    throw new Error(
      "API URL is not configured. Please set NEXT_PUBLIC_API_URL in your .env.local file."
    );
  }
  return config.apiUrl;
};
