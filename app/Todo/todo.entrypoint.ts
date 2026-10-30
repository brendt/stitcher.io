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

// A hashtag or mention is a word within a task that starts with `#` or `@`.
// Keep this in sync with `TodoMarkdown::TAG`.
const TAG = /(^|\s)([#@][\p{L}\p{N}_-]+)/gu

const projectLine = Decoration.line({ class: 'cm-project' })
const completedLine = Decoration.line({ class: 'cm-completed' })
const mutedLine = Decoration.line({ class: 'cm-muted' })

function isProject(line: Line): boolean {
    return line.text.startsWith('# ')
}

function isTask(line: Line): boolean {
    return line.text.startsWith('- ')
}

function decorateSyntax(view: EditorView): DecorationSet {
    const builder = new RangeSetBuilder<Decoration>()

    for (const { from, to } of view.visibleRanges) {
        for (let position = from; position <= to;) {
            const line = view.state.doc.lineAt(position)

            if (isProject(line)) {
                builder.add(line.from, line.from, projectLine)
            }

            if (isTask(line)) {
                for (const match of line.text.matchAll(TAG)) {
                    const start = line.from + match.index + match[1].length
                    const tag = match[2]

                    builder.add(start, start + tag.length, Decoration.mark({ class: 'cm-tag', attributes: { 'data-tag': tag } }))
                }
            }

            position = line.to + 1
        }
    }

    return builder.finish()
}

const syntax = ViewPlugin.fromClass(
    class {
        decorations: DecorationSet

        constructor(view: EditorView) {
            this.decorations = decorateSyntax(view)
        }

        update(update: ViewUpdate) {
            if (update.docChanged || update.viewportChanged) {
                this.decorations = decorateSyntax(update.view)
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
        return isSameItem(other.item, this.item)
            && other.item.completed === this.item.completed
            && other.text === this.text
    }
}

// Text inserted right at the start of a line ends up before that line's marker, instead of taking it over.
ItemMarker.prototype.startSide = ItemMarker.prototype.endSide = 1

function isSameItem(a: Item, b: Item): boolean {
    return a.type === b.type && a.id === b.id
}

/**
 * Removes `from` until `to` (old document positions) and inserts `insert` at `insertAt`.
 * The item's text starts at `textFrom` in the old document, and at `offset` within `insert`.
 */
type Move = {
    item: Item
    completed: boolean
    from: number
    to: number
    textFrom: number
    textLength: number
    insertAt: number
    insert: string
    offset: number
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

            const isMoved = (from: number) => moves.some((move) => move.insert !== '' && from >= move.textFrom && from < move.textFrom + move.textLength)
            const moveOf = (item: Item) => moves.find((move) => isSameItem(move.item, item))
            const moved: Range<ItemMarker>[] = []

            for (const move of moves) {
                if (move.insert === '') {
                    continue
                }

                const start = transaction.changes.mapPos(move.insertAt, -1) + move.offset

                markers.between(move.textFrom, move.textFrom + move.textLength, (from, _to, marker) => {
                    if (from < move.textFrom + move.textLength) {
                        moved.push(new ItemMarker(marker.item, marker.text).range(start + from - move.textFrom))
                    }
                })
            }

            markers = markers
                .update({ filter: (from) => ! isMoved(from) })
                .map(transaction.changes)
                .update({ add: moved, sort: true })

            const updated: Range<ItemMarker>[] = []

            markers.between(0, transaction.state.doc.length, (from, _to, marker) => {
                const move = moveOf(marker.item)

                if (move) {
                    updated.push(new ItemMarker({ ...marker.item, completed: move.completed }, marker.text).range(from))
                }
            })

            return markers.update({
                filter: (_from, _to, marker) => ! moveOf(marker.item),
                add: updated,
                sort: true,
            })
        },
    })
}

class CheckButton extends WidgetType {
    constructor(
        readonly item: Item,
        readonly toggle: (view: EditorView, item: Item) => void,
    ) {
        super()
    }

    eq(other: CheckButton): boolean {
        return isSameItem(other.item, this.item) && other.item.completed === this.item.completed
    }

    toDOM(view: EditorView): HTMLElement {
        const button = document.createElement('button')

        button.type = 'button'
        button.className = this.item.completed ? 'cm-check cm-check-completed' : 'cm-check'
        button.title = this.item.completed ? `Mark ${this.item.type} as open` : `Mark ${this.item.type} as completed`
        button.setAttribute('aria-label', button.title)
        button.textContent = '✓'
        button.addEventListener('mousedown', (event) => event.preventDefault())
        button.addEventListener('click', () => this.toggle(view, this.item))

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

function lastFilledLine(state: EditorState, before: number = state.doc.lines + 1): Line {
    for (let number = before - 1; number > 1; number--) {
        const line = state.doc.line(number)

        if (line.text.trim() !== '') {
            return line
        }
    }

    return state.doc.line(1)
}

/**
 * Returns the item on the given line, as long as that line still has the text it had when the item was loaded.
 */
function itemOn(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line): Item | null {
    let found: Item | null = null

    state.field(markers).between(line.from, line.from, (from, _to, marker) => {
        if (from === line.from && marker.text === line.text) {
            found = marker.item

            return false
        }
    })

    return found
}

function itemLines(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, type: ItemType, from: number, to: number): { line: Line, item: Item }[] {
    const lines: { line: Line, item: Item }[] = []

    for (let number = from; number <= to; number++) {
        const line = state.doc.line(number)
        const item = itemOn(state, markers, line)

        if (item?.type === type) {
            lines.push({ line, item })
        }
    }

    return lines
}

function itemDecorations(markers: StateField<RangeSet<ItemMarker>>, toggle: (view: EditorView, item: Item) => void) {
    return EditorView.decorations.compute(['doc', markers], (state) => {
        const decorations: Range<Decoration>[] = []

        for (let number = 1; number <= state.doc.lines; number++) {
            const line = state.doc.line(number)
            const item = itemOn(state, markers, line)

            if (! item) {
                continue
            }

            decorations.push(Decoration.widget({ widget: new CheckButton(item, toggle), side: -1 }).range(line.from))

            if (! item.completed) {
                continue
            }

            decorations.push(completedLine.range(line.from))

            if (item.type === 'project') {
                const { last } = blockOf(state, line)

                for (let next = line.number + 1; next <= last.number; next++) {
                    decorations.push(mutedLine.range(state.doc.line(next).from))
                }
            }
        }

        return Decoration.set(decorations, true)
    })
}

/**
 * Cuts a task's line, or a project with all of its tasks, and places it right before `target`.
 * Without a target, it goes to the bottom of its project (tasks) or the bottom of the workspace (projects).
 */
function moveTo(state: EditorState, line: Line, item: Item, completed: boolean, target: Line | null): Move | null {
    const { last, next } = blockOf(state, line)
    const isTask = item.type === 'task'
    const separator = isTask ? '\n' : '\n\n'
    const end = isTask ? last : lastFilledLine(state)

    const isInPlace = target
        ? target.number === (isTask ? line.number + 1 : next?.number)
        : end.number <= (isTask ? line.number : last.number)

    if (isInPlace) {
        return null
    }

    const text = isTask ? line.text : state.doc.sliceString(line.from, last.to)

    const [from, to] = isTask
        ? (line.number < state.doc.lines ? [line.from, line.to + 1] : [line.from - 1, line.to])
        : (next ? [line.from, next.from] : [lastFilledLine(state, line.number).to, state.doc.length])

    return {
        item,
        completed,
        from,
        to,
        textFrom: line.from,
        textLength: text.length,
        insertAt: target ? target.from : end.to,
        insert: target ? text + separator : separator + text,
        offset: target ? 0 : separator.length,
    }
}

/**
 * Completed items go to the top of the completed items, so that the most recently completed one comes first.
 * Reopened items go back between the open items, ordered by when they were created.
 * This is the same order as when loading the page.
 */
function targetFor(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line, item: Item, completed: boolean): Line | null {
    const { start, last } = blockOf(state, line)

    const siblings = item.type === 'task'
        ? itemLines(state, markers, 'task', start.number, last.number)
        : itemLines(state, markers, 'project', 1, state.doc.lines)

    const target = siblings.find(({ item: sibling }) => ! isSameItem(sibling, item) && (completed
        ? sibling.completed
        : sibling.completed || sibling.id > item.id))

    return target?.line ?? null
}

function findLine(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, item: Item): Line | null {
    for (let cursor = state.field(markers).iter(); cursor.value; cursor.next()) {
        if (isSameItem(cursor.value.item, item)) {
            return state.doc.lineAt(cursor.from)
        }
    }

    return null
}

function toggleItem(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, item: Item): Transaction | null {
    const line = findLine(state, markers, item)

    if (! line) {
        return null
    }

    const completed = ! item.completed

    const move = moveTo(state, line, item, completed, targetFor(state, markers, line, item, completed))
        ?? { item, completed, from: line.from, to: line.from, textFrom: line.from, textLength: 0, insertAt: line.from, insert: '', offset: 0 }

    return state.update({
        changes: move.insert === '' ? [] : [
            { from: move.from, to: move.to },
            { from: move.insertAt, insert: move.insert },
        ],
        effects: moveItem.of(move),
        annotations: Transaction.addToHistory.of(false),
    })
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
    '.cm-tag': {
        fontWeight: 'bold',
        cursor: 'pointer',
    },
    '.cm-tag:hover': {
        textDecoration: 'underline',
    },
    '.cm-placeholder': {
        color: 'var(--color-gray-400)',
    },
    '.cm-check': {
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
    '.cm-line:hover .cm-check': {
        opacity: '1',
    },
    '.cm-check:hover': {
        borderColor: 'var(--color-green-600)',
        backgroundColor: 'var(--color-green-600)',
        color: 'white',
    },
    '.cm-check-completed': {
        borderColor: 'var(--color-green-600)',
        backgroundColor: 'var(--color-green-600)',
        color: 'white',
    },
    '.cm-check-completed:hover': {
        borderColor: 'var(--color-gray-300)',
        backgroundColor: 'white',
        color: 'var(--color-gray-400)',
    },
})

const textarea = document.querySelector<HTMLTextAreaElement>('#todo-markdown')

if (textarea) {
    const items: Record<string, Item> = JSON.parse(textarea.dataset.items ?? '{}')

    const urls: Record<ItemType, { complete: string, reopen: string }> = {
        project: {
            complete: textarea.dataset.completeProjectUrl ?? '',
            reopen: textarea.dataset.reopenProjectUrl ?? '',
        },
        task: {
            complete: textarea.dataset.completeTaskUrl ?? '',
            reopen: textarea.dataset.reopenTaskUrl ?? '',
        },
    }

    const filterUrl = textarea.dataset.filterUrl

    const markers = createItemMarkers(items)

    const toggle = async (view: EditorView, item: Item) => {
        const url = item.completed ? urls[item.type].reopen : urls[item.type].complete
        const response = await fetch(url.replace('__ID__', String(item.id)), { method: 'POST' })

        if (! response.ok) {
            alert(`Could not update this ${item.type}`)

            return
        }

        const transaction = toggleItem(view.state, markers, item)

        if (transaction) {
            view.dispatch(transaction)
        }
    }

    const view = new EditorView({
        doc: textarea.value,
        extensions: [
            history(),
            keymap.of([...defaultKeymap, ...historyKeymap, indentWithTab]),
            placeholder(textarea.placeholder),
            EditorView.lineWrapping,
            markers,
            itemDecorations(markers, toggle),
            syntax,
            theme,
            EditorView.domEventHandlers({
                mousedown(event) {
                    const tag = (event.target as HTMLElement).closest<HTMLElement>('.cm-tag')?.dataset.tag

                    if (! tag || ! filterUrl) {
                        return false
                    }

                    event.preventDefault()
                    window.location.href = `${filterUrl}?filter=${encodeURIComponent(tag)}`

                    return true
                },
            }),
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
