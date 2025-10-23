import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'
import tempest from 'vite-plugin-tempest'

export default defineConfig({
	plugins: [
		tailwindcss(),
		tempest(),
		vue(),
	],
})
