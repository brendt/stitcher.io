import tailwindcss from '@tailwindcss/vite'
import { defineConfig, loadEnv } from 'vite'
import tempest from 'vite-plugin-tempest'

export default defineConfig(({ mode }) => {
	const env = loadEnv(mode, process.cwd(), '')
	let hmrHost: string | undefined

	if (env.BASE_URI) {
		try {
			hmrHost = new URL(env.BASE_URI).hostname
		} catch {
			// Ignore malformed BASE_URI in local environments.
		}
	}

	return {
		plugins: [
			tailwindcss(),
			tempest(),
		],
		server: {
			// Bind to all interfaces so mobile devices on the LAN can connect.
			host: '0.0.0.0',
			// Allow host headers from LAN/IP access during local testing.
			allowedHosts: true,
			hmr: hmrHost
				? {
					host: hmrHost,
				}
				: undefined,
		},
	}
})
