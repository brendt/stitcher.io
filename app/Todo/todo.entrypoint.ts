import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands'
import { RangeSet, RangeSetBuilder, RangeValue, StateEffect, StateField, Transaction } from '@codemirror/state'
import { Decoration, EditorView, keymap, placeholder, ViewPlugin, WidgetType } from '@codemirror/view'
import type { DecorationSet, ViewUpdate } from '@codemirror/view'

type ItemType = 'project' | 'task'

type Item = {
    type: ItemType
    id: number
}

const projectLine = Decoration.line({ class: 'cm-project' })

function decorateProjects(view: EditorView): DecorationSet {
    const builder = new RangeSetBuilder<Decoration>()

    for (const { from, to } of view.visibleRanges) {
        for (let position = from; position <= to;) {
            const line = view.state.doc.lineAt(position)

            if (line.text.startsWith('# ')) {
                builder.add(line.from, line.from, projectLine)
            }

            position = line.to + 1
        }
    }

    return builder.finish()
}

const projects = ViewPlugin.fromClass(
    class {
        decorations: DecorationSet

        constructor(view: EditorView) {
            this.decorations = decorateProjects(view)
        }

        update(update: ViewUpdate) {
            if (update.docChanged || update.viewportChanged) {
                this.decorations = decorateProjects(update.view)
            }
        }
    },
    { decorations: (plugin) => plugin.decorations },
)

/**
 * Remembers which line belongs to which project or task, and follows that line while the document is being edited.
 * The original text is kept as well, so that a line which was changed or removed no longer counts as that item.
 */
class ItemMarker extends RangeValue {
    constructor(
        readonly item: Item,
        readonly text: string,
    ) {
        super()
    }

    eq(other: ItemMarker): boolean {
        return other.item.type === this.item.type && other.item.id === this.item.id && other.text === this.text
    }
}

const removeItem = StateEffect.define<Item>()

function createItemMarkers(items: Record<string, Item>) {
    return StateField.define<RangeSet<ItemMarker>>({
        create(state) {
            const builder = new RangeSetBuilder<ItemMarker>()

            for (const [lineNumber, item] of Object.entries(items).sort(([a], [b]) => Number(a) - Number(b))) {
                const line = state.doc.line(Number(lineNumber))

                builder.add(line.from, line.from, new ItemMarker(item, line.text))
            }

            return builder.finish()
        },

        update(markers, transaction) {
            markers = markers.map(transaction.changes)

            for (const effect of transaction.effects) {
                if (effect.is(removeItem)) {
                    markers = markers.update({
                        filter: (_from, _to, marker) => marker.item.type !== effect.value.type || marker.item.id !== effect.value.id,
                    })
                }
            }

            return markers
        },
    })
}

class CompleteButton extends WidgetType {
    constructor(
        readonly item: Item,
        readonly complete: (view: EditorView, item: Item) => void,
    ) {
        super()
    }

    eq(other: CompleteButton): boolean {
        return other.item.type === this.item.type && other.item.id === this.item.id
    }

    toDOM(view: EditorView): HTMLElement {
        const button = document.createElement('button')

        button.type = 'button'
        button.className = 'cm-complete'
        button.title = `Mark ${this.item.type} as completed`
        button.setAttribute('aria-label', button.title)
        button.textContent = '✓'
        button.addEventListener('mousedown', (event) => event.preventDefault())
        button.addEventListener('click', () => this.complete(view, this.item))

        return button
    }

    ignoreEvent(): boolean {
        return true
    }
}

function completeButtons(markers: StateField<RangeSet<ItemMarker>>, complete: (view: EditorView, item: Item) => void) {
    return EditorView.decorations.compute(['doc', markers], (state) => {
        const builder = new RangeSetBuilder<Decoration>()
        let previousLine = -1

        for (let cursor = state.field(markers).iter(); cursor.value; cursor.next()) {
            const line = state.doc.lineAt(cursor.from)

            if (line.from !== cursor.from || line.text !== cursor.value.text || line.number === previousLine) {
                continue
            }

            previousLine = line.number

            builder.add(line.from, line.from, Decoration.widget({
                widget: new CompleteButton(cursor.value.item, complete),
                side: -1,
            }))
        }

        return builder.finish()
    })
}

