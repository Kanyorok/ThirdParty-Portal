
import fs from "node:fs"
import path from "node:path"
import crypto from "node:crypto"

const runtimeProcess = globalThis.process
const runtimeConsole = globalThis.console
const RuntimeUrl = globalThis.URL

const rootDir = runtimeProcess.cwd()
const envPath = path.join(rootDir, ".env.local")

function parseEnvFile(filePath) {
    const raw = fs.readFileSync(filePath, "utf8")
    const entries = {}

    for (const line of raw.split(/\r?\n/)) {
        const trimmed = line.trim()
        if (!trimmed || trimmed.startsWith("#")) continue

        const separatorIndex = trimmed.indexOf("=")
        if (separatorIndex === -1) continue

        const key = trimmed.slice(0, separatorIndex).trim()
        let value = trimmed.slice(separatorIndex + 1).trim()

        if ((value.startsWith('"') && value.endsWith('"')) || (value.startsWith("'") && value.endsWith("'"))) {
            value = value.slice(1, -1)
        }

        entries[key] = value
    }

    return entries
}

function isValidUrl(value) {
    try {
        const url = new RuntimeUrl(value)
        return url.protocol === "http:" || url.protocol === "https:"
    } catch {
        return false
    }
}

function printBlock(lines) {
    for (const line of lines) {
        runtimeConsole.error(line)
    }
}

if (!fs.existsSync(envPath)) {
    printBlock([
        "[dev:check] Missing .env.local",
        "Create it from .env.local.example before starting the dev server.",
        "Example:",
        "  copy .env.local.example .env.local",
    ])
    runtimeProcess.exit(1)
}

const env = parseEnvFile(envPath)
const errors = []
const warnings = []

const requiredKeys = ["NEXTAUTH_URL", "NEXTAUTH_SECRET", "NEXT_PUBLIC_API_URL"]

for (const key of requiredKeys) {
    if (!env[key]) {
        errors.push(`Missing ${key} in .env.local`)
    }
}

for (const key of ["NEXTAUTH_URL", "NEXT_PUBLIC_API_URL", "NEXT_PUBLIC_EXTERNAL_API_URL", "ERP_BASE_URL"]) {
    if (env[key] && !isValidUrl(env[key])) {
        errors.push(`${key} must be a valid http(s) URL`)
    }
}

if (env.NEXTAUTH_SECRET) {
    if (env.NEXTAUTH_SECRET.includes("replace-with") || env.NEXTAUTH_SECRET.length < 32) {
        errors.push("NEXTAUTH_SECRET must be replaced with a real secret of at least 32 characters")
    }
}

if (env.NEXTAUTH_URL && env.NEXTAUTH_URL.includes("127.0.0.1")) {
    warnings.push("NEXTAUTH_URL uses 127.0.0.1. Prefer http://localhost:3000 if that is your browser host to avoid auth/session host drift.")
}

if (env.NEXT_PUBLIC_API_URL && env.NEXT_PUBLIC_EXTERNAL_API_URL && env.NEXT_PUBLIC_API_URL !== env.NEXT_PUBLIC_EXTERNAL_API_URL) {
    warnings.push("NEXT_PUBLIC_API_URL and NEXT_PUBLIC_EXTERNAL_API_URL differ. Confirm that split is intentional for local development.")
}

if (errors.length > 0) {
    printBlock(["[dev:check] Local environment validation failed", ...errors.map((error) => `- ${error}`)])
    runtimeConsole.error("Suggested NEXTAUTH_SECRET example:")
    runtimeConsole.error(`  ${crypto.randomBytes(32).toString("hex")}`)
    runtimeProcess.exit(1)
}

if (warnings.length > 0) {
    printBlock(["[dev:check] Warnings", ...warnings.map((warning) => `- ${warning}`)])
}

runtimeConsole.log("[dev:check] Environment looks good.")