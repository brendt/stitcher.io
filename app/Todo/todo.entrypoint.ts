import { autocompletion } from '@codemirror/autocomplete'
import { codeFolding, foldedRanges, foldEffect, foldState, unfoldEffect } from '@codemirror/language'
import type { CompletionContext, CompletionResult } from '@codemirror/autocomplete'
import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands'
import { Annotation, EditorState, MapMode, RangeSet, RangeSetBuilder, RangeValue, StateEffect, StateField, Transaction } from '@codemirror/state'
import type { Line, Range } from '@codemirror/state'
import { Decoration, EditorView, keymap, placeholder, ViewPlugin, WidgetType } from '@codemirror/view'
import type { DecorationSet, ViewUpdate } from '@codemirror/view'

// Text is any other line within a project, which is saved but can't be completed
type ItemType = 'project' | 'task' | 'text'

type Item = {
    type: ItemType
    // `null` until a newly written project or task has been saved
    id: number | null
    // Only for projects, whether it was collapsed when loading the page
    collapsed?: boolean
    // Identifies the item within the editor, also before it has an ID
    key: string
    completed: boolean
}

// A tag is a word within a task that can be filtered on:
// a hashtag or mention starting with `#` or `@`, or a date formatted like `2026-10-30`.
// Keep this in sync with `TodoMarkdown::TAG`, except that a `#` or `@` on its own is matched here as well,
// so that it's already styled while you're still typing the rest of the word.
const TAG = /(^|\s)([#@][\p{L}\p{N}_-]*|\d{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12]\d|3[01]))(?![\p{L}\p{N}_-])/gu

// Just a `#` or `@` without a word yet is styled like a tag, but can't be clicked to filter on
const tagSign = Decoration.mark({ class: 'cm-tag-sign' })

// The hashtags and mentions the tasks are currently filtered by, matched case-insensitively like the server does
let activeFilters: string[] = []

function isActiveFilter(tag: string): boolean {
    return activeFilters.some((filter) => filter.toLowerCase() === tag.toLowerCase())
}

// The project that's shown on its own, matched by name like the server does
let activeProject: string | null = null

function isActiveProject(name: string): boolean {
    return activeProject !== null && activeProject.toLowerCase() === name.toLowerCase()
}

/**
 * The URL of the current page with other filters, like `?filter=#urgent @alex&project=Tempest`.
 */
function filterHref(baseUrl: string, filters: string[], project: string | null): string {
    const query = new URLSearchParams()

    if (filters.length > 0) {
        query.set('filter', filters.join(' '))
    }

    if (project !== null) {
        query.set('project', project)
    }

    return query.size > 0 ? `${baseUrl}?${query}` : baseUrl
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

function isText(line: Line): boolean {
    return ! isProject(line) && ! isTask(line) && line.text.trim() !== ''
}

function typeOf(line: Line): ItemType | null {
    if (isProject(line)) {
        return 'project'
    }

    if (isTask(line)) {
        return 'task'
    }

    return isText(line) ? 'text' : null
}

// A link is a word starting with `http://` or `https://`, without punctuation that follows it, like a full stop
const LINK = /https?:\/\/[^\s]*[^\s.,;:!?)\]'"]/gu

function decorateSyntax(view: EditorView): DecorationSet {
    const decorations: Range<Decoration>[] = []

    for (const { from, to } of view.visibleRanges) {
        for (let position = from; position <= to;) {
            const line = view.state.doc.lineAt(position)

            if (isProject(line)) {
                decorations.push(projectLine.range(line.from))

                // The title can be clicked to only show that project
                const name = line.text.slice(2).trim()
                const start = line.from + line.text.indexOf(name, 2)

                if (name !== '') {
                    decorations.push(Decoration.mark({
                        class: isActiveProject(name) ? 'cm-project-title cm-project-title-active' : 'cm-project-title',
                        attributes: { 'data-project': name },
                    }).range(start, start + name.length))
                }
            }

            if (isTask(line) || isText(line)) {
                for (const match of line.text.matchAll(TAG)) {
                    const start = line.from + match.index + match[1].length
                    const tag = match[2]

                    decorations.push((tag.length === 1 ? tagSign : Decoration.mark({
                        class: isActiveFilter(tag) ? 'cm-tag cm-tag-active' : 'cm-tag',
                        attributes: { 'data-tag': tag },
                    })).range(start, start + tag.length))
                }

                for (const match of line.text.matchAll(LINK)) {
                    const start = line.from + match.index

                    decorations.push(Decoration.mark({ class: 'cm-link', attributes: { 'data-href': match[0] } }).range(start, start + match[0].length))
                }
            }

            position = line.to + 1
        }
    }

    return Decoration.set(decorations, true)
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
        // Drawn instead of using a `✓` character, so that it doesn't depend on the font or styling of the line it's on
        button.innerHTML = '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3.5 8.5l3 3 6-7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        button.addEventListener('mousedown', (event) => event.preventDefault())
        button.addEventListener('click', () => this.toggle(view, this.item))

        return button
    }

    ignoreEvent(): boolean {
        return true
    }
}

class DragHandle extends WidgetType {
    constructor(
        readonly drag: (view: EditorView, event: MouseEvent, handle: HTMLElement) => void,
    ) {
        super()
    }

    eq(): boolean {
        return true
    }

    toDOM(view: EditorView): HTMLElement {
        const handle = document.createElement('span')

        handle.className = 'cm-drag'
        handle.title = 'Drag to reorder'
        handle.setAttribute('aria-hidden', 'true')
        handle.innerHTML = '<svg viewBox="0 0 10 16" aria-hidden="true"><g fill="currentColor"><circle cx="3" cy="3" r="1.25"/><circle cx="7" cy="3" r="1.25"/><circle cx="3" cy="8" r="1.25"/><circle cx="7" cy="8" r="1.25"/><circle cx="3" cy="13" r="1.25"/><circle cx="7" cy="13" r="1.25"/></g></svg>'
        handle.addEventListener('mousedown', (event) => this.drag(view, event, handle))

        return handle
    }

    ignoreEvent(): boolean {
        return true
    }
}

/**
 * Open tasks and text within a project can be put in another order. Completed tasks stay below them, ordered by when they were completed.
 */
function isSortable(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line): boolean {
    const type = typeOf(line)

    if (type !== 'task' && type !== 'text') {
        return false
    }

    return isProject(blockOf(state, line).start) && ! itemOn(state, markers, line)?.completed
}

/**
 * The lines a line can be dragged between: the other open tasks and text in its project.
 */
function sortableLines(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line): Line[] {
    const { start, last } = blockOf(state, line)
    const lines: Line[] = []

    for (let number = start.number + 1; number <= last.number; number++) {
        const current = state.doc.line(number)

        if (isSortable(state, markers, current)) {
            lines.push(current)
        }
    }

    return lines
}

/**
 * Moves a line to another slot between `lines`: slot 0 is before the first line, the last slot is after the last line.
 * The item on the line keeps following it, so that it stays the same task or text.
 */
function reorder(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line, lines: Line[], slot: number): Transaction | null {
    const current = lines.findIndex((other) => other.number === line.number)

    // The slots right before and after the line itself keep it where it is
    if (slot === current || slot === current + 1) {
        return null
    }

    const isBefore = slot < lines.length
    const target = isBefore ? lines[slot] : lines[lines.length - 1]
    const [from, to] = line.number < state.doc.lines ? [line.from, line.to + 1] : [line.from - 1, line.to]
    const insertAt = isBefore ? target.from : target.to
    const insert = isBefore ? line.text + '\n' : '\n' + line.text
    const item = itemOn(state, markers, line)

    return state.update({
        changes: [{ from, to }, { from: insertAt, insert }],
        effects: item ? moveItem.of({
            item,
            completed: item.completed,
            from,
            to,
            textFrom: line.from,
            textLength: line.length,
            insertAt,
            insert,
            offset: isBefore ? 0 : 1,
        }) : [],
        annotations: Transaction.addToHistory.of(false),
    })
}

class CollapseButton extends WidgetType {
    constructor(
        readonly item: Item,
        readonly isCollapsed: boolean,
        readonly collapse: (view: EditorView, item: Item) => void,
    ) {
        super()
    }

    eq(other: CollapseButton): boolean {
        return isSameItem(other.item, this.item) && other.isCollapsed === this.isCollapsed
    }

    toDOM(view: EditorView): HTMLElement {
        const button = document.createElement('button')

        button.type = 'button'
        button.className = this.isCollapsed ? 'cm-collapse cm-collapse-collapsed' : 'cm-collapse'
        button.title = this.isCollapsed ? 'Expand project' : 'Collapse project'
        button.setAttribute('aria-label', button.title)
        button.setAttribute('aria-expanded', String(! this.isCollapsed))
        button.textContent = this.isCollapsed ? '▸' : '▾'
        button.addEventListener('mousedown', (event) => event.preventDefault())
        button.addEventListener('click', () => this.collapse(view, this.item))

        return button
    }

    ignoreEvent(): boolean {
        return true
    }
}

class ArchiveButton extends WidgetType {
    constructor(
        readonly item: Item,
        readonly isArchived: boolean,
        readonly archive: (view: EditorView, item: Item) => void,
    ) {
        super()
    }

    eq(other: ArchiveButton): boolean {
        return isSameItem(other.item, this.item) && other.isArchived === this.isArchived
    }

    toDOM(view: EditorView): HTMLElement {
        const button = document.createElement('button')

        button.type = 'button'
        button.className = 'cm-archive'
        button.textContent = this.isArchived ? 'Unarchive' : 'Archive'
        button.addEventListener('mousedown', (event) => event.preventDefault())
        button.addEventListener('click', () => this.archive(view, this.item))

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
        if (from === line.from && marker.item.type === typeOf(line)) {
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

type Actions = {
    drag: (view: EditorView, event: MouseEvent, handle: HTMLElement) => void
    toggle: (view: EditorView, item: Item) => void
    collapse: (view: EditorView, item: Item) => void
    archive: (view: EditorView, item: Item) => void
    mode: Mode
}

// What the page is showing, see `TodoMode`:
// a workspace can be edited, the tasks coming up can only be completed, archived projects can only be unarchived
type Mode = 'workspace' | 'soon' | 'archived'

function itemDecorations(markers: StateField<RangeSet<ItemMarker>>, actions: Actions) {
    return EditorView.decorations.compute(['doc', markers, foldState], (state) => {
        const decorations: Range<Decoration>[] = []

        for (let number = 1; number <= state.doc.lines; number++) {
            const line = state.doc.line(number)
            const item = itemOn(state, markers, line)

            if (actions.mode === 'workspace' && isSortable(state, markers, line)) {
                decorations.push(Decoration.widget({ widget: new DragHandle(actions.drag), side: -1 }).range(line.from))
            }

            if (! item) {
                continue
            }

            // A collapsed project can't be completed, its tasks need to be visible for that
            const isCollapsedProject = item.type === 'project' && foldAt(state, line) !== null

            if (item.id !== null && item.type !== 'text' && actions.mode !== 'archived' && ! isCollapsedProject) {
                decorations.push(Decoration.widget({ widget: new CheckButton(item, actions.toggle), side: -1 }).range(line.from))
            }

            if (item.id !== null && item.type === 'project') {
                // Archived projects are always shown completely
                if (actions.mode === 'workspace' && projectFold(state, line)) {
                    decorations.push(Decoration.widget({
                        widget: new CollapseButton(item, foldAt(state, line) !== null, actions.collapse),
                        side: -1,
                    }).range(line.from))
                }

                if ((actions.mode === 'workspace' && item.completed) || actions.mode === 'archived') {
                    decorations.push(Decoration.widget({
                        widget: new ArchiveButton(item, actions.mode === 'archived', actions.archive),
                        side: -1,
                    }).range(line.from))
                }
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
 * The part of a project that's hidden when it's collapsed: everything after its title, until its last task.
 */
function projectFold(state: EditorState, line: Line): { from: number, to: number } | null {
    const { last } = blockOf(state, line)

    return last.number > line.number ? { from: line.to, to: last.to } : null
}

function foldAt(state: EditorState, line: Line): { from: number, to: number } | null {
    let folded: { from: number, to: number } | null = null

    foldedRanges(state).between(line.to, line.to, (from, to) => {
        if (from === line.to) {
            folded = { from, to }

            return false
        }
    })

    return folded
}

// Marks the transaction that collapses projects when loading the page, which doesn't need to be saved
const restoringFolds = Annotation.define<boolean>()

/**
 * The part of the document that's removed when a project disappears from it, together with its tasks.
 */
function projectRange(state: EditorState, line: Line): { from: number, to: number } {
    const { next } = blockOf(state, line)

    if (next) {
        return { from: line.from, to: next.from }
    }

    return { from: line.number > 1 ? lastFilledLine(state, line.number).to : 0, to: state.doc.length }
}

/**
 * Cuts a task's line and places it right before `target`, or at the bottom of its project without a target.
 */
function moveTo(state: EditorState, line: Line, item: Item, completed: boolean, target: Line | null): Move | null {
    const { last } = blockOf(state, line)

    const isInPlace = target
        ? target.number === line.number + 1
        : last.number <= line.number

    if (isInPlace) {
        return null
    }

    const [from, to] = line.number < state.doc.lines ? [line.from, line.to + 1] : [line.from - 1, line.to]

    return {
        item,
        completed,
        from,
        to,
        textFrom: line.from,
        textLength: line.length,
        insertAt: target ? target.from : last.to,
        insert: target ? line.text + '\n' : '\n' + line.text,
        offset: target ? 0 : 1,
    }
}

/**
 * Both completed and reopened tasks go right before the first completed task:
 * completed ones end up at the top of the completed tasks, so that the most recently completed one comes first,
 * and reopened ones at the bottom of the open tasks. This is the same order as when loading the page.
 */
function targetFor(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line, item: Item): Line | null {
    const { start, last } = blockOf(state, line)

    const target = itemLines(state, markers, 'task', start.number, last.number)
        .find(({ item: sibling }) => ! isSameItem(sibling, item) && sibling.completed)

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

    // Projects stay where they are, only tasks move between the open and completed ones
    const move = (item.type === 'task' ? moveTo(state, line, item, completed, targetFor(state, markers, line, item)) : null)
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

        if (! (isTask(line) || isText(line)) || (word.from > line.from && ! /\s/.test(before))) {
            return null
        }

        const writtenTags: string[] = []

        for (let number = 1; number <= context.state.doc.lines; number++) {
            const current = context.state.doc.line(number)

            if (! isTask(current) && ! isText(current)) {
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

/**
 * Deleting a selection that spans multiple lines asks for confirmation first, so that projects and tasks aren't removed by accident.
 * Returning `true` stops the deletion, returning `false` lets it go ahead as usual.
 */
function confirmBulkDelete(view: EditorView): boolean {
    const { doc } = view.state
    const ranges = view.state.selection.ranges.filter((range) => doc.lineAt(range.from).number !== doc.lineAt(range.to).number)

    if (ranges.length === 0) {
        return false
    }

    let projects = 0
    let tasks = 0

    for (const range of ranges) {
        for (let number = doc.lineAt(range.from).number; number <= doc.lineAt(range.to).number; number++) {
            const line = doc.line(number)

            // A line only counts as deleted when its first character, its `#` or `-`, is removed
            const keepsFirstCharacter = range.from > line.from || (range.to === line.from && line.length > 0)

            if (keepsFirstCharacter) {
                continue
            }

            if (isProject(line)) {
                projects++
            } else if (isTask(line)) {
                tasks++
            }
        }
    }

    const counts = [
        projects > 0 ? `${projects} ${projects === 1 ? 'project' : 'projects'}` : null,
        tasks > 0 ? `${tasks} ${tasks === 1 ? 'task' : 'tasks'}` : null,
    ].filter((count) => count !== null)

    const message = counts.length > 0
        ? `This deletes ${counts.join(' and ')}. Are you sure?`
        : 'This deletes multiple lines. Are you sure?'

    return ! window.confirm(`${message}\n\nHold ${modifierKey} while deleting to skip this question.`)
}

const modifierKey = /Mac|iPhone|iPad/.test(navigator.platform) ? '⌘' : 'Ctrl'

/**
 * Holding Cmd (or Ctrl) while pressing Backspace or Delete deletes a selection right away, without asking for confirmation.
 * Without a selection, it does what it usually does, like deleting until the start or end of the line.
 */
function deleteSelectionWithoutConfirming(view: EditorView): boolean {
    if (view.state.selection.ranges.every((range) => range.empty)) {
        return false
    }

    view.dispatch(view.state.update(view.state.replaceSelection(''), { scrollIntoView: true, userEvent: 'delete.selection' }))

    return true
}

type Known = Record<ItemType, Set<number>>

type Payload = {
    projects: {
        key: string
        id: number | null
        name: string
        tasks: { key: string, id: number | null, name: string, kind: 'task' | 'text' }[]
    }[]
    deleted: Record<'projects' | 'tasks', number[]>
    // Only when everything is shown, the order of the tasks can be saved
    ordered: boolean
}

/**
 * Collects all projects with their tasks and text in the document, ready to be saved.
 * Lines that aren't tracked yet get a new item, which are returned so that they can be tracked from now on.
 * Items that were known before but can't be found anymore, are reported as deleted.
 * Tasks and text above the first project don't belong to any project, so they aren't saved.
 */
function snapshot(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, known: Known, createKey: () => string): { payload: Payload, added: Range<ItemMarker>[] } {
    const payload: Payload = { projects: [], deleted: { projects: [], tasks: [] }, ordered: activeFilters.length === 0 }
    const added: Range<ItemMarker>[] = []
    const present: Known = { project: new Set(), task: new Set(), text: new Set() }

    for (let number = 1; number <= state.doc.lines; number++) {
        const line = state.doc.line(number)
        const type = typeOf(line)
        const name = type === 'text' ? line.text.trimEnd() : line.text.slice(2).trim()
        const project = payload.projects.at(-1)

        if (type === null || name === '' || (type !== 'project' && ! project)) {
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
            project?.tasks.push({ key: item.key, id: item.id, name, kind: type })
        }
    }

    payload.deleted.projects = [...known.project].filter((id) => ! present.project.has(id))
    // Tasks and text are stored together, so they're deleted together as well
    payload.deleted.tasks = [
        ...[...known.task].filter((id) => ! present.task.has(id)),
        ...[...known.text].filter((id) => ! present.text.has(id)),
    ]

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
    // Looks like the checkbox next to it
    '.cm-collapse': {
        position: 'absolute',
        top: '50%',
        left: '-3.375rem',
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
        color: 'var(--color-gray-400)',
        fontSize: '0.625rem',
        fontWeight: 'normal',
        lineHeight: '1',
        cursor: 'pointer',
        opacity: '0',
    },
    '.cm-line:hover .cm-collapse, .cm-collapse-collapsed': {
        opacity: '1',
    },
    '.cm-collapse:hover': {
        borderColor: 'var(--color-gray-900)',
        color: 'var(--color-gray-900)',
    },
    '.cm-drag': {
        position: 'absolute',
        top: '50%',
        left: '-3.375rem',
        transform: 'translateY(-50%)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        width: '1.125rem',
        height: '1.125rem',
        color: 'var(--color-gray-400)',
        cursor: 'grab',
        opacity: '0',
    },
    '.cm-drag svg': {
        display: 'block',
        width: '0.625rem',
        height: '1rem',
    },
    '.cm-line:hover .cm-drag': {
        opacity: '1',
    },
    '.cm-drag:hover': {
        color: 'var(--color-gray-900)',
    },
    '.cm-dragged': {
        opacity: '0.4',
    },
    '.cm-archive': {
        position: 'absolute',
        top: '50%',
        left: 'calc(100% + 1rem)',
        transform: 'translateY(-50%)',
        padding: '0',
        color: 'var(--color-gray-400)',
        fontSize: '0.75rem',
        fontWeight: 'normal',
        lineHeight: '1',
        whiteSpace: 'nowrap',
        cursor: 'pointer',
        opacity: '0',
    },
    '.cm-line:hover .cm-archive': {
        opacity: '1',
    },
    '.cm-archive:hover': {
        color: 'var(--color-gray-900)',
        textDecoration: 'underline',
    },
    // CodeMirror's invisible helpers around widgets, aligned at the baseline they make lines with a collapsed project taller
    '.cm-widgetBuffer': {
        verticalAlign: 'top',
    },
    '.cm-foldPlaceholder': {
        // As an inline block, the strikethrough of a completed project's title doesn't run through it
        display: 'inline-block',
        textDecoration: 'none',
        marginLeft: '0.5rem',
        padding: '0',
        border: 'none',
        backgroundColor: 'transparent',
        color: 'var(--color-gray-400)',
        fontWeight: 'normal',
        cursor: 'pointer',
    },
    '.cm-link': {
        fontWeight: 'bold',
        cursor: 'pointer',
    },
    '.cm-link:hover': {
        textDecoration: 'underline',
    },
    '.cm-tag-sign': {
        fontWeight: 'bold',
    },
    '.cm-project-title': {
        cursor: 'pointer',
    },
    '.cm-project-title:hover, .cm-project-title-active': {
        textDecoration: 'underline',
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
        textDecoration: 'none',
        cursor: 'pointer',
        opacity: '0',
    },
    '.cm-line:hover .cm-check': {
        opacity: '1',
    },
    '.cm-check svg': {
        display: 'block',
        width: '0.75rem',
        height: '0.75rem',
    },
    '.cm-check:hover': {
        borderColor: 'var(--color-gray-900)',
        color: 'var(--color-gray-900)',
    },
    '.cm-check-completed': {
        borderColor: 'var(--color-gray-400)',
        backgroundColor: 'var(--color-gray-400)',
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
    const known: Known = { project: new Set(), task: new Set(), text: new Set() }

    for (const [lineNumber, item] of Object.entries<Omit<Item, 'key'> & { id: number }>(JSON.parse(textarea.dataset.items ?? '{}'))) {
        items[lineNumber] = { ...item, key: `${item.type}-${item.id}` }
        known[item.type].add(item.id)
    }

    let lastKey = 0
    const createKey = () => `new-${++lastKey}`

    // Only projects and tasks can be completed, text can't
    const urls: Record<Exclude<ItemType, 'text'>, { complete: string, reopen: string }> = {
        project: {
            complete: textarea.dataset.completeProjectUrl ?? '',
            reopen: textarea.dataset.reopenProjectUrl ?? '',
        },
        task: {
            complete: textarea.dataset.completeTaskUrl ?? '',
            reopen: textarea.dataset.reopenTaskUrl ?? '',
        },
    }

    const mode = (textarea.dataset.mode ?? 'workspace') as Mode
    const isArchive = mode === 'archived'

    const projectUrls = {
        archive: textarea.dataset.archiveProjectUrl ?? '',
        unarchive: textarea.dataset.unarchiveProjectUrl ?? '',
        collapse: textarea.dataset.collapseProjectUrl ?? '',
        expand: textarea.dataset.expandProjectUrl ?? '',
    }

    const filterUrl = textarea.dataset.filterUrl
    const usedTags: string[] = JSON.parse(textarea.dataset.tags ?? '[]')
    activeFilters = (textarea.dataset.filter ?? '').split(' ').filter((filter) => filter !== '')
    activeProject = textarea.dataset.project ?? null
    const saveUrl = textarea.dataset.saveUrl
    const status = document.querySelector<HTMLElement>('#todo-status')

    const markers = createItemMarkers(items)

    const toggle = async (view: EditorView, item: Item) => {
        const line = findLine(view.state, markers, item)

        if (item.type === 'project' && line && foldAt(view.state, line)) {
            return
        }

        if (item.type === 'text') {
            return
        }

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

    // Dragging a line shows where it will go, and moves it there when letting go
    const drag = (view: EditorView, event: MouseEvent, handle: HTMLElement) => {
        event.preventDefault()

        const line = view.state.doc.lineAt(view.posAtDOM(handle))
        const lines = sortableLines(view.state, markers, line)
        const lineElement = handle.closest<HTMLElement>('.cm-line')
        const content = view.contentDOM.getBoundingClientRect()
        const indicator = document.createElement('div')
        let slot = lines.findIndex((other) => other.number === line.number)

        Object.assign(indicator.style, {
            position: 'fixed',
            left: `${content.left}px`,
            width: `${content.width}px`,
            height: '2px',
            marginTop: '-1px',
            backgroundColor: 'var(--color-gray-900)',
            pointerEvents: 'none',
            zIndex: '50',
        })

        document.body.append(indicator)
        document.body.style.cursor = 'grabbing'
        lineElement?.classList.add('cm-dragged')

        const move = (event: MouseEvent) => {
            const blocks = lines.map((other) => view.lineBlockAt(other.from))
            const found = blocks.findIndex((block) => event.clientY < view.documentTop + block.top + block.height / 2)

            slot = found === -1 ? lines.length : found

            const block = slot < blocks.length ? blocks[slot] : blocks[blocks.length - 1]

            indicator.style.top = `${view.documentTop + (slot < blocks.length ? block.top : block.bottom)}px`
        }

        const drop = () => {
            window.removeEventListener('mousemove', move)
            window.removeEventListener('mouseup', drop)
            indicator.remove()
            document.body.style.cursor = ''
            lineElement?.classList.remove('cm-dragged')

            const transaction = reorder(view.state, markers, line, lines, slot)

            if (transaction) {
                view.dispatch(transaction)
            }
        }

        move(event)
        window.addEventListener('mousemove', move)
        window.addEventListener('mouseup', drop)
    }

    const collapse = (view: EditorView, item: Item) => {
        const line = findLine(view.state, markers, item)
        const fold = line ? projectFold(view.state, line) : null

        if (! line || ! fold) {
            return
        }

        const folded = foldAt(view.state, line)
        const head = view.state.selection.main.head

        // The title should stay exactly where it is on screen, also when the page becomes shorter by collapsing
        const top = view.coordsAtPos(line.from)?.top

        view.dispatch({
            effects: folded ? unfoldEffect.of(folded) : foldEffect.of(fold),
            // A cursor within the tasks that are being hidden moves to the end of the title
            selection: ! folded && head > fold.from && head <= fold.to ? { anchor: fold.from } : undefined,
        })

        const topAfter = view.coordsAtPos(line.from)?.top

        if (top !== undefined && topAfter !== undefined && topAfter !== top) {
            window.scrollBy(0, topAfter - top)
        }
    }

    // Collapsing or expanding a project is saved, also when it's expanded by clicking its `…`
    const saveFolds = (update: ViewUpdate) => {
        for (const transaction of update.transactions) {
            if (transaction.annotation(restoringFolds)) {
                continue
            }

            for (const effect of transaction.effects) {
                if (! effect.is(foldEffect) && ! effect.is(unfoldEffect)) {
                    continue
                }

                const item = itemOn(update.state, markers, update.state.doc.lineAt(effect.value.from))

                if (item?.type !== 'project' || item.id === null) {
                    continue
                }

                const url = effect.is(foldEffect) ? projectUrls.collapse : projectUrls.expand

                fetch(url.replace('__ID__', String(item.id)), { method: 'POST' })
            }
        }
    }

    const archive = async (view: EditorView, item: Item) => {
        const url = isArchive ? projectUrls.unarchive : projectUrls.archive
        const response = await fetch(url.replace('__ID__', String(item.id)), { method: 'POST' })

        if (! response.ok) {
            alert(`Could not ${isArchive ? 'unarchive' : 'archive'} this project`)

            return
        }

        const line = findLine(view.state, markers, item)

        if (! line) {
            return
        }

        // The project and its tasks are forgotten, so that saving doesn't think they were deleted
        const { last } = blockOf(view.state, line)

        for (const type of ['task', 'text'] as const) {
            for (const { item: task } of itemLines(view.state, markers, type, line.number + 1, last.number)) {
                if (task.id !== null) {
                    known[type].delete(task.id)
                }
            }
        }

        if (item.id !== null) {
            known.project.delete(item.id)
        }

        view.dispatch({
            changes: projectRange(view.state, line),
            annotations: Transaction.addToHistory.of(false),
        })
    }

    // The cursor position is remembered per workspace and filter, so that refreshing the page continues where you left off
    const cursorKey = `todo-cursor:${mode}:${textarea.dataset.workspaceId ?? ''}:${window.location.search}`
    const storedCursor = Number(window.localStorage.getItem(cursorKey))
    const cursor = Number.isInteger(storedCursor) ? Math.min(Math.max(storedCursor, 0), textarea.value.length) : 0

    const view = new EditorView({
        doc: textarea.value,
        selection: { anchor: cursor },
        extensions: [
            history(),
            keymap.of([
                { key: 'Enter', run: continueTask },
                { key: 'Backspace', run: confirmBulkDelete },
                { key: 'Delete', run: confirmBulkDelete },
                { key: 'Mod-Backspace', run: deleteSelectionWithoutConfirming },
                { key: 'Mod-Delete', run: deleteSelectionWithoutConfirming },
                ...defaultKeymap,
                ...historyKeymap,
                indentWithTab,
            ]),
            placeholder(textarea.placeholder),
            EditorView.lineWrapping,
            autocompletion({ override: [tagCompletions(usedTags)], icons: false }),
            EditorState.readOnly.of(mode !== 'workspace'),
            EditorView.editable.of(mode === 'workspace'),
            codeFolding({
                placeholderDOM(_view, onclick) {
                    const placeholder = document.createElement('span')

                    placeholder.className = 'cm-foldPlaceholder'
                    placeholder.title = 'Expand project'
                    placeholder.textContent = '…'
                    placeholder.addEventListener('click', onclick)

                    return placeholder
                },
            }),
            markers,
            itemDecorations(markers, { drag, toggle, collapse, archive, mode }),
            syntax,
            theme,
            EditorView.domEventHandlers({
                mousedown(event) {
                    // Clicking a link opens it in a new tab
                    const href = (event.target as HTMLElement).closest<HTMLElement>('.cm-link')?.dataset.href

                    if (href) {
                        event.preventDefault()
                        window.open(href, '_blank', 'noopener')

                        return true
                    }

                    if (! filterUrl) {
                        return false
                    }

                    // Clicking a project's title only shows that project, clicking it again shows all projects again
                    const project = (event.target as HTMLElement).closest<HTMLElement>('.cm-project-title')?.dataset.project

                    if (project) {
                        event.preventDefault()
                        window.location.href = filterHref(filterUrl, activeFilters, isActiveProject(project) ? null : project)

                        return true
                    }

                    const tag = (event.target as HTMLElement).closest<HTMLElement>('.cm-tag')?.dataset.tag

                    if (! tag) {
                        return false
                    }

                    event.preventDefault()
                    // Clicking a tag adds it to the filter, clicking a tag that's already being filtered on removes it again
                    const filters = isActiveFilter(tag)
                        ? activeFilters.filter((filter) => filter.toLowerCase() !== tag.toLowerCase())
                        : [...activeFilters, tag]

                    window.location.href = filterHref(filterUrl, filters, activeProject)

                    return true
                },
            }),
            EditorView.updateListener.of((update) => {
                saveFolds(update)

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
                project.tasks.forEach((task) => types.set(task.key, task.kind))
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

    const folds = []

    for (let number = 1; number <= view.state.doc.lines; number++) {
        const line = view.state.doc.line(number)
        const item = itemOn(view.state, markers, line)
        const fold = item?.collapsed && mode === 'workspace' ? projectFold(view.state, line) : null

        if (fold) {
            folds.push(foldEffect.of(fold))
        }
    }

    view.dispatch({ effects: folds, annotations: restoringFolds.of(true) })
    view.focus()
    view.dispatch({ effects: EditorView.scrollIntoView(cursor, { y: 'center' }) })
}