/**
 * Finds the part of the document that should disappear once an item is completed:
 * a task is a single line, while a project runs until the next project.
 */
function rangeOf(view: EditorView, line: { from: number, to: number, number: number }, type: ItemType) {
    const doc = view.state.doc

    if (type === 'task') {
        return line.number < doc.lines
            ? { from: line.from, to: line.to + 1 }
            : { from: Math.max(0, line.from - 1), to: line.to }
    }

    for (let number = line.number + 1; number <= doc.lines; number++) {
        const next = doc.line(number)

        if (next.text.startsWith('# ')) {
            return { from: line.from, to: next.from }
        }
    }

    let from = line.from

    for (let number = line.number - 1; number >= 1; number--) {
        const previous = doc.line(number)

        if (previous.text.trim() !== '') {
            from = previous.to
            break
        }

        from = previous.from
    }

    return { from, to: doc.length }
}

function findLine(view: EditorView, markers: StateField<RangeSet<ItemMarker>>, item: Item) {
    for (let cursor = view.state.field(markers).iter(); cursor.value; cursor.next()) {
        if (cursor.value.item.type === item.type && cursor.value.item.id === item.id) {
            return view.state.doc.lineAt(cursor.from)
        }
    }

    return null
}

const theme = EditorView.theme({
    '&': {
        fontSize: '1rem',
    },
    '&.cm-focused': {
        outline: 'none',
    },
    '.cm-scroller': {
        fontFamily: 'var(--font-mono)',
        lineHeight: '1.75',
        overflow: 'visible',
    },
    '.cm-content': {
        minHeight: '70vh',
        padding: '0',
        caretColor: 'var(--color-gray-900)',
    },
    '.cm-line': {
        position: 'relative',
        padding: '0',
    },
    '.cm-project': {
        fontSize: '1.5rem',
        fontWeight: 'bold',
        lineHeight: '2',
    },
    '.cm-placeholder': {
        color: 'var(--color-gray-400)',
    },
    '.cm-complete': {
        position: 'absolute',
        top: '50%',
        left: '-2.25rem',
        transform: 'translateY(-50%)',
        width: '1.5rem',
        height: '1.5rem',
        borderRadius: '9999px',
        border: '1px solid var(--color-gray-300)',
        color: 'var(--color-gray-400)',
        fontSize: '0.75rem',
        fontWeight: 'normal',
        lineHeight: '1',
        cursor: 'pointer',
        opacity: '0',
    },
    '.cm-line:hover .cm-complete': {
        opacity: '1',
    },
    '.cm-complete:hover': {
        borderColor: 'var(--color-green-600)',
        backgroundColor: 'var(--color-green-600)',
        color: 'white',
    },
})

const textarea = document.querySelector<HTMLTextAreaElement>('#todo-markdown')

if (textarea) {
    const items: Record<string, Item> = JSON.parse(textarea.dataset.items ?? '{}')

    const urls: Record<ItemType, string> = {
        project: textarea.dataset.completeProjectUrl ?? '',
        task: textarea.dataset.completeTaskUrl ?? '',
    }

    const markers = createItemMarkers(items)

    const complete = async (view: EditorView, item: Item) => {
        const response = await fetch(urls[item.type].replace('__ID__', String(item.id)), { method: 'POST' })

        if (! response.ok) {
            alert(`Could not complete this ${item.type}`)

            return
        }

        const line = findLine(view, markers, item)

        view.dispatch({
            changes: line ? rangeOf(view, line, item.type) : [],
            effects: removeItem.of(item),
            annotations: Transaction.addToHistory.of(false),
        })
    }

    const view = new EditorView({
        doc: textarea.value,
        extensions: [
            history(),
            keymap.of([...defaultKeymap, ...historyKeymap, indentWithTab]),
            placeholder(textarea.placeholder),
            EditorView.lineWrapping,
            markers,
            completeButtons(markers, complete),
            projects,
            theme,
            EditorView.updateListener.of((update) => {
                if (update.docChanged) {
                    textarea.value = update.state.doc.toString()
                }
            }),
        ],
    })

    textarea.after(view.dom)
    textarea.hidden = true
    view.focus()
}
