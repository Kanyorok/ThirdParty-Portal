import packageJson from "@/package.json";

export const CLIENT_APP_NAME = {
  name: "Third Party Portal",
  version: packageJson.version,
  meta: {
    title: "Third Party Self-Service Portal",
    description: "Self-service portal for managing third parties.",
  },
};

export const CLIENT_APP_NAME_STRING = "Third Party Portal"

export const LINKS = {
  SITE_URL: "https://thirdparties.brokerrepublic.com",
} as const