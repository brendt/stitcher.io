<script setup lang="ts">
import { DialogContent, DialogDescription, DialogOverlay, DialogRoot, DialogTitle } from 'reka-ui'

defineProps<{
	contentClass?: string
	title: string
}>()

const open = defineModel<boolean>('open', { required: true })
</script>

<template>
	<dialog-root v-model:open="open">
		<dialog-overlay
			class="z-50 fixed inset-0 motion-reduce:bg-[black]/90 motion-safe:backdrop-blur-sm motion-safe:data-[state=closed]:animate-dialog-close motion-safe:data-[state=open]:animate-dialog-open"
		/>
		<dialog-content
			:class="contentClass"
			class="top-[50%] left-[50%] z-50 fixed bg-(--ui-bg) shadow-dialog-shadow shadow-xl p-0 border border-(--ui-border) sm:rounded-lg text-(--ui-text) translate-x-[-50%] translate-y-[-50%] data-[state=closed]:animate-dialog-close data-[state=open]:animate-dialog-open"
		>
			<dialog-title class="sr-only">
				{{ title }}
			</dialog-title>
			<dialog-description class="sr-only">
				{{ title }}
			</dialog-description>
			<slot />
		</dialog-content>
	</dialog-root>
</template>
