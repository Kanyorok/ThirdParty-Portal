// Deprecated file: using next.config.ts as the single source of truth.
// This file is intentionally kept minimal to avoid conflicting settings
// that can break API routes (e.g., output: 'export' disables server routes).
const path = require('path');
/** @type {import('next').NextConfig} */
const nextConfig = {
	output: 'standalone',
	webpack: (config) => {
		config.resolve = config.resolve || {};
		config.resolve.alias = {
			...(config.resolve.alias || {}),
			'@': path.resolve(__dirname, '.'),
			// Compatibility shim: map deprecated '@/components/ui/*' to 'components/common/*'
			'@/components/ui': path.resolve(__dirname, 'components', 'common'),
		};
		return config;
	},
};
module.exports = nextConfig
