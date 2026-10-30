import { autocompletion } from '@codemirror/autocomplete'
import type { CompletionContext, CompletionResult } from '@codemirror/autocomplete'
import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands'
import { MapMode, RangeSet, RangeSetBuilder, RangeValue, StateEffect, StateField, Transaction } from '@codemirror/state'
import type { EditorState, Line, Range } from '@codemirror/state'
import { Decoration, EditorView, keymap, placeholder, ViewPlugin, WidgetType } from '@codemirror/view'
import type { DecorationSet, ViewUpdate } from '@codemirror/view'

type ItemType = 'project' | 'task'

type Item = {
    type: ItemType
    // `null` until a newly written project or task has been saved
    id: number | null
    // Identifies the item within the editor, also before it has an ID
    key: string
    completed: boolean
}

// A hashtag or mention is a word within a task that starts with `#` or `@`.
// Keep this in sync with `TodoMarkdown::TAG`, except that a `#` or `@` on its own is matched here as well,
// so that it's already styled while you're still typing the rest of the word.
const TAG = /(^|\s)([#@][\p{L}\p{N}_-]*)/gu

// Just a `#` or `@` without a word yet is styled like a tag, but can't be clicked to filter on
const tagSign = Decoration.mark({ class: 'cm-tag-sign' })

// The hashtags and mentions the tasks are currently filtered by, matched case-insensitively like the server does
let activeFilters: string[] = []

function isActiveFilter(tag: string): boolean {
    return activeFilters.some((filter) => filter.toLowerCase() === tag.toLowerCase())
}

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

                    builder.add(start, start + tag.length, tag.length === 1 ? tagSign : Decoration.mark({
                        class: isActiveFilter(tag) ? 'cm-tag cm-tag-active' : 'cm-tag',
                        attributes: { 'data-tag': tag },
                    }))
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
 */
class ItemMarker extends RangeValue {
    constructor(
        readonly item: Item,
    ) {
        super()
    }

    eq(other: ItemMarker): boolean {
        return isSameItem(other.item, this.item)
            && other.item.id === this.item.id
            && other.item.completed === this.item.completed
    }
}

// Text inserted right at the start of a line ends up before that line's marker, instead of taking it over.
ItemMarker.prototype.startSide = ItemMarker.prototype.endSide = 1

// Deleting the first character of a line (its `#` or `-`) means that line no longer is that item.
ItemMarker.prototype.mapMode = MapMode.TrackAfter

function isSameItem(a: Item, b: Item): boolean {
    return a.key === b.key
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

// Tracks lines that were written in the editor, so that they can be matched with their ID once saved
const addItems = StateEffect.define<Range<ItemMarker>[]>()

// Assigns IDs to saved items, keyed by their `key`
const assignIds = StateEffect.define<Record<string, number>>()

function createItemMarkers(items: Record<string, Item>) {
    return StateField.define<RangeSet<ItemMarker>>({
        create(state) {
            const builder = new RangeSetBuilder<ItemMarker>()

            for (const [lineNumber, item] of Object.entries(items).sort(([a], [b]) => Number(a) - Number(b))) {
                const line = state.doc.line(Number(lineNumber))

                builder.add(line.from, line.from, new ItemMarker(item))
            }

            return builder.finish()
        },

        update(markers, transaction) {
            markers = moveMarkers(markers, transaction)

            for (const effect of transaction.effects) {
                if (effect.is(addItems)) {
                    markers = markers.update({ add: effect.value, sort: true })
                }

                if (effect.is(assignIds)) {
                    const ids = effect.value
                    const assigned: Range<ItemMarker>[] = []

                    markers.between(0, transaction.state.doc.length, (from, _to, marker) => {
                        if (marker.item.key in ids) {
                            assigned.push(new ItemMarker({ ...marker.item, id: ids[marker.item.key] }).range(from))
                        }
                    })

                    markers = markers.update({
                        filter: (_from, _to, marker) => ! (marker.item.key in ids),
                        add: assigned,
                        sort: true,
                    })
                }
            }

            return markers
        },
    })
}

function moveMarkers(markers: RangeSet<ItemMarker>, transaction: Transaction): RangeSet<ItemMarker> {
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
                moved.push(new ItemMarker(marker.item).range(start + from - move.textFrom))
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
            updated.push(new ItemMarker({ ...marker.item, completed: move.completed }).range(from))
        }
    })

    return markers.update({
        filter: (_from, _to, marker) => ! moveOf(marker.item),
        add: updated,
        sort: true,
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
        return isSameItem(other.item, this.item) && other.item.id === this.item.id && other.item.completed === this.item.completed
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
 * Returns the item on the given line, as long as that line is still a project or task like the item is.
 */
function itemOn(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line): Item | null {
    let found: Item | null = null

    state.field(markers).between(line.from, line.from, (from, _to, marker) => {
        if (from === line.from && (marker.item.type === 'project' ? isProject(line) : isTask(line))) {
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

            if (item.id !== null) {
                decorations.push(Decoration.widget({ widget: new CheckButton(item, toggle), side: -1 }).range(line.from))
            }

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
        : sibling.completed || sibling.id === null || (item.id !== null && sibling.id > item.id)))

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

const TAG_WORD = /^[#@][\p{L}\p{N}_-]*$/u

/**
 * Suggests hashtags and mentions while typing one in a task: the ones used before, most used first,
 * followed by new ones that were written in the editor but haven't been loaded from the server yet.
 */
function tagCompletions(usedTags: string[]) {
    return (context: CompletionContext): CompletionResult | null => {
        const word = context.matchBefore(/[#@][\p{L}\p{N}_-]*/u)

        if (! word) {
            return null
        }

        const line = context.state.doc.lineAt(word.from)
        const before = context.state.sliceDoc(word.from - 1, word.from)

        if (! isTask(line) || (word.from > line.from && ! /\s/.test(before))) {
            return null
        }

        const writtenTags: string[] = []

        for (let number = 1; number <= context.state.doc.lines; number++) {
            const current = context.state.doc.line(number)

            if (! isTask(current)) {
                continue
            }

            for (const match of current.text.matchAll(TAG)) {
                const from = current.from + match.index + match[1].length

                if (from !== word.from && match[2].length > 1) {
                    writtenTags.push(match[2])
                }
            }
        }

        const seen = new Set<string>([word.text.toLowerCase()])
        const tags = [...usedTags, ...writtenTags].filter((tag) => {
            const key = tag.toLowerCase()

            if (tag[0] !== word.text[0] || seen.has(key)) {
                return false
            }

            seen.add(key)

            return true
        })

        return {
            from: word.from,
            options: tags.map((tag, index) => ({ label: tag, boost: Math.max(-99, -index) })),
            validFor: TAG_WORD,
        }
    }
}

/**
 * Pressing Enter within a task starts a new task right away.
 * Pressing Enter on a task that's still empty removes its `- ` again, which leaves a blank line,
 * and continues on the next line, for example to start a new project.
 */
function continueTask(view: EditorView): boolean {
    const { state } = view
    const selection = state.selection.main

    if (state.selection.ranges.length > 1 || ! selection.empty) {
        return false
    }

    const line = state.doc.lineAt(selection.head)

    if (! isTask(line) || selection.head < line.from + 2) {
        return false
    }

    if (line.text.trim() === '-') {
        view.dispatch({
            changes: { from: line.from, to: line.to, insert: '\n' },
            selection: { anchor: line.from + 1 },
            scrollIntoView: true,
            userEvent: 'input',
        })

        return true
    }

    // When splitting a task in two, the second one shouldn't start with the space that separated them
    const spaces = line.text.slice(selection.head - line.from).match(/^ */)?.[0].length ?? 0

    view.dispatch({
        changes: { from: selection.head, to: selection.head + spaces, insert: '\n- ' },
        selection: { anchor: selection.head + 3 },
        scrollIntoView: true,
        userEvent: 'input',
    })

    return true
}

type Known = Record<ItemType, Set<number>>

type Payload = {
    projects: {
        key: string
        id: number | null
        name: string
        tasks: { key: string, id: number | null, name: string }[]
    }[]
    deleted: Record<'projects' | 'tasks', number[]>
}

/**
 * Collects all projects and their tasks in the document, ready to be saved.
 * Lines that aren't tracked yet get a new item, which are returned so that they can be tracked from now on.
 * Items that were known before but can't be found anymore, are reported as deleted.
 * Tasks above the first project don't belong to any project, so they aren't saved.
 */
function snapshot(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, known: Known, createKey: () => string): { payload: Payload, added: Range<ItemMarker>[] } {
    const payload: Payload = { projects: [], deleted: { projects: [], tasks: [] } }
    const added: Range<ItemMarker>[] = []
    const present: Known = { project: new Set(), task: new Set() }

    for (let number = 1; number <= state.doc.lines; number++) {
        const line = state.doc.line(number)
        const type: ItemType | null = isProject(line) ? 'project' : (isTask(line) ? 'task' : null)
        const name = line.text.slice(2).trim()
        const project = payload.projects.at(-1)

        if (type === null || name === '' || (type === 'task' && ! project)) {
            continue
        }

        let item = itemOn(state, markers, line)

        if (! item) {
            item = { type, id: null, key: createKey(), completed: false }
            added.push(new ItemMarker(item).range(line.from))
        }

        if (item.id !== null) {
            present[type].add(item.id)
        }

        if (type === 'project') {
            payload.projects.push({ key: item.key, id: item.id, name, tasks: [] })
        } else {
            project?.tasks.push({ key: item.key, id: item.id, name })
        }
    }

    payload.deleted.projects = [...known.project].filter((id) => ! present.project.has(id))
    payload.deleted.tasks = [...known.task].filter((id) => ! present.task.has(id))

    return { payload, added }
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
    '.cm-tooltip.cm-tooltip-autocomplete': {
        border: '1px solid var(--color-gray-200)',
        borderRadius: '0.375rem',
        backgroundColor: 'white',
        boxShadow: '0 4px 12px rgb(0 0 0 / 0.08)',
        overflow: 'hidden',
    },
    '.cm-tooltip.cm-tooltip-autocomplete > ul': {
        fontFamily: 'var(--font-mono)',
        fontSize: '0.875rem',
        maxHeight: '12rem',
    },
    '.cm-tooltip.cm-tooltip-autocomplete > ul > li': {
        padding: '0.25rem 0.75rem',
    },
    '.cm-tooltip.cm-tooltip-autocomplete > ul > li[aria-selected]': {
        backgroundColor: 'var(--color-gray-100)',
        color: 'var(--color-gray-900)',
    },
    '.cm-completionMatchedText': {
        textDecoration: 'none',
        fontWeight: 'bold',
    },
    '.cm-tag-sign': {
        fontWeight: 'bold',
    },
    '.cm-tag-active': {
        textDecoration: 'underline',
        backgroundColor: 'var(--color-gray-100)',
        border: '1px solid black',
        borderRadius: '0.25rem',
        boxDecorationBreak: 'clone',
        padding: '0.125rem 0.375rem',
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
        left: '-2rem',
        transform: 'translateY(-50%)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        width: '1.125rem',
        height: '1.125rem',
        padding: '0',
        borderRadius: '0.25rem',
        border: '1.5px solid var(--color-gray-400)',
        backgroundColor: 'white',
        color: 'transparent',
        fontSize: '0.75rem',
        fontWeight: 'bold',
        lineHeight: '1',
        cursor: 'pointer',
        opacity: '0',
    },
    '.cm-line:hover .cm-check': {
        opacity: '1',
    },
    '.cm-check:hover': {
        borderColor: 'var(--color-gray-900)',
        color: 'var(--color-gray-900)',
    },
    '.cm-check-completed': {
        borderColor: 'var(--color-gray-900)',
        backgroundColor: 'var(--color-gray-900)',
        color: 'white',
    },
    '.cm-check-completed:hover': {
        borderColor: 'var(--color-gray-400)',
        backgroundColor: 'white',
        color: 'var(--color-gray-400)',
    },
})

const textarea = document.querySelector<HTMLTextAreaElement>('#todo-markdown')

if (textarea) {
    const items: Record<string, Item> = {}
    const known: Known = { project: new Set(), task: new Set() }

    for (const [lineNumber, item] of Object.entries<Omit<Item, 'key'> & { id: number }>(JSON.parse(textarea.dataset.items ?? '{}'))) {
        items[lineNumber] = { ...item, key: `${item.type}-${item.id}` }
        known[item.type].add(item.id)
    }

    let lastKey = 0
    const createKey = () => `new-${++lastKey}`

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
    const usedTags: string[] = JSON.parse(textarea.dataset.tags ?? '[]')
    activeFilters = (textarea.dataset.filter ?? '').split(' ').filter((filter) => filter !== '')
    const saveUrl = textarea.dataset.saveUrl
    const status = document.querySelector<HTMLElement>('#todo-status')

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

    // The cursor position is remembered per workspace and filter, so that refreshing the page continues where you left off
    const cursorKey = `todo-cursor:${window.location.pathname}${window.location.search}`
    const storedCursor = Number(window.localStorage.getItem(cursorKey))
    const cursor = Number.isInteger(storedCursor) ? Math.min(Math.max(storedCursor, 0), textarea.value.length) : 0

    const view = new EditorView({
        doc: textarea.value,
        selection: { anchor: cursor },
        extensions: [
            history(),
            keymap.of([{ key: 'Enter', run: continueTask }, ...defaultKeymap, ...historyKeymap, indentWithTab]),
            placeholder(textarea.placeholder),
            EditorView.lineWrapping,
            autocompletion({ override: [tagCompletions(usedTags)], icons: false }),
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
                    // Clicking a tag adds it to the filter, clicking a tag that's already being filtered on removes it again
                    const filters = isActiveFilter(tag)
                        ? activeFilters.filter((filter) => filter.toLowerCase() !== tag.toLowerCase())
                        : [...activeFilters, tag]

                    window.location.href = filters.length > 0 ? `${filterUrl}?filter=${encodeURIComponent(filters.join(' '))}` : filterUrl

                    return true
                },
            }),
            EditorView.updateListener.of((update) => {
                if (update.docChanged) {
                    textarea.value = update.state.doc.toString()
                    scheduleSave()
                }

                if (update.selectionSet || update.docChanged) {
                    window.localStorage.setItem(cursorKey, String(update.state.selection.main.head))
                }
            }),
        ],
    })

    // Saving waits until there haven't been any changes for a moment, and only one save runs at a time.
    const SAVE_DELAY = 1000
    const RETRY_DELAY = 5000

    let saveTimer: number | undefined
    let isSaving = false
    let hasChanges = false

    let statusTimer: number | undefined

    const showStatus = (text: string, clearAfter: number | null = null) => {
        window.clearTimeout(statusTimer)

        if (status) {
            status.textContent = text
        }

        if (clearAfter !== null) {
            statusTimer = window.setTimeout(() => showStatus(''), clearAfter)
        }
    }

    const scheduleSave = (delay: number = SAVE_DELAY) => {
        hasChanges = true
        window.clearTimeout(saveTimer)
        saveTimer = window.setTimeout(save, delay)
    }

    const save = async () => {
        if (isSaving || ! hasChanges || ! saveUrl) {
            return
        }

        isSaving = true
        hasChanges = false

        const { payload, added } = snapshot(view.state, markers, known, createKey)

        if (added.length > 0) {
            view.dispatch({ effects: addItems.of(added) })
        }

        showStatus('Saving…')

        try {
            const response = await fetch(saveUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(payload),
            })

            if (! response.ok) {
                throw new Error(`Saving failed with status ${response.status}`)
            }

            const { ids } = await response.json() as { ids: Record<string, number> }

            view.dispatch({ effects: assignIds.of(ids) })

            const types = new Map<string, ItemType>()

            for (const project of payload.projects) {
                types.set(project.key, 'project')
                project.tasks.forEach((task) => types.set(task.key, 'task'))
            }

            for (const [key, id] of Object.entries(ids)) {
                const type = types.get(key)

                if (type) {
                    known[type].add(id)
                }
            }

            payload.deleted.projects.forEach((id) => known.project.delete(id))
            payload.deleted.tasks.forEach((id) => known.task.delete(id))

            showStatus(hasChanges ? '' : 'Saved', 2000)
        } catch {
            showStatus('Not saved, retrying…')
            hasChanges = true
            window.clearTimeout(saveTimer)
            saveTimer = window.setTimeout(save, RETRY_DELAY)

            return
        } finally {
            isSaving = false
        }

        if (hasChanges) {
            scheduleSave()
        }
    }

    // Cmd+S (or Ctrl+S) saves right away, instead of opening the browser's save dialog
    window.addEventListener('keydown', (event) => {
        if (! (event.metaKey || event.ctrlKey) || event.key.toLowerCase() !== 's') {
            return
        }

        event.preventDefault()
        hasChanges = true
        window.clearTimeout(saveTimer)
        save()
    })

    // Changes that haven't been saved yet are still sent when leaving the page
    window.addEventListener('pagehide', () => {
        if (! hasChanges || ! saveUrl) {
            return
        }

        fetch(saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(snapshot(view.state, markers, known, createKey).payload),
            keepalive: true,
        })
    })

    textarea.after(view.dom)
    textarea.hidden = true
    view.focus()
    view.dispatch({ effects: EditorView.scrollIntoView(cursor, { y: 'center' }) })
}
