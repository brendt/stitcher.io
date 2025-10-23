<script setup lang="ts">
import {
	ComboboxContent,
	ComboboxEmpty,
	ComboboxGroup,
	ComboboxInput,
	ComboboxItem,
	ComboboxLabel,
	ComboboxRoot,
} from 'reka-ui'
import { ref } from 'vue'
import BaseDialog from './base-dialog.vue'
import { registerPalette } from './register-palette'
import { handleCommand, useSearch } from './use-search'

const open = ref(false)

const query = ref<string>('')
const { results } = useSearch({ query, open })

registerPalette({ value: open })
</script>

<template>
	<base-dialog
		v-model:open="open"
		content-class="w-full h-full sm:h-auto sm:max-w-xl md:max-w-2xl"
		title="Command palette"
	>
		<combobox-root
			:open="true"
			:ignore-filter="true"
			:reset-search-term-on-blur="false"
			:reset-search-term-on-select="false"
		>
			<!-- Search -->
			<combobox-input
				v-model="query"
				placeholder="Search..."
				class="bg-transparent px-6 py-4 outline-none w-full placeholder-on-dialog-muted"
				@keydown.enter.prevent
			/>
			<!-- Results -->
			<combobox-content
				class="p-2 border-gray-300 border-t h-full sm:max-h-[40rem] overflow-y-auto"
				@escape-key-down="open = false"
			>
				<!-- No result -->
				<combobox-empty class="p-4 w-full text-center grow">
					<template v-if="query">
						No result. Try another query.
					</template>
					<template v-else>
						Type something to search.
					</template>
				</combobox-empty>

				<!-- Group by category -->
				<combobox-group v-for="category in results" :key="category.title">
					<!-- Category title -->
					<combobox-label class="mt-3 mb-3 px-4 pl-4 font-semibold">
						{{ category.title }}
					</combobox-label>
					<!-- Items -->
					<combobox-item
						v-for="(item, c) in category.children"
						:key="item.hierarchy.join('_') + c"
						:as="item.type === 'uri' ? 'a' : 'button'"
						:value="item"
						:href="item.uri"
						class="flex flex-col data-[highlighted]:bg-pastel data-[highlighted]:text-primary data-[highlighted]:font-bold  px-4 py-2 pl-4 rounded-md w-full text-left transition-colors"
						@select="(e) => handleCommand(item, e)"
					>
						<div class="flex items-center gap-x-1 text-(--ui-text-dimmed)">
							<template
								v-for="(breadcrumb, i) in item.hierarchy.slice(1)"
								:key="breadcrumb + i + c"
							>
								<template v-if="breadcrumb !== item.title">
									<span class="inline-block font-medium text-sm" v-text="breadcrumb" />
									<svg
										v-if="i < item.hierarchy.length"
										xmlns="http://www.w3.org/2000/svg"
										width="32"
										height="32"
										class="last:hidden size-4"
										viewBox="0 0 24 24"
									>
										<path
											fill="none"
											stroke="currentColor"
											stroke-linecap="round"
											stroke-linejoin="round"
											stroke-width="2"
											d="m7 7l5 5l-5 5m6-10l5 5l-5 5"
										/>
									</svg>
								</template>
							</template>
						</div>
						<span>{{ item.title }}</span>
					</combobox-item>
				</combobox-group>
			</combobox-content>
		</combobox-root>
	</base-dialog>
</template>
