import packageJson from "@/package.json";

export const CLIENT_APP_NAME = {
  name: "BR Portal",
  version: packageJson.version,
  meta: {
    title: "Third Parties Self-Service Portal",
    description: "Self-Service Portal for managing Third Parties",
  },
};

export const CLIENT_APP_NAME_STRING = "Third Parties Portal"

export const LINKS = {
  SITE_URL: "https://thirdparties.brokerrepublic.com",
} as const