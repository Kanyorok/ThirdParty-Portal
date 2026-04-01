// runtime environment overrides for the single-build multi-deploy pattern
// Deployers should replace or overwrite this file during deployment.
// Example content to be written on the host:
// window.__ENV__ = { NEXT_PUBLIC_API_URL: 'https://api.example.com' }

(function () {
  try {
    if (typeof window === 'undefined') return
    if (!window.__ENV__) {
      window.__ENV__ = {
        // Default empty values meaning use relative paths.
        NEXT_PUBLIC_API_URL: 'http://172.17.22.10',
        API_BASE_URL: 'http://172.17.22.10',
        EXTERNAL_API_URL: 'http://172.17.22.10'
      }
    }
  } catch {
    // ignore
  }
}())
