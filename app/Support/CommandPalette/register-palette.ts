import { useActiveElement, useMagicKeys, whenever } from '@vueuse/core'
import { logicAnd } from '@vueuse/math'
import { computed, type Ref, watchEffect } from 'vue'

interface Options {
	value: Ref<boolean>
}

/**
 * Registers `/` and `Cmd+K` hotkeys, as well as a `toggleCommandPalette` function.
 */
export function registerPalette(options: Options) {
	const { Meta_K, Slash } = useMagicKeys({
		target: document.body,
		passive: false,
		onEventFired(e) {
			if (['INPUT', 'TEXTAREA'].includes((e.target as Element).tagName ?? '')) {
				return
			}

			if (e.key === '/' && e.type === 'keydown') {
				e.preventDefault()
			}

			if (e.key === 'k' && e.type === 'keydown' && e.metaKey) {
				e.preventDefault()
			}
		},
	})

	function toggleCommandPalette() {
		options.value.value = !options.value.value
	}

	// @ts-expect-error window is not typed
	window.toggleCommandPalette = toggleCommandPalette

	window.document.querySelectorAll('[toggle-palette]').forEach((element) => {
		element.addEventListener('click', toggleCommandPalette)
	})

	const activeElement = useActiveElement()
	const notUsingInput = computed(() => !['INPUT', 'TEXTAREA'].includes(activeElement.value?.tagName ?? ''))

	watchEffect(() => {
		console.log({ Meta_K, Slash, notUsingInput })
	})

	whenever(logicAnd(Meta_K, notUsingInput), () => options.value.value = !options.value.value)
	whenever(logicAnd(Slash, notUsingInput), () => options.value.value = true)
}
