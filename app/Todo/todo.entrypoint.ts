import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands'
import { RangeSet, RangeSetBuilder, RangeValue, StateEffect, StateField, Transaction } from '@codemirror/state'
import type { EditorState, Line, Range } from '@codemirror/state'
import { Decoration, EditorView, keymap, placeholder, ViewPlugin, WidgetType } from '@codemirror/view'
import type { DecorationSet, ViewUpdate } from '@codemirror/view'

type ItemType = 'project' | 'task'

type Item = {
    type: ItemType
    id: number
    completed: boolean
}

const projectLine = Decoration.line({ class: 'cm-project' })
const completedLine = Decoration.line({ class: 'cm-completed' })
const mutedLine = Decoration.line({ class: 'cm-muted' })

function isProject(line: Line): boolean {
    return line.text.startsWith('# ')
}

function decorateProjects(view: EditorView): DecorationSet {
    const builder = new RangeSetBuilder<Decoration>()

    for (const { from, to } of view.visibleRanges) {
        for (let position = from; position <= to;) {
            const line = view.state.doc.lineAt(position)

            if (isProject(line)) {
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
        return other.item.type === this.item.type
            && other.item.id === this.item.id
            && other.item.completed === this.item.completed
            && other.text === this.text
    }
}

function isSameItem(a: Item, b: Item): boolean {
    return a.type === b.type && a.id === b.id
}

/**
 * Moves the text between `from` and `to` (old document positions) to `insertAt`, preceded by `prefix`,
 * and marks the given item as completed.
 */
type Move = {
    item: Item
    from: number
    to: number
    insertAt: number
    prefix: string
}

const moveItem = StateEffect.define<Move>()

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
            const moves = transaction.effects.filter((effect) => effect.is(moveItem)).map((effect) => effect.value as Move)

            if (moves.length === 0) {
                return markers.map(transaction.changes)
            }

            const isMoved = (from: number) => moves.some((move) => from >= move.from && from < move.to)
            const isCompleted = (item: Item) => moves.some((move) => isSameItem(move.item, item))
            const moved: Range<ItemMarker>[] = []

            for (const move of moves) {
                const start = transaction.changes.mapPos(move.insertAt, -1) + move.prefix.length

                markers.between(move.from, move.to, (from, _to, marker) => {
                    if (from < move.to) {
                        moved.push(new ItemMarker(marker.item, marker.text).range(start + from - move.from))
                    }
                })
            }

            markers = markers
                .update({ filter: (from) => ! isMoved(from) })
                .map(transaction.changes)
                .update({ add: moved, sort: true })

            const completed: Range<ItemMarker>[] = []

            markers.between(0, transaction.state.doc.length, (from, _to, marker) => {
                if (isCompleted(marker.item)) {
                    completed.push(new ItemMarker({ ...marker.item, completed: true }, marker.text).range(from))
                }
            })

            return markers.update({
                filter: (_from, _to, marker) => ! isCompleted(marker.item),
                add: completed,
                sort: true,
            })
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
        return isSameItem(other.item, this.item)
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

/**
 * A block runs from a project's title until the next project, or the end of the document.
 * Lines before the first project form a block of their own.
 */
function blockOf(state: EditorState, line: Line): { start: Line, last: Line, next: Line | null } {
    const doc = state.doc

    let start = line

    while (! isProject(start) && start.number > 1) {
        start = doc.line(start.number - 1)
    }

    let last = start
    let next: Line | null = null

    for (let number = start.number + 1; number <= doc.lines; number++) {
        const current = doc.line(number)

        if (isProject(current)) {
            next = current
            break
        }

        if (current.text.trim() !== '') {
            last = current
        }
    }

    return { start, last, next }
}

function lastFilledLine(state: EditorState): Line {
    for (let number = state.doc.lines; number > 1; number--) {
        const line = state.doc.line(number)

        if (line.text.trim() !== '') {
            return line
        }
    }

    return state.doc.line(1)
}

function itemDecorations(markers: StateField<RangeSet<ItemMarker>>, complete: (view: EditorView, item: Item) => void) {
    return EditorView.decorations.compute(['doc', markers], (state) => {
        const decorations: Range<Decoration>[] = []
        const decoratedLines = new Set<number>()

        for (let cursor = state.field(markers).iter(); cursor.value; cursor.next()) {
            const line = state.doc.lineAt(cursor.from)
            const { item, text } = cursor.value

            if (line.from !== cursor.from || line.text !== text || decoratedLines.has(line.number)) {
                continue
            }

            decoratedLines.add(line.number)

            if (! item.completed) {
                decorations.push(Decoration.widget({ widget: new CompleteButton(item, complete), side: -1 }).range(line.from))

                continue
            }

            decorations.push(completedLine.range(line.from))

            if (item.type === 'project') {
                const { last } = blockOf(state, line)

                for (let number = line.number + 1; number <= last.number; number++) {
                    decorations.push(mutedLine.range(state.doc.line(number).from))
                }
            }
        }

        return Decoration.set(decorations, true)
    })
}

/**
 * Completed tasks move to the bottom of their project, completed projects move to the bottom of the workspace.
 */
function moveToBottom(state: EditorState, line: Line, item: Item): Move | null {
    if (item.type === 'task') {
        const { last } = blockOf(state, line)

        if (last.number <= line.number) {
            return null
        }

        return { item, from: line.from, to: line.to + 1, insertAt: last.to, prefix: '\n' }
    }

    const { next } = blockOf(state, line)

    if (! next) {
        return null
    }

    return { item, from: line.from, to: next.from, insertAt: lastFilledLine(state).to, prefix: '\n\n' }
}

function findLine(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, item: Item): Line | null {
    for (let cursor = state.field(markers).iter(); cursor.value; cursor.next()) {
        if (isSameItem(cursor.value.item, item)) {
            return state.doc.lineAt(cursor.from)
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
    '.cm-completed': {
        color: 'var(--color-gray-400)',
        textDecoration: 'line-through',
    },
    '.cm-muted': {
        color: 'var(--color-gray-400)',
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

        const line = findLine(view.state, markers, item)

        if (! line) {
            return
        }

        const move = moveToBottom(view.state, line, item)
            ?? { item, from: line.from, to: line.from, insertAt: line.from, prefix: '' }

        const text = view.state.doc.sliceString(move.from, move.to).replace(/\n+$/, '')

        view.dispatch({
            changes: move.from === move.to ? [] : [
                { from: move.from, to: move.to },
                { from: move.insertAt, insert: move.prefix + text },
            ],
            effects: moveItem.of(move),
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
            itemDecorations(markers, complete),
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
