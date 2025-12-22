export function getApiUrl(): string {
  // prefer runtime env set by Docker build or Next.js runtime
  return (process.env.API_BASE_URL as string) || (process.env.NEXT_PUBLIC_EXTERNAL_API_URL as string) || '';
}

export function getSanctumDomains(): string[] {
  const raw = process.env.SANCTUM_STATEFUL_DOMAINS || process.env.NEXT_PUBLIC_SANCTUM_STATEFUL_DOMAINS || '';
  return raw.split(',').map(s => s.trim()).filter(Boolean);
}
