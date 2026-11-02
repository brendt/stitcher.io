import { acceptCompletion, autocompletion, startCompletion } from '@codemirror/autocomplete'
import { codeFolding, foldedRanges, foldEffect, foldState, unfoldEffect } from '@codemirror/language'
import type { CompletionContext, CompletionResult } from '@codemirror/autocomplete'
import { defaultKeymap, history, historyKeymap, indentLess, indentMore, indentWithTab } from '@codemirror/commands'
import { Annotation, ChangeSet, Compartment, EditorState, MapMode, Prec, RangeSet, RangeSetBuilder, RangeValue, StateEffect, StateField, Transaction } from '@codemirror/state'
import type { Line, Range, Text, TransactionSpec } from '@codemirror/state'
import { Decoration, EditorView, keymap, placeholder, ViewPlugin, WidgetType } from '@codemirror/view'
import type { DecorationSet, ViewUpdate } from '@codemirror/view'

// Text is any other line within a project, which is saved but can't be completed
type ItemType = 'project' | 'task' | 'text'

type Item = {
    // A group is the title of tasks that aren't grouped by project, like the dates on the Soon page, it's never saved
    type: ItemType | 'group'
    // `null` until a newly written project or task has been saved
    id: number | null
    // Only for projects, whether it was collapsed when loading the page
    collapsed?: boolean
    // Part of the virtual project at the top, like "Today", which only shows tasks of other projects again
    virtual?: boolean
    // For tasks in the virtual project or on the Soon page, the name of the project they belong to, which is shown in front of them
    project?: string
    // Only for groups, the background they're shown with, if any
    shade?: 'light' | 'dark'
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
const taskLine = Decoration.line({ class: 'cm-task' })
const subtaskLine = Decoration.line({ class: 'cm-task cm-subtask' })
const completedLine = Decoration.line({ class: 'cm-completed' })
const mutedLine = Decoration.line({ class: 'cm-muted' })
const virtualLine = Decoration.line({ class: 'cm-virtual' })
// The title of a group that isn't a project, like the dates on the Soon page
const groupLine = Decoration.line({ class: 'cm-group' })
const shadeLines = {
    light: Decoration.line({ class: 'cm-shade-light' }),
    dark: Decoration.line({ class: 'cm-shade-dark' }),
}
const virtualSectionLine = Decoration.line({ class: 'cm-virtual-section' })
const virtualSectionFirstLine = Decoration.line({ class: 'cm-virtual-section cm-virtual-first' })
const virtualSectionLastLine = Decoration.line({ class: 'cm-virtual-section cm-virtual-last' })

function isProject(line: Line): boolean {
    return line.text.startsWith('# ')
}

// A subtask is indented below the task it belongs to, by two spaces for every level.
// Keep this in sync with `TodoMarkdown::INDENT`.
const INDENT = '  '
const TASK_PREFIX = /^[ \t]*- /

function isTask(line: Line): boolean {
    return TASK_PREFIX.test(line.text)
}

function indentOf(line: Line): number {
    return line.text.length - line.text.trimStart().length
}

// What's written after a task's indentation and `- `
function taskText(line: Line): string {
    return line.text.replace(TASK_PREFIX, '')
}

/**
 * A subtask belongs to the closest task above it that's indented less, with only other tasks and blank lines in between.
 * Text and tasks that aren't indented don't belong to any task.
 */
function parentOf(state: EditorState, line: Line): Line | null {
    const indent = indentOf(line)

    if (! isTask(line) || indent === 0) {
        return null
    }

    for (let number = line.number - 1; number >= 1; number--) {
        const current = state.doc.line(number)

        if (current.text.trim() === '') {
            continue
        }

        if (! isTask(current)) {
            return null
        }

        if (indentOf(current) < indent) {
            return current
        }
    }

    return null
}

/**
 * The last line of a task together with its subtasks, and theirs, which move along with it. Without subtasks, that's the task itself.
 */
function lastDescendant(state: EditorState, line: Line): Line {
    let last = line

    if (! isTask(line)) {
        return last
    }

    for (let number = line.number + 1; number <= state.doc.lines; number++) {
        const current = state.doc.line(number)

        if (current.text.trim() === '') {
            continue
        }

        if (! isTask(current) || indentOf(current) <= indentOf(line)) {
            break
        }

        last = current
    }

    return last
}

/**
 * The tasks and text that belong to the same task as the given line, including the line itself.
 * Tasks and text that don't belong to any task are siblings within their project.
 */
function siblingsOf(state: EditorState, line: Line): Line[] {
    const parent = parentOf(state, line)
    const { start, last } = blockOf(state, line)
    const from = (parent ?? start).number + 1
    const to = parent ? lastDescendant(state, parent).number : last.number
    const lines: Line[] = []

    for (let number = from; number <= to; number++) {
        const current = state.doc.line(number)
        const type = typeOf(current)

        if ((type === 'task' || type === 'text') && parentOf(state, current)?.number === parent?.number) {
            lines.push(current)
        }
    }

    return lines
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
                        class: isActiveProject(name) ? 'cm-project-title cm-filterable cm-project-title-active' : 'cm-project-title cm-filterable',
                        attributes: { 'data-project': name },
                    }).range(start, start + name.length))
                }
            }

            if (isTask(line)) {
                decorations.push((indentOf(line) > 0 ? subtaskLine : taskLine).range(line.from))
            }

            if (isTask(line) || isText(line)) {
                for (const match of line.text.matchAll(TAG)) {
                    const start = line.from + match.index + match[1].length
                    const tag = match[2]

                    decorations.push((tag.length === 1 ? tagSign : Decoration.mark({
                        class: isActiveFilter(tag) ? 'cm-tag cm-filterable cm-tag-active' : 'cm-tag cm-filterable',
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
    // The item whose `completed` changes, if any
    item: Item | null
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

            if (transaction.docChanged) {
                markers = keepAtLineStart(markers, transaction.state)
            }

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

/**
 * Indenting a line inserts spaces right at its start, which would end up before the line's marker, see `ItemMarker`.
 * The marker goes back to the start of the line then, so that it's still the same task, but now as a subtask.
 */
function keepAtLineStart(markers: RangeSet<ItemMarker>, state: EditorState): RangeSet<ItemMarker> {
    // The starts of lines that have a marker already, and the markers that go back to the start of their line, by where they are now
    const taken = new Set<number>()
    const moved = new Map<number, Range<ItemMarker>>()

    markers.between(0, state.doc.length, (from, _to, marker) => {
        const line = state.doc.lineAt(from)

        if (from === line.from) {
            taken.add(line.from)
        } else if (state.sliceDoc(line.from, from).trim() === '') {
            moved.set(from, marker.range(line.from))
        }
    })

    for (const [from, range] of moved) {
        if (taken.has(range.from)) {
            moved.delete(from)
        }

        taken.add(range.from)
    }

    if (moved.size === 0) {
        return markers
    }

    return markers.update({ filter: (from) => ! moved.has(from), add: [...moved.values()], sort: true })
}

function moveMarkers(markers: RangeSet<ItemMarker>, transaction: Transaction): RangeSet<ItemMarker> {
    const moves = transaction.effects.filter((effect) => effect.is(moveItem)).map((effect) => effect.value as Move)

    if (moves.length === 0) {
        return markers.map(transaction.changes)
    }

    const isMoved = (from: number) => moves.some((move) => move.insert !== '' && from >= move.textFrom && from < move.textFrom + move.textLength)
    const moveOf = (item: Item) => moves.find((move) => move.item !== null && isSameItem(move.item, item))
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
        readonly isProject: boolean,
    ) {
        super()
    }

    eq(other: DragHandle): boolean {
        return other.isProject === this.isProject
    }

    toDOM(view: EditorView): HTMLElement {
        const handle = document.createElement('span')

        handle.className = this.isProject ? 'cm-drag cm-drag-project' : 'cm-drag'
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
 * Whether a line is part of the virtual project at the top, like "Today", which only shows tasks of other projects again.
 * It can't be moved, and isn't saved. Only the text of its tasks can be edited, which edits them in their own project, see `mirrorVirtualEdits`.
 */
function isVirtual(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line): boolean {
    const { start } = blockOf(state, line)

    return isProject(start) && itemOn(state, markers, start)?.virtual === true
}

/**
 * Where the virtual project is in the document, including the line break after it, so that nothing can be added at its end.
 */
function virtualRange(state: EditorState, markers: StateField<RangeSet<ItemMarker>>): { from: number, to: number } | null {
    for (const line of projectLines(state, markers, true)) {
        if (isVirtual(state, markers, line)) {
            const { last } = blockOf(state, line)

            return { from: line.from, to: Math.min(last.to + 1, state.doc.length) }
        }
    }

    return null
}

/**
 * Where the text of a task in the virtual project is, after the name of its own project in front of it.
 */
function virtualTaskText(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line): { from: number, to: number } | null {
    const item = itemOn(state, markers, line)

    if (! item?.virtual || item.type !== 'task' || ! item.project) {
        return null
    }

    const prefix = `- ${item.project}: `

    return line.text.startsWith(prefix) ? { from: line.from + prefix.length, to: line.to } : null
}

/**
 * The line of a task in its own project, for a task that's shown in the virtual project as well.
 */
function ownLineOf(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, item: Item): Line | null {
    for (let cursor = state.field(markers).iter(); cursor.value; cursor.next()) {
        const other = cursor.value.item

        if (! other.virtual && other.type === item.type && other.id === item.id) {
            const line = state.doc.lineAt(cursor.from)

            return itemOn(state, markers, line) === other ? line : null
        }
    }

    return null
}

/**
 * Open tasks and text within a project can be put in another order. Completed tasks stay below them, ordered by when they were completed.
 * With tags filtered on, not all tasks are shown, so their order can't be saved.
 */
function isSortable(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line): boolean {
    if (activeFilters.length > 0) {
        return false
    }

    const type = typeOf(line)

    if (type !== 'task' && type !== 'text') {
        return false
    }

    return isProject(blockOf(state, line).start) && ! isVirtual(state, markers, line) && ! itemOn(state, markers, line)?.completed
}

/**
 * Projects can be put in another order, as long as all of them are shown, so that their order can be saved.
 */
function isSortableProject(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line): boolean {
    return isProject(line) && activeFilters.length === 0 && activeProject === null && ! isVirtual(state, markers, line)
}

/**
 * The titles of all projects, leaving out the virtual one unless asked for.
 */
function projectLines(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, includeVirtual: boolean = false): Line[] {
    const lines: Line[] = []

    for (let number = 1; number <= state.doc.lines; number++) {
        const line = state.doc.line(number)

        if (isProject(line) && (includeVirtual || itemOn(state, markers, line)?.virtual !== true)) {
            lines.push(line)
        }
    }

    return lines
}

/**
 * Moves a project with all of its tasks and text to another slot between `projects`,
 * like `reorder` does for a single line. A collapsed project stays collapsed.
 */
function reorderProject(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line, projects: Line[], slot: number): Transaction[] {
    const current = projects.findIndex((other) => other.number === line.number)

    if (slot === current || slot === current + 1) {
        return []
    }

    const { last } = blockOf(state, line)
    const { from, to } = projectRange(state, line)
    const text = state.doc.sliceString(line.from, last.to)
    const isBefore = slot < projects.length
    const insertAt = isBefore ? projects[slot].from : lastFilledLine(state).to
    const separator = '\n'.repeat(BLANK_LINES_ABOVE_PROJECTS + 1)
    const insert = isBefore ? text + separator : separator + text
    const offset = isBefore ? 0 : separator.length
    const item = itemOn(state, markers, line)
    const fold = foldAt(state, line)

    const transaction = state.update({
        changes: [{ from, to }, { from: insertAt, insert }],
        effects: moveItem.of({ item, completed: item?.completed ?? false, from, to, textFrom: line.from, textLength: text.length, insertAt, insert, offset }),
        annotations: Transaction.addToHistory.of(false),
    })

    if (! fold) {
        return [transaction]
    }

    const moved = transaction.state.doc.lineAt(transaction.changes.mapPos(insertAt, -1) + offset)
    const refold = projectFold(transaction.state, moved)

    return refold
        ? [transaction, transaction.state.update({ effects: foldEffect.of(refold), annotations: restoringFolds.of(true) })]
        : [transaction]
}

/**
 * The lines a line can be dragged between: the other open tasks and text in its project, or of the task it's a subtask of.
 */
function sortableLines(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line): Line[] {
    return siblingsOf(state, line).filter((sibling) => isSortable(state, markers, sibling))
}

/**
 * Moves a line, together with its subtasks, to another slot between `lines`: slot 0 is before the first line, the last slot is after the last line.
 * The items on the lines keep following them, so that they stay the same tasks or text.
 */
function reorder(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line, lines: Line[], slot: number): Transaction | null {
    const current = lines.findIndex((other) => other.number === line.number)

    // The slots right before and after the line itself keep it where it is
    if (slot === current || slot === current + 1) {
        return null
    }

    const last = lastDescendant(state, line)
    const text = state.sliceDoc(line.from, last.to)
    const isBefore = slot < lines.length
    const target = isBefore ? lines[slot] : lastDescendant(state, lines[lines.length - 1])
    const [from, to] = last.number < state.doc.lines ? [line.from, last.to + 1] : [line.from - 1, last.to]
    const insertAt = isBefore ? target.from : target.to
    const insert = isBefore ? text + '\n' : '\n' + text
    const item = itemOn(state, markers, line)

    return state.update({
        changes: [{ from, to }, { from: insertAt, insert }],
        effects: moveItem.of({
            item,
            completed: item?.completed ?? false,
            from,
            to,
            textFrom: line.from,
            textLength: text.length,
            insertAt,
            insert,
            offset: isBefore ? 0 : 1,
        }),
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

/**
 * The title of a group that isn't a project, like the dates on the Soon page, which `itemOn` leaves out because it looks like a project.
 */
function groupOn(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line): Item | null {
    let found: Item | null = null

    if (! isProject(line)) {
        return null
    }

    state.field(markers).between(line.from, line.from, (from, _to, marker) => {
        if (from === line.from && marker.item.type === 'group') {
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
            const item = itemOn(state, markers, line) ?? groupOn(state, markers, line)

            // The virtual project gets a background of its own, from its title until its last task, and so do some groups
            const { start: blockStart, last } = blockOf(state, line)
            const shade = groupOn(state, markers, blockStart)?.shade

            if ((isVirtual(state, markers, line) || shade) && line.number <= last.number) {
                decorations.push((line.number === blockStart.number ? virtualSectionFirstLine : (line.number === last.number ? virtualSectionLastLine : virtualSectionLine)).range(line.from))

                if (shade) {
                    decorations.push(shadeLines[shade].range(line.from))
                }
            }

            // The checkbox and drag handle of a subtask go right before its `- `, see `.cm-subtask` in the theme
            const start = isTask(line) ? line.from + indentOf(line) : line.from

            if (actions.mode === 'workspace' && (isSortable(state, markers, line) || isSortableProject(state, markers, line))) {
                decorations.push(Decoration.widget({ widget: new DragHandle(actions.drag, isProject(line)), side: -1 }).range(start))
            }

            if (! item) {
                continue
            }

            if (item.type === 'group') {
                decorations.push(groupLine.range(line.from))

                continue
            }

            // A collapsed project can't be completed, its tasks need to be visible for that
            const isCollapsedProject = item.type === 'project' && foldAt(state, line) !== null

            if (item.id !== null && item.type !== 'text' && actions.mode !== 'archived' && ! isCollapsedProject) {
                decorations.push(Decoration.widget({ widget: new CheckButton(item, actions.toggle), side: -1 }).range(start))
            }

            if (item.virtual && item.type === 'project') {
                decorations.push(virtualLine.range(line.from))
            }

            // In the virtual project and on the Soon page, the name of a task's own project can be clicked to only show that project
            if (item.type === 'task' && item.project && line.text.startsWith(`- ${item.project}: `)) {
                decorations.push(Decoration.mark({
                    class: 'cm-project-title cm-project-name cm-filterable',
                    attributes: { 'data-project': item.project },
                }).range(line.from + 2, line.from + 2 + item.project.length))
            }

            if (item.id !== null && item.type === 'project') {
                // Archived projects are always shown completely
                if (actions.mode === 'workspace' && projectFold(state, line)) {
                    decorations.push(Decoration.widget({
                        widget: new CollapseButton(item, foldAt(state, line) !== null, actions.collapse),
                        side: -1,
                    }).range(line.from))
                }

                if (actions.mode === 'workspace' || actions.mode === 'archived') {
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

            // The tasks and text of a completed project are muted, like the subtasks of a completed task
            if (item.type === 'project' || item.type === 'task') {
                const last = item.type === 'project' ? blockOf(state, line).last : lastDescendant(state, line)

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

// Hides or shows completed tasks and projects again
const hideCompleted = StateEffect.define<boolean>()

const hiddenLines = Decoration.replace({ block: true })

/**
 * The parts of the document that are hidden while completed items are hidden:
 * completed tasks with their subtasks, and completed projects with their tasks and text.
 * Blank lines right above them are hidden as well, so that there's no more space between what's left than usual.
 * A project at the top has nothing above it, so the blank lines below it are hidden instead.
 */
function completedRanges(state: EditorState, markers: StateField<RangeSet<ItemMarker>>): { from: number, to: number }[] {
    const ranges: { from: number, to: number }[] = []
    const { doc } = state

    for (let number = 1; number <= doc.lines; number++) {
        const line = doc.line(number)
        const item = itemOn(state, markers, line)

        if (! item?.completed || item.type === 'text' || item.virtual) {
            continue
        }

        let first = line
        let last = item.type === 'project' ? blockOf(state, line).last : lastDescendant(state, line)

        while (first.number > 1 && doc.line(first.number - 1).text.trim() === '') {
            first = doc.line(first.number - 1)
        }

        if (first.number === 1) {
            while (last.number < doc.lines && doc.line(last.number + 1).text.trim() === '') {
                last = doc.line(last.number + 1)
            }
        }

        const previous = ranges.at(-1)

        // Completed tasks right below each other are hidden as a whole
        if (previous && previous.to + 1 >= first.from) {
            previous.to = last.to
        } else {
            ranges.push({ from: first.from, to: last.to })
        }

        number = last.number
    }

    return ranges
}

/**
 * Completed tasks and projects are only hidden from view, they're still part of the document, so they're saved like always.
 * The cursor skips over them, so that they can't be changed without seeing them.
 */
function createCompletedHiding(markers: StateField<RangeSet<ItemMarker>>, isHidden: boolean) {
    const build = (state: EditorState, hidden: boolean) => hidden
        ? Decoration.set(completedRanges(state, markers).map(({ from, to }) => hiddenLines.range(from, to)))
        : Decoration.none

    return StateField.define<{ hidden: boolean, decorations: DecorationSet }>({
        create: (state) => ({ hidden: isHidden, decorations: build(state, isHidden) }),

        update(value, transaction) {
            const effect = transaction.effects.find((effect) => effect.is(hideCompleted))
            const hidden = effect ? effect.value as boolean : value.hidden

            // Completing a task changes its marker without changing the document, so any effect could change what's hidden
            if (! transaction.docChanged && transaction.effects.length === 0) {
                return value
            }

            return { hidden, decorations: build(transaction.state, hidden) }
        },

        provide: (field) => [
            EditorView.decorations.from(field, (value) => value.decorations),
            EditorView.atomicRanges.of((view) => view.state.field(field).decorations),
        ],
    })
}

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
 * Cuts a task's line, together with its subtasks, and places it right before `target`,
 * or at the bottom of its project, or of the task it's a subtask of, without a target.
 */
function moveTo(state: EditorState, line: Line, item: Item, completed: boolean, target: Line | null): Move | null {
    const parent = parentOf(state, line)
    const bottom = parent ? lastDescendant(state, parent) : blockOf(state, line).last
    const last = lastDescendant(state, line)

    const isInPlace = target
        ? target.number === last.number + 1
        : bottom.number <= last.number

    if (isInPlace) {
        return null
    }

    const text = state.sliceDoc(line.from, last.to)
    const [from, to] = last.number < state.doc.lines ? [line.from, last.to + 1] : [line.from - 1, last.to]

    return {
        item,
        completed,
        from,
        to,
        textFrom: line.from,
        textLength: text.length,
        insertAt: target ? target.from : bottom.to,
        insert: target ? text + '\n' : '\n' + text,
        offset: target ? 0 : 1,
    }
}

/**
 * Both completed and reopened tasks go right before the first completed task among their siblings:
 * completed ones end up at the top of the completed tasks, so that the most recently completed one comes first,
 * and reopened ones at the bottom of the open tasks. This is the same order as when loading the page.
 */
function targetFor(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, line: Line, item: Item): Line | null {
    return siblingsOf(state, line).find((sibling) => {
        const other = itemOn(state, markers, sibling)

        return other?.type === 'task' && ! isSameItem(other, item) && other.completed
    }) ?? null
}

/**
 * Adds a task at the bottom of a project's open tasks, right before the first completed one, where reopened tasks go as well.
 */
function addTask(view: EditorView, markers: StateField<RangeSet<ItemMarker>>, project: Line, text: string): void {
    const { last } = blockOf(view.state, project)
    const target = itemLines(view.state, markers, 'task', project.number, last.number)
        .find(({ line, item }) => item.completed && parentOf(view.state, line) === null)?.line
    const task = `- ${text}`

    view.dispatch({
        changes: target ? { from: target.from, insert: task + '\n' } : { from: last.to, insert: '\n' + task },
        effects: EditorView.scrollIntoView(target ? target.from : last.to + 1, { y: 'center' }),
        userEvent: 'input',
    })
}

/**
 * `#` and `@` are hard to find on a phone's keyboard, so they can be inserted with a button,
 * followed by `#today`, `#tomorrow`, and `#nextweek`, which add or remove their date on the cursor's line with a single tap.
 * `#today+` is inserted as it is instead, so that the number of days can be typed after it, see `RELATIVE_DATES`.
 * Phones don't have a Tab key either, so with `canIndent`, there are buttons in front of them to make a task a subtask and back.
 * The editor is passed lazily, so that the buttons can be set up before it.
 */
function tagShortcuts(container: HTMLElement, editor: () => EditorView, canIndent: boolean = false): { update: () => void, refreshDates: () => void } {
    // On Sundays, tomorrow and next week are the same day, so the dates aren't unique
    let dateChips: { date: string, button: HTMLButtonElement }[] = []

    const chip = (label: string, onTap: () => void, ariaLabel: string | null = null) => {
        const button = document.createElement('button')

        button.type = 'button'
        button.textContent = label

        if (ariaLabel !== null) {
            button.setAttribute('aria-label', ariaLabel)
        }

        button.className = 'shrink-0 select-none px-3 py-1 rounded-full border-[1.5px] border-gray-300 bg-white text-gray-600 whitespace-nowrap cursor-pointer aria-pressed:border-dotted aria-pressed:border-gray-900 aria-pressed:bg-gray-100 aria-pressed:text-gray-900'
        // Keeps the editor focused, so that the keyboard stays open
        button.addEventListener('mousedown', (event) => event.preventDefault())
        button.addEventListener('click', () => {
            onTap()
            editor().focus()
        })

        return button
    }

    // The ranges of a tag on the cursor's line, including one space next to it, so that removing it doesn't leave two spaces behind
    const rangesOf = (tag: string) => {
        const { state } = editor()
        const line = state.doc.lineAt(state.selection.main.head)
        const ranges: { from: number, to: number }[] = []

        for (const match of line.text.matchAll(TAG)) {
            if (match[2].toLowerCase() !== tag.toLowerCase()) {
                continue
            }

            const from = match.index + match[1].length
            const to = from + match[2].length

            ranges.push(line.text[to] === ' '
                ? { from: line.from + from, to: line.from + to + 1 }
                : { from: line.from + from - match[1].length, to: line.from + to })
        }

        return ranges
    }

    // Inserts at the cursor, with a space in front when it would otherwise be stuck to the word before it
    const insert = (text: string) => {
        const view = editor()
        const { from, to } = view.state.selection.main
        const before = view.state.sliceDoc(from - 1, from)
        const prefix = before !== '' && ! /\s/.test(before) ? ' ' : ''

        view.dispatch({
            changes: { from, to, insert: prefix + text },
            selection: { anchor: from + prefix.length + text.length },
            scrollIntoView: true,
            userEvent: 'input',
        })
    }

    // A task only gets one of these dates, so picking another one replaces it
    const toggleDate = (date: string) => {
        const isAdded = rangesOf(date).length > 0
        const ranges = [...new Set(dateChips.map(({ date }) => date))].flatMap(rangesOf)

        if (ranges.length > 0) {
            editor().dispatch({ changes: ranges, userEvent: 'delete' })
        }

        if (! isAdded) {
            insert(date + ' ')
        }
    }

    const update = () => {
        for (const { date, button } of dateChips) {
            button.setAttribute('aria-pressed', String(rangesOf(date).length > 0))
        }
    }

    if (canIndent) {
        container.append(
            chip('⇤', () => indentLess(editor()), 'Indent less'),
            chip('⇥', () => indentMore(editor()), 'Indent more'),
        )
    }

    for (const sign of ['#', '@']) {
        container.append(chip(sign, () => {
            insert(sign)
            startCompletion(editor())
        }))
    }

    const todayPlus = chip('#today+', () => insert('#today+'))

    // The dates are worked out again when the buttons are shown, so that they're right for the current day
    const refreshDates = () => {
        dateChips.forEach(({ button }) => button.remove())

        dateChips = Object.keys(DAYS_FROM_TODAY).map((tag) => {
            const date = relativeDate(tag)

            return { date, button: chip(tag, () => toggleDate(date)) }
        })

        container.append(...dateChips.map(({ button }) => button))
        // Right after `#today`
        dateChips[0].button.after(todayPlus)
        update()
    }

    return { update, refreshDates }
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
/**
 * The hashtags, mentions, and dates written in tasks and text, leaving out the one starting at `except`, which is still being written.
 */
function writtenTags(state: EditorState, except: number | null = null): string[] {
    const tags: string[] = []

    for (let number = 1; number <= state.doc.lines; number++) {
        const line = state.doc.line(number)

        if (! isTask(line) && ! isText(line)) {
            continue
        }

        for (const match of line.text.matchAll(TAG)) {
            const from = line.from + match.index + match[1].length

            if (from !== except && match[2].length > 1) {
                tags.push(match[2])
            }
        }
    }

    return tags
}

/**
 * Suggests the tags of `otherTags` as well, which are written somewhere else, like in the main editor while adding a task in a popup.
 */
function tagCompletions(usedTags: string[], otherTags: () => string[] = () => []) {
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

        const seen = new Set<string>([word.text.toLowerCase()])
        const tags = [...usedTags, ...writtenTags(context.state, word.from), ...otherTags()].filter((tag) => {
            const key = tag.toLowerCase()

            if (tag[0] !== word.text[0] || seen.has(key)) {
                return false
            }

            seen.add(key)

            return true
        })

        // `#today`, `#tomorrow`, and `#nextweek` are suggested first, and are written as their date when picked.
        // `#today+` is written as it is, so that the number of days can be typed after it, see `RELATIVE_DATES`.
        const relativeDates = word.text.startsWith('#')
            ? [
                ...Object.keys(DAYS_FROM_TODAY).map((tag) => ({ label: tag, detail: relativeDate(tag), apply: relativeDate(tag) + ' ', boost: 99 })),
                { label: '#today+', detail: 'days from today', apply: '#today+', boost: 98 },
            ]
            : []

        return {
            from: word.from,
            options: [
                ...relativeDates.filter((option) => option.label !== word.text.toLowerCase()),
                ...tags.filter((tag) => ! (tag.toLowerCase() in DAYS_FROM_TODAY)).map((tag, index) => ({ label: tag, boost: Math.max(-99, -index) })),
            ],
            validFor: TAG_WORD,
        }
    }
}

/**
 * Removes one level of indentation from a subtask, so that it belongs to the task above the one it belonged to.
 */
function outdent(view: EditorView, line: Line): void {
    view.dispatch({
        changes: { from: line.from, to: line.from + Math.min(indentOf(line), INDENT.length) },
        scrollIntoView: true,
        userEvent: 'delete.dedent',
    })
}

/**
 * Pressing Enter at the end of a new project's title starts its first task right below it.
 * Pressing Enter within a task starts a new task right away, indented as far as the task itself.
 * Pressing Enter on a subtask that's still empty indents it less.
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

    const isNewProject = isProject(line)
        && line.text.slice(2).trim() !== ''
        && selection.head === line.to
        && blockOf(state, line).last.number === line.number

    if (isNewProject) {
        view.dispatch({
            changes: { from: line.to, insert: '\n- ' },
            selection: { anchor: line.to + 3 },
            scrollIntoView: true,
            userEvent: 'input',
        })

        return true
    }

    if (! isTask(line) || selection.head < line.from + indentOf(line) + 2) {
        return false
    }

    if (line.text.trim() === '-' && indentOf(line) > 0) {
        outdent(view, line)

        return true
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
    const insert = '\n' + line.text.slice(0, indentOf(line)) + '- '

    view.dispatch({
        changes: { from: selection.head, to: selection.head + spaces, insert },
        selection: { anchor: selection.head + insert.length },
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

/**
 * Keeps the spacing the same as when loading the page: a blank line above every project,
 * a blank line between text and a task right above or below it, and no blank lines between two tasks,
 * for example after the text between them was removed.
 * Lines near the cursor are left alone until the cursor moves away, so that they don't change while typing.
 */
const BLANK_LINES_ABOVE_PROJECTS = 1

// Moves skip `normalizeSpacing`, so the spacing is fixed right after them instead
function tidy(view: EditorView): void {
    view.dispatch({ selection: view.state.selection, annotations: Transaction.addToHistory.of(false) })
}

const normalizeSpacing = EditorState.transactionFilter.of((transaction) => {
    if (! transaction.docChanged && ! transaction.selection) {
        return transaction
    }

    // Moving items keeps track of where their lines end up, which extra changes in the same transaction would throw off.
    // Their spacing is fixed by the next transaction instead.
    if (transaction.effects.some((effect) => effect.is(moveItem))) {
        return transaction
    }

    const { doc, selection } = transaction.state
    const cursorLine = doc.lineAt(selection.main.head).number
    const changes: { from: number, to?: number, insert?: string }[] = []

    for (let number = 1; number <= doc.lines; number++) {
        const line = doc.line(number)

        if (isProject(line)) {
            let blankLines = 0

            while (number - blankLines > 1 && doc.line(number - blankLines - 1).text.trim() === '') {
                blankLines++
            }

            const isAtTop = number - blankLines === 1
            const isNearCursor = cursorLine >= number - blankLines && cursorLine <= number

            if (isAtTop || isNearCursor || blankLines === BLANK_LINES_ABOVE_PROJECTS) {
                continue
            }

            changes.push(blankLines < BLANK_LINES_ABOVE_PROJECTS
                ? { from: line.from, insert: '\n'.repeat(BLANK_LINES_ABOVE_PROJECTS - blankLines) }
                : { from: doc.line(number - blankLines).from, to: doc.line(number - BLANK_LINES_ABOVE_PROJECTS).from })

            continue
        }

        if (isTask(line)) {
            let blankLines = 0

            while (number + blankLines < doc.lines && doc.line(number + blankLines + 1).text.trim() === '') {
                blankLines++
            }

            const next = number + blankLines + 1
            const isNearCursor = cursorLine > number && cursorLine < next

            if (blankLines > 0 && next <= doc.lines && isTask(doc.line(next)) && ! isNearCursor) {
                changes.push({ from: line.to, to: doc.line(number + blankLines).to })
            }

            continue
        }

        if (number === cursorLine || ! isText(line) || ! isProject(blockOf(transaction.state, line).start)) {
            continue
        }

        if (number > 1 && isTask(doc.line(number - 1))) {
            changes.push({ from: line.from, insert: '\n' })
        }

        if (number < doc.lines && isTask(doc.line(number + 1))) {
            changes.push({ from: line.to, insert: '\n' })
        }
    }

    return changes.length === 0 ? transaction : [transaction, { changes, sequential: true }]
})

/**
 * Typing `#today`, `#tomorrow`, or `#nextweek` in a task or text writes it as a date instead, like `2026-10-30`,
 * so that it can be filtered on and shows up in Soon. `#today+5` is written as the date five days from today.
 * Only lines that are being edited change, once the word is finished:
 * not while the cursor is still right after it, so that typing a longer word doesn't get replaced halfway.
 */
const RELATIVE_DATE = '#today\\+\\d{1,4}|#today|#tomorrow|#nextweek'
const RELATIVE_DATES = new RegExp(`(^|\\s)(${RELATIVE_DATE})(?=$|[\\s.,;:!?)])`, 'giu')
const RELATIVE_DATE_BEFORE_CURSOR = new RegExp(`(^|\\s)(${RELATIVE_DATE})$`, 'iu')

// The number of days from today, worked out when it's used, so that it's always right for the current day.
// The next week starts on its Monday.
// Marks a transaction in which `#today`, `#tomorrow`, or `#nextweek` was written as a date, which is saved right away
const relativeDateResolved = Annotation.define<boolean>()

const DAYS_FROM_TODAY: Record<string, () => number> = {
    '#today': () => 0,
    '#tomorrow': () => 1,
    '#nextweek': () => (8 - new Date().getDay()) % 7 || 7,
}

function relativeDate(tag: string): string {
    const offset = tag.match(/^#today\+(\d+)$/iu)

    return dateInDays(offset ? Number(offset[1]) : DAYS_FROM_TODAY[tag.toLowerCase()]())
}

function dateInDays(days: number): string {
    const date = new Date()

    date.setDate(date.getDate() + days)

    return [
        date.getFullYear(),
        String(date.getMonth() + 1).padStart(2, '0'),
        String(date.getDate()).padStart(2, '0'),
    ].join('-')
}

const replaceRelativeDates = EditorState.transactionFilter.of((transaction) => {
    if (! transaction.docChanged || transaction.isUserEvent('undo') || transaction.isUserEvent('redo')) {
        return transaction
    }

    if (transaction.effects.some((effect) => effect.is(moveItem))) {
        return transaction
    }

    const { doc, selection } = transaction.state
    const head = selection.main.head
    const lines = new Set<number>()

    transaction.changes.iterChangedRanges((_fromA, _toA, fromB, toB) => {
        for (let number = doc.lineAt(fromB).number; number <= doc.lineAt(toB).number; number++) {
            lines.add(number)
        }
    })

    const changes: { from: number, to: number, insert: string }[] = []

    for (const number of lines) {
        const line = doc.line(number)

        if (! isTask(line) && ! isText(line)) {
            continue
        }

        for (const match of line.text.matchAll(RELATIVE_DATES)) {
            const from = line.from + match.index + match[1].length
            const to = from + match[2].length

            if (to === head) {
                continue
            }

            changes.push({ from, to, insert: relativeDate(match[2]) })
        }
    }

    return changes.length === 0 ? transaction : [transaction, { changes, sequential: true, annotations: relativeDateResolved.of(true) }]
})

/**
 * Tab right after `#today`, `#today+5`, `#tomorrow`, or `#nextweek` writes the date right away, followed by a space to continue typing.
 */
function expandRelativeDate(view: EditorView): boolean {
    const selection = view.state.selection.main

    if (view.state.selection.ranges.length > 1 || ! selection.empty) {
        return false
    }

    const line = view.state.doc.lineAt(selection.head)
    const match = line.text.slice(0, selection.head - line.from).match(RELATIVE_DATE_BEFORE_CURSOR)

    if (! match || (! isTask(line) && ! isText(line))) {
        return false
    }

    const from = selection.head - match[2].length
    const date = relativeDate(match[2])

    // When there's already a space after it, the cursor moves past that one instead of adding another
    const hasSpace = view.state.sliceDoc(selection.head, selection.head + 1) === ' '
    const insert = hasSpace ? date : date + ' '

    view.dispatch({
        changes: { from, to: selection.head, insert },
        selection: { anchor: from + date.length + 1 },
        userEvent: 'input.complete',
        annotations: relativeDateResolved.of(true),
    })

    return true
}

/**
 * Backspace on a task that's still empty removes its `- ` at once, instead of only the space.
 * On a subtask that's still empty, it indents it less instead.
 */
function removeEmptyTask(view: EditorView): boolean {
    const selection = view.state.selection.main

    if (view.state.selection.ranges.length > 1 || ! selection.empty) {
        return false
    }

    const line = view.state.doc.lineAt(selection.head)

    if (! isTask(line) || taskText(line) !== '' || selection.head !== line.to) {
        return false
    }

    if (indentOf(line) > 0) {
        outdent(view, line)

        return true
    }

    view.dispatch({
        changes: { from: line.from, to: line.to },
        selection: { anchor: line.from },
        userEvent: 'delete.backward',
    })

    return true
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

/**
 * Keeps the virtual project at the top up to date with the document, the same way as `TodoMarkdown::upcoming()` does when loading the page:
 * open tasks due today or overdue, or else tomorrow, or else in the coming week, latest first, with the name of their own project.
 * Returns the changes to make when it should look different than it does now, for example after adding a task for today.
 */
function refreshUpcoming(state: EditorState, markers: StateField<RangeSet<ItemMarker>>): TransactionSpec | null {
    if (activeProject !== null) {
        return null
    }

    const current = virtualRange(state, markers)
    const currentHeading = current ? state.doc.lineAt(current.from) : null
    const currentText = currentHeading ? state.sliceDoc(currentHeading.from, blockOf(state, currentHeading).last.to) : null

    // The current virtual project is replaced as a whole, together with the blank lines after it
    const replace = currentHeading ? projectRange(state, currentHeading) : { from: 0, to: 0 }

    const { doc } = state
    const tasks: { item: Item, text: string, project: string, dates: string[] }[] = []

    for (let number = 1; number <= doc.lines; number++) {
        const line = doc.line(number)
        const item = itemOn(state, markers, line)
        const { start } = blockOf(state, line)
        const project = isProject(start) ? itemOn(state, markers, start) : null

        if (item?.type !== 'task' || item.id === null || item.completed || ! project || project.completed || project.virtual) {
            continue
        }

        const tags = [...line.text.matchAll(TAG)].map((match) => match[2])

        if (! activeFilters.every((filter) => tags.some((tag) => tag.toLowerCase() === filter.toLowerCase()))) {
            continue
        }

        tasks.push({ item, text: taskText(line), project: start.text.slice(2).trim(), dates: tags.filter((tag) => /^\d{4}-/.test(tag)) })
    }

    // Today also shows overdue tasks, which have a date before today
    for (const [title, fromDays, toDays] of [['Today', null, 0], ['Tomorrow', 1, 1], ['Soon', 0, 7]] as const) {
        const from = fromDays === null ? '' : dateInDays(fromDays)
        const to = dateInDays(toDays)

        const found = tasks
            .map((task) => ({ ...task, date: task.dates.filter((date) => date >= from && date <= to).sort()[0] }))
            .filter((task) => task.date !== undefined)
            .sort((a, b) => b.date.localeCompare(a.date))

        if (found.length === 0) {
            continue
        }

        const lines = [`# ${title}`, ...found.map((task) => `- ${task.project}: ${task.text}`)]

        if (lines.join('\n') === currentText) {
            return null
        }

        const hasProjectsBelow = replace.to < state.doc.length || (currentHeading === null && state.doc.length > 0)
        const insert = lines.join('\n') + (hasProjectsBelow ? '\n'.repeat(BLANK_LINES_ABOVE_PROJECTS + 1) : '')
        const added: Range<ItemMarker>[] = []
        let position = 0

        lines.forEach((text, index) => {
            const item: Item = index === 0
                ? { type: 'project', id: null, key: 'today-project-title', completed: false, virtual: true }
                : { ...found[index - 1].item, key: `today-task-${found[index - 1].item.id}`, virtual: true, project: found[index - 1].project }

            added.push(new ItemMarker(item).range(position))
            position += text.length + 1
        })

        return {
            changes: { from: replace.from, to: replace.to, insert },
            effects: addItems.of(added),
            annotations: Transaction.addToHistory.of(false),
        }
    }

    // Nothing is coming up anymore, so the current virtual project goes away
    return currentHeading
        ? { changes: replace, annotations: Transaction.addToHistory.of(false) }
        : null
}

/**
 * Only the text of the tasks in the virtual project can be edited, not the name of their project in front of it, nor its title.
 */
function virtualChangeFilter(markers: StateField<RangeSet<ItemMarker>>) {
    return EditorState.changeFilter.of((transaction) => {
        // Changes made by the editor itself, like bringing the virtual project up to date
        if (transaction.annotation(Transaction.addToHistory) === false) {
            return true
        }

        const { startState: state } = transaction
        const range = virtualRange(state, markers)

        if (! range) {
            return true
        }

        // Everything in between the text of the tasks can't be changed
        const suppressed = [range.from]

        for (let number = state.doc.lineAt(range.from).number; number <= state.doc.lineAt(range.to).number; number++) {
            const text = virtualTaskText(state, markers, state.doc.line(number))

            if (text) {
                suppressed.push(text.from, text.to)
            }
        }

        suppressed.push(range.to)

        return suppressed
    })
}

/**
 * Editing a task in the virtual project edits it in its own project as well, which is where it's saved from.
 * A task can't be split into more lines there, new tasks are added to their own project instead.
 * Keep this before other transaction filters, so that it runs after them and copies what they changed as well, like a date written for `#today`.
 */
function mirrorVirtualEdits(markers: StateField<RangeSet<ItemMarker>>) {
    return EditorState.transactionFilter.of((transaction) => {
        if (! transaction.docChanged || transaction.annotation(Transaction.addToHistory) === false) {
            return transaction
        }

        const { startState: state } = transaction
        const range = virtualRange(state, markers)

        if (! range) {
            return transaction
        }

        const mirrored: { from: number, to: number, insert: Text }[] = []
        let isSplit = false

        transaction.changes.iterChanges((fromA, toA, _fromB, _toB, inserted) => {
            if (fromA < range.from || toA > range.to) {
                return
            }

            const line = state.doc.lineAt(fromA)
            const text = virtualTaskText(state, markers, line)

            if (! text || fromA < text.from || toA > text.to) {
                return
            }

            if (inserted.lines > 1) {
                isSplit = true

                return
            }

            const item = itemOn(state, markers, line)
            const own = item ? ownLineOf(state, markers, item) : null

            // Right after its own line was edited, the virtual project might not be up to date yet, it's brought up to date in a moment
            if (! own || taskText(own) !== state.sliceDoc(text.from, text.to)) {
                return
            }

            const ownFrom = own.to - taskText(own).length

            mirrored.push({ from: ownFrom + fromA - text.from, to: ownFrom + toA - text.from, insert: inserted })
        })

        if (isSplit) {
            return {}
        }

        return mirrored.length === 0
            ? transaction
            : [transaction, { changes: ChangeSet.of(mirrored, state.doc.length).map(transaction.changes), sequential: true }]
    })
}

type Known = Record<ItemType, Set<number>>

type Payload = {
    projects: {
        key: string
        id: number | null
        name: string
        // A subtask refers to the task it belongs to by its key, which comes before it within the same project
        tasks: { key: string, id: number | null, name: string, kind: 'task' | 'text', parent: string | null }[]
    }[]
    deleted: Record<'projects' | 'tasks', number[]>
    // Only when all tasks of a project are shown, their order can be saved
    ordered: boolean
    // Only when all projects are shown, their order can be saved
    orderedProjects: boolean
}

/**
 * Collects all projects with their tasks and text in the document, ready to be saved.
 * Lines that aren't tracked yet get a new item, which are returned so that they can be tracked from now on.
 * Items that were known before but can't be found anymore, are reported as deleted.
 * Tasks and text above the first project don't belong to any project, so they aren't saved.
 */
function snapshot(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, known: Known, createKey: () => string): { payload: Payload, added: Range<ItemMarker>[] } {
    const payload: Payload = {
        projects: [],
        deleted: { projects: [], tasks: [] },
        ordered: activeFilters.length === 0,
        orderedProjects: activeFilters.length === 0 && activeProject === null,
    }
    const added: Range<ItemMarker>[] = []
    const present: Known = { project: new Set(), task: new Set(), text: new Set() }

    // The keys of the tasks that are saved, by their line number, so that their subtasks can refer to them
    const taskKeys = new Map<number, string>()

    for (let number = 1; number <= state.doc.lines; number++) {
        const line = state.doc.line(number)
        const type = typeOf(line)
        const name = type === 'text' ? line.text.trimEnd() : (type === 'task' ? taskText(line) : line.text.slice(2)).trim()
        const project = payload.projects.at(-1)

        // The virtual project only shows tasks of other projects again
        if (isVirtual(state, markers, line)) {
            continue
        }

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
            const parent = parentOf(state, line)

            project?.tasks.push({ key: item.key, id: item.id, name, kind: type, parent: parent ? taskKeys.get(parent.number) ?? null : null })
        }

        if (type === 'task') {
            taskKeys.set(line.number, item.key)
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

/**
 * Whether all characters of `query` are found in `text`, in the same order, ignoring case and spaces in the query.
 * The score is higher when characters follow each other, or start a word, and when it's found as a whole.
 * Returns where the characters were found, so that they can be highlighted.
 */
function fuzzyMatch(query: string, text: string): { score: number, indices: number[] } | null {
    const characters = [...query.toLowerCase().replace(/\s+/g, '')]
    const lower = text.toLowerCase()

    if (characters.length === 0) {
        return null
    }

    const indices: number[] = []
    let score = 0
    let position = 0

    for (const character of characters) {
        const index = lower.indexOf(character, position)

        if (index === -1) {
            return null
        }

        const isConsecutive = indices.length > 0 && index === indices[indices.length - 1] + 1
        const isWordStart = index === 0 || /[\s\-_/#@:.]/.test(lower[index - 1])

        score += 1 + (isConsecutive ? 3 : 0) + (isWordStart ? 2 : 0)
        indices.push(index)
        position = index + 1
    }

    if (lower.includes(query.toLowerCase().trim())) {
        score += 10
    }

    // Shorter texts match more closely
    return { score: score - text.length / 100, indices }
}

/**
 * A project or task that was found while searching, on this page, or in another workspace
 */
type SearchResult = {
    // The line on this page, or `null` when it's somewhere else
    line: number | null
    type: 'project' | 'task'
    // `null` until a newly written project or task has been saved
    id: number | null
    text: string
    project: string | null
    // The workspace it's in, when it's not on this page
    workspace: { id: number, name: string } | null
    completed: boolean
    indices: number[]
    score: number
}

/**
 * The projects and tasks of all workspaces, as loaded with the page, see `TodoController::searchItems()`
 */
type SearchItem = {
    type: 'project' | 'task'
    id: number
    workspace: number
    workspaceName: string
    project: string | null
    text: string
    completed: boolean
}

const SEARCH_LIMIT = 10

/**
 * Searches the projects and tasks on the page as they are now, followed by the ones of all workspaces that aren't on the page, best matches first.
 * The virtual project only shows tasks again, so it's left out. Completed ones are only found with `includeCompleted`.
 */
function search(state: EditorState, markers: StateField<RangeSet<ItemMarker>>, items: SearchItem[], query: string, includeCompleted: boolean): SearchResult[] {
    const results: SearchResult[] = []
    const onPage = new Set<string>()
    const completed = completedRanges(state, markers)
    let project: string | null = null

    for (let number = 1; number <= state.doc.lines; number++) {
        const line = state.doc.line(number)
        const type = typeOf(line)

        if (type === 'project') {
            project = line.text.slice(2).trim()
        }

        if ((type !== 'project' && type !== 'task') || isVirtual(state, markers, line)) {
            continue
        }

        const item = itemOn(state, markers, line)
        const id = item?.id ?? null

        // The title of a group that isn't a project, like the dates on the Soon page, isn't found
        if (groupOn(state, markers, line)) {
            continue
        }

        const isCompleted = completed.some(({ from, to }) => line.from >= from && line.from <= to)

        if (id !== null) {
            onPage.add(`${type}-${id}`)
        }

        if (isCompleted && ! includeCompleted) {
            continue
        }

        // On the Soon page, tasks are shown with the name of their own project in front of them
        const ownProject = item?.project ?? project
        const text = type === 'project' ? line.text.slice(2).trim() : taskText(line).replace(item?.project ? `${item.project}: ` : '', '').trim()
        const match = fuzzyMatch(query, text)

        if (match) {
            results.push({ line: number, type, id, text, project: type === 'task' ? ownProject : null, workspace: null, completed: isCompleted, ...match })
        }
    }

    for (const item of items) {
        if (onPage.has(`${item.type}-${item.id}`) || (item.completed && ! includeCompleted)) {
            continue
        }

        const match = fuzzyMatch(query, item.text)

        if (match) {
            results.push({
                line: null,
                type: item.type,
                id: item.id,
                text: item.text,
                project: item.project,
                workspace: { id: item.workspace, name: item.workspaceName },
                completed: item.completed,
                ...match,
            })
        }
    }

    // Sorting keeps the order for equal scores, so what's on this page comes first then
    return results.sort((a, b) => b.score - a.score).slice(0, SEARCH_LIMIT)
}

// A line that was jumped to from the search results is highlighted for a moment
const flashLine = StateEffect.define<number | null>()

const flashedLine = StateField.define<DecorationSet>({
    create: () => Decoration.none,

    update(decorations, transaction) {
        // Once something changes, the highlight isn't needed anymore
        if (transaction.docChanged) {
            decorations = Decoration.none
        }

        for (const effect of transaction.effects) {
            if (effect.is(flashLine)) {
                decorations = effect.value === null
                    ? Decoration.none
                    : Decoration.set([Decoration.line({ class: 'cm-flash' }).range(effect.value)])
            }
        }

        return decorations
    },

    provide: (field) => EditorView.decorations.from(field),
})

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
    '.cm-flash': {
        backgroundColor: 'rgb(254 249 195)',
    },
    // Everything that can be clicked to filter on looks the same: hashtags, mentions, dates, project titles, and project names in the virtual project,
    // except that project titles aren't underlined, see below
    '.cm-filterable': {
        fontWeight: 'bold',
        cursor: 'pointer',
        textDecorationLine: 'underline',
        textDecorationStyle: 'dotted',
    },
    '.cm-filterable:hover': {
        textDecorationStyle: 'solid',
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
    '.cm-drag-project': {
        left: '-4.75rem',
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
        cursor: 'pointer',
        textDecoration: 'underline',
    },
    '.cm-link:hover': {
        textDecoration: 'none',
    },
    '.cm-tag-sign': {
        fontWeight: 'bold',
    },
    // Unlike tags, project names aren't underlined until hovering them
    '.cm-project-title': {
        cursor: 'pointer',
        textDecorationLine: 'none',
    },
    '.cm-project-title:hover': {
        textDecorationLine: 'underline',
    },
    // In the virtual project, the names of the tasks' own projects are underlined like tags, so that they stand out from the tasks
    '.cm-project-name': {
        textDecorationLine: 'underline',
    },
    // The background of the virtual project reaches a bit beyond its text, without moving the text itself
    '.cm-virtual-section': {
        backgroundColor: 'var(--color-gray-50)',
        margin: '0 -0.75rem',
        paddingLeft: '0.75rem',
        paddingRight: '0.75rem',
    },
    // Groups with a background of their own, like overdue tasks on the Soon page
    '.cm-shade-light': {
        backgroundColor: 'var(--color-gray-50)',
    },
    '.cm-shade-dark': {
        backgroundColor: 'var(--color-gray-100)',
    },
    '.cm-virtual-first': {
        paddingTop: '0.5rem',
        borderTopLeftRadius: '0.5rem',
        borderTopRightRadius: '0.5rem',
    },
    '.cm-virtual-last': {
        paddingBottom: '0.5rem',
        borderBottomLeftRadius: '0.5rem',
        borderBottomRightRadius: '0.5rem',
    },
    '.cm-virtual-section .cm-check': {
        left: '-1.25rem',
    },
    '.cm-virtual-last .cm-check': {
        transform: 'translateY(calc(-50% - 0.25rem))',
    },
    // The titles of the virtual project and of other groups that aren't projects can't be clicked to only show that project
    '.cm-virtual .cm-project-title, .cm-group .cm-project-title': {
        cursor: 'text',
        textDecoration: 'none',
    },
    '.cm-virtual .cm-project-title:hover, .cm-group .cm-project-title:hover': {
        textDecoration: 'none',
    },
    // The project and tags that are being filtered on look the same
    '.cm-tag-active, .cm-project-title-active': {
        textDecoration: 'none',
        backgroundColor: 'var(--color-gray-100)',
        border: '1px dotted black',
        borderRadius: '0.25rem',
        boxDecorationBreak: 'clone',
        padding: '0.125rem 0.375rem',
    },
    '.cm-tag-active:hover, .cm-project-title-active:hover': {
        textDecoration: 'none',
    },
    // The placeholder is shown on top of the empty document, so that its lines don't make the first line, and the cursor on it, as tall as all of them
    '.cm-placeholder': {
        position: 'absolute',
        color: 'var(--color-gray-400)',
        pointerEvents: 'none',
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
    // A subtask's checkbox and drag handle are placed right before its `- `, instead of at the start of the line, so that they're indented along with it.
    // Without `left`, they stay where they're written in the line, which only works for inline elements.
    '.cm-subtask .cm-check, .cm-subtask .cm-drag': {
        display: 'inline-flex',
        left: 'auto',
    },
    '.cm-subtask .cm-check': {
        marginLeft: '-2rem',
    },
    '.cm-subtask .cm-drag': {
        marginLeft: '-3.375rem',
    },
    // Touch screens use gestures instead, see `enableTouchGestures`.
    // This comes last, so that it wins over the styles above.
    '@media (pointer: coarse)': {
        '.cm-check, .cm-subtask .cm-check': {
            display: 'none',
        },
        '.cm-drag, .cm-subtask .cm-drag': {
            display: 'none',
        },
        '.cm-archive': {
            display: 'none',
        },
        '.cm-collapse': {
            display: 'none',
        },
        // A bit more room around tasks makes them easier to swipe and drag
        '.cm-task': {
            paddingTop: '0.25rem',
            paddingBottom: '0.25rem',
        },
        '.cm-task.cm-virtual-last': {
            paddingBottom: '0.5rem',
        },
        // Sideways swipes are handled by the editor, so the browser shouldn't pan the page for them
        '.cm-content': {
            touchAction: 'pan-y pinch-zoom',
        },
    },
    // On wide screens, there's room for more space around the virtual project
    '@media (min-width: 1024px)': {
        '.cm-virtual-section': {
            margin: '0 -1.5rem',
            paddingLeft: '1.5rem',
            paddingRight: '1.5rem',
        },
        '.cm-virtual-section .cm-check': {
            left: '-0.5rem',
        },
    },
})

const textarea = document.querySelector<HTMLTextAreaElement>('#todo-markdown')

if (textarea) {
    const items: Record<string, Item> = {}
    const known: Known = { project: new Set(), task: new Set(), text: new Set() }

    for (const [lineNumber, item] of Object.entries<Omit<Item, 'key'>>(JSON.parse(textarea.dataset.items ?? '{}'))) {
        // Tasks in the virtual project are shown twice, so they need another key than in their own project
        // Groups don't have an ID, so they're told apart by where they are
        const key = item.type === 'group' ? `group-${lineNumber}` : (item.virtual ? `today-${item.type}-${item.id ?? 'title'}` : `${item.type}-${item.id}`)
        items[lineNumber] = { ...item, key }

        if (! item.virtual && item.type !== 'group' && item.id !== null) {
            known[item.type].add(item.id)
        }
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

    // Touch screens use gestures instead of the checkbox and drag handle, which are too small to use there
    const isTouchScreen = window.matchMedia('(pointer: coarse)').matches
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

        if (item.type === 'text' || item.type === 'group') {
            return
        }

        const url = item.completed ? urls[item.type].reopen : urls[item.type].complete
        const response = await fetch(url.replace('__ID__', String(item.id)), { method: 'POST' })

        if (! response.ok) {
            alert(`Could not update this ${item.type}`)

            return
        }

        // A task that's shown in the virtual project as well, is completed or reopened in its own project,
        // after which the virtual project is brought up to date, which only shows open tasks
        const ownLine = item.virtual ? ownLineOf(view.state, markers, item) : findLine(view.state, markers, item)
        const own = ownLine ? itemOn(view.state, markers, ownLine) : null
        const transaction = own ? toggleItem(view.state, markers, own) : null

        if (transaction) {
            view.dispatch(transaction)
        }

        updateUpcoming(view)
        tidy(view)
    }

    const updateUpcoming = (view: EditorView) => {
        const spec = mode === 'workspace' ? refreshUpcoming(view.state, markers) : null

        if (spec) {
            view.dispatch(spec)
        }
    }

    // After typing, the virtual project is brought up to date once there haven't been any changes for a moment
    let upcomingTimer: number | undefined

    const scheduleUpcoming = (view: EditorView) => {
        window.clearTimeout(upcomingTimer)
        upcomingTimer = window.setTimeout(() => updateUpcoming(view), 500)
    }

    /**
     * The elements showing a line, a task with all of its subtasks, or a project with all of its tasks and text.
     * Lines outside of the screen might not be shown, and lines of a collapsed project are hidden.
     */
    const elementsOf = (view: EditorView, line: Line): HTMLElement[] => {
        const last = isProject(line) ? blockOf(view.state, line).last.number : lastDescendant(view.state, line).number

        return Array.from(view.contentDOM.querySelectorAll<HTMLElement>(':scope > .cm-line')).filter((element) => {
            const number = view.state.doc.lineAt(view.posAtDOM(element)).number

            return number >= line.number && number <= last
        })
    }

    // Dragging a line shows where it will go, and moves it there when letting go.
    // A task or text moves between the other tasks and text of its project, a project moves between the other projects.
    const startReorder = (view: EditorView, line: Line, lineElement: HTMLElement | null) => {
        const isProjectLine = isProject(line)
        const lines = isProjectLine ? projectLines(view.state, markers) : sortableLines(view.state, markers, line)

        // A project is dragged together with its tasks and text, a task together with its subtasks
        const draggedElements = elementsOf(view, line)

        if (lineElement && ! draggedElements.includes(lineElement)) {
            draggedElements.push(lineElement)
        }

        const release = () => {
            for (const element of draggedElements) {
                element.classList.remove('cm-dragged')
                element.style.transform = ''
                element.style.zIndex = ''
            }
        }
        const content = view.contentDOM.getBoundingClientRect()
        const indicator = document.createElement('div')
        let slot = lines.findIndex((other) => other.number === line.number)

        // The space each slot covers on screen: a whole project including its tasks, or a task including its subtasks
        const spans = () => lines.map((other) => ({
            top: view.lineBlockAt(other.from).top,
            bottom: view.lineBlockAt(isProjectLine ? blockOf(view.state, other).last.to : lastDescendant(view.state, other).to).bottom,
        }))

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
        draggedElements.forEach((element) => element.classList.add('cm-dragged'))

        return {
            // Moves the dragged lines along with a finger, by the given distance from where dragging started
            follow(distance: number) {
                for (const element of draggedElements) {
                    element.style.transform = `translateY(${distance}px)`
                    element.style.zIndex = '10'
                }
            },

            move(clientY: number) {
                const current = spans()
                const found = current.findIndex((span) => clientY < view.documentTop + (span.top + span.bottom) / 2)

                slot = found === -1 ? lines.length : found

                const top = slot < current.length ? current[slot].top : current[current.length - 1].bottom

                indicator.style.top = `${view.documentTop + top}px`
            },

            cancel() {
                indicator.remove()
                release()
            },

            drop() {
                indicator.remove()
                release()

                const transactions = isProjectLine
                    ? reorderProject(view.state, markers, line, lines, slot)
                    : [reorder(view.state, markers, line, lines, slot)].filter((transaction) => transaction !== null)

                for (const transaction of transactions) {
                    view.dispatch(transaction)
                }

                tidy(view)
            },
        }
    }

    const drag = (view: EditorView, event: MouseEvent, handle: HTMLElement) => {
        event.preventDefault()

        const reordering = startReorder(view, view.state.doc.lineAt(view.posAtDOM(handle)), handle.closest<HTMLElement>('.cm-line'))
        const move = (event: MouseEvent) => reordering.move(event.clientY)

        const drop = () => {
            window.removeEventListener('mousemove', move)
            window.removeEventListener('mouseup', drop)
            document.body.style.cursor = ''
            reordering.drop()
        }

        document.body.style.cursor = 'grabbing'
        move(event)
        window.addEventListener('mousemove', move)
        window.addEventListener('mouseup', drop)
    }

    /**
     * On touch screens, where the checkbox, drag handle, and archive button are hidden:
     * swiping a line to the left completes or reopens it, swiping a project to the right archives it (or unarchives it),
     * and pressing and holding a line picks it up to drag it up or down.
     * Double tapping a project's title collapses or expands the project, tapping anywhere else starts editing there.
     */
    const SWIPE_DISTANCE = 80
    const HOLD_DURATION = 400
    const MOVE_TOLERANCE = 10

    const canToggle = (view: EditorView, line: Line) => {
        const item = itemOn(view.state, markers, line)

        return item !== null
            && item.id !== null
            && item.type !== 'text'
            && mode !== 'archived'
            && ! (item.type === 'project' && foldAt(view.state, line))
    }

    const canArchive = (view: EditorView, line: Line) => {
        const item = itemOn(view.state, markers, line)

        return item?.type === 'project'
            && item.id !== null
            && (mode === 'workspace' || mode === 'archived')
    }

    // Shows only the given project, or all projects again when it's the one shown already
    const openProject = (project: string) => {
        if (filterUrl) {
            window.location.href = filterHref(filterUrl, activeFilters, isActiveProject(project) ? null : project)
        }
    }

    const canCollapse = (view: EditorView, line: Line) => {
        const item = itemOn(view.state, markers, line)

        return mode === 'workspace' && item?.type === 'project' && item.id !== null && ! item.virtual && projectFold(view.state, line) !== null
    }

    const canReorder = (view: EditorView, line: Line) => mode === 'workspace' && (isSortable(view.state, markers, line) || isSortableProject(view.state, markers, line))

    // Set up along with the edit button, see below
    let startEditingAt: ((position: number) => void) | null = null

    const enableTouchGestures = (view: EditorView) => {
        let gesture: 'pending' | 'swipe' | 'archive' | 'reorder' | 'scroll' | null = null
        let lastTap: { line: number, time: number } | null = null
        let tappedProject: string | null = null
        let tappedLink = false
        let openProjectTimer: number | undefined
        let start = { x: 0, y: 0 }
        let tapPosition = 0
        let distance = 0
        let line: Line | null = null
        let lineElement: HTMLElement | null = null
        let swipedElements: HTMLElement[] = []
        let holdTimer: number | undefined
        let reordering: ReturnType<typeof startReorder> | null = null

        const reset = () => {
            window.clearTimeout(holdTimer)

            for (const element of swipedElements) {
                element.style.transform = ''
                element.style.backgroundColor = ''
            }

            swipedElements = []

            gesture = null
            line = null
            lineElement = null
            reordering = null
        }

        view.contentDOM.addEventListener('touchstart', (event) => {
            reset()

            const touch = event.touches[0]
            const position = event.touches.length === 1 ? view.posAtCoords({ x: touch.clientX, y: touch.clientY }) : null

            if (position === null) {
                return
            }

            gesture = 'pending'
            start = { x: touch.clientX, y: touch.clientY }
            tapPosition = position
            distance = 0
            line = view.state.doc.lineAt(position)
            lineElement = (event.target as HTMLElement).closest<HTMLElement>('.cm-line')
            tappedProject = (event.target as HTMLElement).closest<HTMLElement>('.cm-line:not(.cm-virtual, .cm-group) .cm-project-title')?.dataset.project ?? null
            // Tags and links do something of their own when tapped, see `domEventHandlers`, and so does the placeholder of a collapsed project
            tappedLink = (event.target as HTMLElement).closest('.cm-tag, .cm-link, .cm-foldPlaceholder') !== null

            holdTimer = window.setTimeout(() => {
                if (gesture !== 'pending' || ! line) {
                    return
                }

                if (! canReorder(view, line)) {
                    return
                }

                gesture = 'reorder'
                reordering = startReorder(view, line, lineElement)
                reordering.move(start.y)
                navigator.vibrate?.(10)
            }, HOLD_DURATION)
        }, { passive: true })

        view.contentDOM.addEventListener('touchmove', (event) => {
            const touch = event.touches[0]
            const dx = touch.clientX - start.x
            const dy = touch.clientY - start.y

            if (gesture === 'pending' && Math.max(Math.abs(dx), Math.abs(dy)) > MOVE_TOLERANCE) {
                window.clearTimeout(holdTimer)
                const isSideways = Math.abs(dx) > Math.abs(dy)

                if (isSideways && dx < 0 && line && canToggle(view, line)) {
                    gesture = 'swipe'
                } else if (isSideways && dx > 0 && line && canArchive(view, line)) {
                    gesture = 'archive'
                } else {
                    gesture = 'scroll'
                }

                // Swiping a project moves and colors it as a whole, including its tasks and text
                if ((gesture === 'swipe' || gesture === 'archive') && line) {
                    swipedElements = elementsOf(view, line)
                }
            }

            // Once the browser started scrolling, it can't be stopped anymore. Scrolling wins then, instead of swiping or dragging.
            if ((gesture === 'swipe' || gesture === 'archive' || gesture === 'reorder') && ! event.cancelable) {
                reordering?.cancel()
                reset()

                return
            }

            if (gesture === 'swipe' || gesture === 'archive') {
                event.preventDefault()
                distance = gesture === 'swipe' ? Math.min(0, dx) : Math.max(0, dx)

                // Green for completing, gray for reopening, purple for archiving, stronger once letting go would do it
                const progress = Math.min(1, Math.abs(distance) / SWIPE_DISTANCE)
                const strength = progress < 1 ? progress * 0.2 : 0.35
                const isReopening = gesture === 'swipe' && line !== null && itemOn(view.state, markers, line)?.completed === true
                const color = gesture === 'archive' ? '168 85 247' : (isReopening ? '107 114 128' : '34 197 94')

                for (const element of swipedElements) {
                    element.style.transform = `translateX(${distance}px)`
                    element.style.backgroundColor = `rgb(${color} / ${strength})`
                }
            }

            if (gesture === 'reorder' && reordering) {
                event.preventDefault()
                reordering.move(touch.clientY)
                reordering.follow(dy)
            }
        }, { passive: false })

        const DOUBLE_TAP_DURATION = 350

        const end = (event: TouchEvent) => {
            const isFarEnough = Math.abs(distance) >= SWIPE_DISTANCE
            const item = (gesture === 'swipe' || gesture === 'archive') && isFarEnough && line ? itemOn(view.state, markers, line) : null
            const action = gesture === 'archive' ? archive : toggle

            // Tapping a project's title twice in a row collapses or expands it
            const isTap = gesture === 'pending' && line !== null
            const isDoubleTap = isTap && lastTap !== null && lastTap.line === line?.number && event.timeStamp - lastTap.time < DOUBLE_TAP_DURATION
            const project = isDoubleTap && line && canCollapse(view, line) ? itemOn(view.state, markers, line) : null

            // Tapping anywhere else starts editing there, except on a project that can be collapsed, which would be the first tap of a double tap.
            // There, tapping twice in a row starts editing instead. While editing, tapping places the cursor like it usually does.
            const isCollapsible = line !== null && canCollapse(view, line)
            const startsEditing = isTap && (isDoubleTap || ! isCollapsible) && ! project && ! tappedProject && ! tappedLink
                && startEditingAt !== null && ! view.state.facet(EditorView.editable)

            lastTap = isTap && ! isDoubleTap && ! startsEditing && line ? { line: line.number, time: event.timeStamp } : null

            // A single tap on a project's title shows only that project, but only once it's clear that it's not a double tap
            window.clearTimeout(openProjectTimer)

            if (isTap && ! isDoubleTap && tappedProject) {
                const project = tappedProject

                openProjectTimer = window.setTimeout(() => openProject(project), DOUBLE_TAP_DURATION)
            }

            // Without this, the browser would place the cursor again, or zoom in on a double tap
            if (isDoubleTap || startsEditing) {
                event.preventDefault()
            }

            if (gesture === 'reorder' && reordering) {
                reordering.drop()
            }

            reset()

            if (item) {
                action(view, item)
            }

            if (project) {
                // Without this, the browser would zoom in or select a word on the second tap
                event.preventDefault()
                collapse(view, project)
            }

            if (startsEditing) {
                startEditingAt?.(tapPosition)
            }
        }

        view.contentDOM.addEventListener('touchend', end, { passive: false })
        view.contentDOM.addEventListener('touchcancel', reset)

        // Holding a line shouldn't open the browser's menu while it's being dragged
        view.contentDOM.addEventListener('contextmenu', (event) => {
            if (gesture === 'reorder') {
                event.preventDefault()
            }
        })
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

        tidy(view)
    }

    // Whether completed tasks and projects are hidden is remembered on this device
    const HIDE_COMPLETED_KEY = 'todo-hide-completed'
    const completedHiding = createCompletedHiding(markers, localStorage.getItem(HIDE_COMPLETED_KEY) === 'true')

    const editing = new Compartment()
    const editable = (isEditable: boolean) => [EditorState.readOnly.of(! isEditable), EditorView.editable.of(isEditable)]

    const view = new EditorView({
        doc: textarea.value,
        extensions: [
            history(),
            keymap.of([
                { key: 'Enter', run: continueTask },
                { key: 'Backspace', run: confirmBulkDelete },
                { key: 'Backspace', run: removeEmptyTask },
                { key: 'Delete', run: confirmBulkDelete },
                { key: 'Mod-Backspace', run: deleteSelectionWithoutConfirming },
                { key: 'Mod-Delete', run: deleteSelectionWithoutConfirming },
                ...defaultKeymap,
                ...historyKeymap,
                indentWithTab,
            ]),
            placeholder(textarea.placeholder),
            EditorView.lineWrapping,
            mirrorVirtualEdits(markers),
            normalizeSpacing,
            replaceRelativeDates,
            autocompletion({ override: [tagCompletions(usedTags)], icons: false }),
            // Tab picks the selected suggestion while suggestions are shown, and indents otherwise
            Prec.highest(keymap.of([{ key: 'Tab', run: expandRelativeDate }, { key: 'Tab', run: acceptCompletion }])),
            // On touch screens, editing is toggled with a button, so that tapping around doesn't open the keyboard
            editing.of(editable(mode === 'workspace' && ! isTouchScreen)),
            virtualChangeFilter(markers),
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
            flashedLine,
            itemDecorations(markers, { drag, toggle, collapse, archive, mode }),
            completedHiding,
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
                    const project = (event.target as HTMLElement).closest<HTMLElement>('.cm-line:not(.cm-virtual, .cm-group) .cm-project-title')?.dataset.project

                    if (project) {
                        event.preventDefault()

                        // On touch screens, a tap on a project's title could be the first of a double tap, which collapses it instead.
                        // Touch gestures take care of that, see `enableTouchGestures`.
                        if (! isTouchScreen) {
                            openProject(project)
                        }

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

                if (update.transactions.some((transaction) => transaction.docChanged && transaction.annotation(Transaction.addToHistory) !== false)) {
                    scheduleUpcoming(update.view)
                }

                // A task that just got a date is saved right away, so that it shows up in the virtual project as soon as possible
                if (update.transactions.some((transaction) => transaction.annotation(relativeDateResolved))) {
                    scheduleSave(0)
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

            // Tasks only show up in the virtual project once they're saved, because it needs to know which task each copy belongs to
            updateUpcoming(view)

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

    // On a phone, the page isn't loaded again when coming back to it, especially when it's added to the home screen as an app:
    // it's shown as it was, maybe days ago. Editing it then would save over changes made somewhere else in the meantime,
    // so it's loaded again when it's been away for a while, once its own changes are saved.
    const RELOAD_AFTER = 5 * 60 * 1000

    let hiddenAt: number | null = null

    const reloadWhenSaved = () => {
        if (hasChanges || isSaving) {
            window.setTimeout(reloadWhenSaved, 500)

            return
        }

        window.location.reload()
    }

    const refreshButton = document.querySelector<HTMLButtonElement>('#todo-refresh')

    if (refreshButton) {
        refreshButton.onclick = () => {
            refreshButton.closest('details')?.removeAttribute('open')
            showStatus('Loading…')
            window.clearTimeout(saveTimer)
            save()
            reloadWhenSaved()
        }
    }

    if (isTouchScreen) {
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                hiddenAt = Date.now()

                // Apps that are put away might not get the chance to save later on
                window.clearTimeout(saveTimer)
                save()

                return
            }

            if (hiddenAt !== null && Date.now() - hiddenAt >= RELOAD_AFTER) {
                showStatus('Loading…')
                reloadWhenSaved()
            }

            hiddenAt = null
        })
    }

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

    if (isTouchScreen) {
        enableTouchGestures(view)
    }

    // On iOS, fixed elements are placed at the bottom of the page as a whole, not of the part that's visible,
    // so they'd end up behind the keyboard, and move along while scrolling with the keyboard open.
    // How much of the page the keyboard covers is kept up to date in `--keyboard-inset`, so that the status, floating buttons, and new task popup can stay right above it.
    const viewport = window.visualViewport

    if (isTouchScreen && viewport) {
        let frame: number | undefined

        const updateKeyboardInset = () => {
            window.cancelAnimationFrame(frame ?? 0)

            frame = window.requestAnimationFrame(() => {
                const inset = Math.max(0, document.documentElement.clientHeight - viewport.height - viewport.offsetTop)

                document.documentElement.style.setProperty('--keyboard-inset', `${inset}px`)
                // With the keyboard open, the part that's visible might be scrolled down, which the new task popup keeps up with
                document.documentElement.style.setProperty('--viewport-top', `${Math.max(0, viewport.offsetTop)}px`)
            })
        }

        viewport.addEventListener('resize', updateKeyboardInset)
        viewport.addEventListener('scroll', updateKeyboardInset)
    }

    const hideCompletedButton = document.querySelector<HTMLButtonElement>('#todo-hide-completed')
    const hideCompletedLabel = hideCompletedButton?.querySelector('span')

    const showHideCompletedState = () => {
        const isHidden = view.state.field(completedHiding).hidden

        hideCompletedButton?.setAttribute('aria-pressed', String(isHidden))

        if (hideCompletedLabel) {
            hideCompletedLabel.textContent = isHidden ? 'Show completed' : 'Hide completed'
        }
    }

    const setCompletedHidden = (isHidden: boolean) => {
        const ranges = isHidden ? completedRanges(view.state, markers) : []
        const head = view.state.selection.main.head
        const hiding = ranges.find(({ from, to }) => head >= from && head <= to)

        view.dispatch({
            effects: hideCompleted.of(isHidden),
            // A cursor on a line that's being hidden moves right above it, or below it at the top,
            // so that typing doesn't change what can't be seen
            selection: hiding ? { anchor: hiding.from > 0 ? hiding.from - 1 : Math.min(hiding.to + 1, view.state.doc.length) } : undefined,
        })

        localStorage.setItem(HIDE_COMPLETED_KEY, String(isHidden))
        showHideCompletedState()
    }

    if (hideCompletedButton) {
        hideCompletedButton.addEventListener('click', () => {
            hideCompletedButton.closest('details')?.removeAttribute('open')
            setCompletedHidden(! view.state.field(completedHiding).hidden)
        })

        showHideCompletedState()
    }

    // Searching projects and tasks, with a search bar in the navigation on wide screens and in the menu on small screens
    const searchInputs = Array.from(document.querySelectorAll<HTMLInputElement>('[data-search]'))

    // Folded projects are expanded to show what was found, and the line is highlighted for a moment
    const jumpTo = (number: number) => {
        const line = view.state.doc.line(number)
        const unfold: StateEffect<{ from: number, to: number }>[] = []

        foldedRanges(view.state).between(line.from, line.from, (from, to) => {
            if (from <= line.from && to >= line.from) {
                unfold.push(unfoldEffect.of({ from, to }))
            }
        })

        view.dispatch({
            effects: [...unfold, flashLine.of(line.from), EditorView.scrollIntoView(line.from, { y: 'center' })],
            selection: { anchor: line.from + (isTask(line) ? line.text.length - taskText(line).length : 2) },
        })

        window.setTimeout(() => view.dispatch({ effects: flashLine.of(null) }), 1500)

        // On touch screens, focusing would open the keyboard, which editing does instead
        if (! isTouchScreen) {
            view.focus()
        }
    }

    // Completed tasks are only found when asked for, which goes for the tasks of completed projects, and subtasks of completed tasks as well.
    // This is the same for both search bars.
    let includeCompleted = false

    const searchItems: SearchItem[] = JSON.parse(textarea.dataset.searchItems ?? '[]')
    const openWorkspaceUrl = textarea.dataset.openWorkspaceUrl
    const searchFor = (query: string) => search(view.state, markers, searchItems, query, includeCompleted)

    // A project or task in another workspace is jumped to once that workspace is opened, see below
    const JUMP_KEY = 'todo-search-jump'

    const show = (result: SearchResult) => {
        // A completed task that's hidden can only be shown by showing all completed ones again
        if (result.completed && view.state.field(completedHiding).hidden) {
            setCompletedHidden(false)
        }

        if (result.line !== null) {
            jumpTo(result.line)

            return
        }

        if (! result.workspace || ! openWorkspaceUrl) {
            return
        }

        sessionStorage.setItem(JUMP_KEY, JSON.stringify({ type: result.type, id: result.id }))

        const form = document.createElement('form')
        const workspace = document.createElement('input')

        form.method = 'post'
        form.action = openWorkspaceUrl
        workspace.type = 'hidden'
        workspace.name = 'workspace'
        workspace.value = String(result.workspace.id)
        form.append(workspace)
        document.body.append(form)

        // Changes that haven't been saved yet are saved first
        window.clearTimeout(saveTimer)
        save().finally(() => form.submit())
    }

    for (const input of searchInputs) {
        const resultsElement = document.getElementById(input.dataset.search ?? '')

        if (! resultsElement) {
            continue
        }

        let results: SearchResult[] = []
        let active = 0

        const close = () => {
            resultsElement.hidden = true
            resultsElement.replaceChildren()
        }

        const pick = (index: number) => {
            const result = results[index]

            if (! result) {
                return
            }

            input.value = ''
            close()
            input.blur()
            input.closest('details')?.removeAttribute('open')
            show(result)
        }

        // The characters that were found are highlighted
        const highlighted = (text: string, indices: number[], completed: boolean) => {
            const element = document.createElement('span')
            const found = new Set(indices)

            element.className = completed ? 'min-w-0 truncate line-through text-gray-400' : 'min-w-0 truncate'

            for (const [index, character] of [...text].entries()) {
                if (found.has(index)) {
                    const mark = document.createElement('mark')

                    mark.className = completed ? 'bg-transparent font-bold text-gray-500' : 'bg-transparent font-bold text-gray-900'
                    mark.textContent = character
                    element.append(mark)
                } else {
                    element.append(character)
                }
            }

            return element
        }

        // Below the results, completed tasks can be included as well
        const completedToggle = document.createElement('button')

        completedToggle.type = 'button'
        completedToggle.className = 'group flex items-center gap-2 mt-1 px-3 pt-2 pb-1.5 border-t border-gray-200 text-left text-xs text-gray-400 hover:text-gray-900 cursor-pointer aria-pressed:text-gray-900'
        completedToggle.innerHTML = '<span class="flex items-center justify-center w-3.5 h-3.5 rounded-sm border-[1.5px] border-current" aria-hidden="true"><svg viewBox="0 0 16 16" class="hidden w-3 h-3 group-aria-pressed:block"><path d="M3.5 8.5l3 3 6-7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span> Include completed'
        completedToggle.addEventListener('mousedown', (event) => event.preventDefault())
        completedToggle.addEventListener('click', () => {
            includeCompleted = ! includeCompleted
            results = searchFor(input.value)
            active = 0
            render()
        })

        const render = () => {
            if (input.value.trim() === '') {
                close()

                return
            }

            completedToggle.setAttribute('aria-pressed', String(includeCompleted))

            if (results.length === 0) {
                const empty = document.createElement('p')

                empty.className = 'px-3 py-1.5 text-gray-400'
                empty.textContent = 'Nothing found'
                resultsElement.replaceChildren(empty, completedToggle)
                resultsElement.hidden = false

                return
            }

            resultsElement.replaceChildren(...results.map((result, index) => {
                const button = document.createElement('button')

                button.type = 'button'
                button.className = 'flex w-full items-baseline gap-2 px-3 py-1.5 text-left text-gray-600 rounded cursor-pointer aria-selected:bg-gray-100'
                button.setAttribute('aria-selected', String(index === active))
                // Keeps the search bar focused, so that it doesn't close before the result is picked
                button.addEventListener('mousedown', (event) => event.preventDefault())
                button.addEventListener('click', () => pick(index))

                if (result.type === 'project') {
                    const sign = document.createElement('span')

                    sign.className = 'shrink-0 text-gray-400'
                    sign.textContent = '#'
                    button.append(sign)
                }

                button.append(highlighted(result.text, result.indices, result.completed))

                // Where it is: the project of a task, after the workspace when that's another one
                const where = [result.workspace?.name, result.type === 'task' ? result.project : null].filter((part) => part)

                if (where.length > 0) {
                    const location = document.createElement('span')

                    location.className = 'ml-auto shrink-0 max-w-[40%] truncate text-xs text-gray-400'
                    location.textContent = where.join(' / ')
                    button.append(location)
                }

                return button
            }), completedToggle)

            resultsElement.hidden = false
            resultsElement.children[active]?.scrollIntoView({ block: 'nearest' })
        }

        input.addEventListener('input', () => {
            results = searchFor(input.value)
            active = 0
            render()
        })

        input.addEventListener('focus', () => {
            if (input.value.trim() !== '') {
                results = searchFor(input.value)
                render()
            }
        })

        input.addEventListener('blur', close)

        input.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault()

                if (results.length > 0) {
                    active = (active + (event.key === 'ArrowDown' ? 1 : -1) + results.length) % results.length
                    render()
                }
            }

            if (event.key === 'Enter') {
                event.preventDefault()
                pick(active)
            }

            if (event.key === 'Escape') {
                event.preventDefault()
                input.value = ''
                close()
                input.blur()

                if (! isTouchScreen) {
                    view.focus()
                }
            }
        })
    }

    // Cmd+K (or Ctrl+K) focuses the search bar that's shown, which is in the menu on small screens
    window.addEventListener('keydown', (event) => {
        if (! (event.metaKey || event.ctrlKey) || event.key.toLowerCase() !== 'k') {
            return
        }

        const input = searchInputs.find((input) => input.offsetParent !== null)
            ?? searchInputs.find((input) => input.closest('details') !== null)

        if (! input) {
            return
        }

        event.preventDefault()
        input.closest('details')?.setAttribute('open', '')
        input.focus()
        input.select()
    })

    const editButton = document.querySelector<HTMLButtonElement>('#todo-edit')

    if (editButton && isTouchScreen && mode === 'workspace') {
        editButton.hidden = false

        const isEditing = () => view.state.facet(EditorView.editable)

        const setEditing = (isEditable: boolean) => {
            view.dispatch({ effects: editing.reconfigure(editable(isEditable)) })
            editButton.setAttribute('aria-pressed', String(isEditable))

            // The floating buttons are hidden while editing, editing is turned off by closing the keyboard instead, see below
            document.body.toggleAttribute('data-editing', isEditable)
        }

        // Keeps the editor focused while tapping the button, so that it's only turned off by the button itself, see below
        editButton.addEventListener('mousedown', (event) => event.preventDefault())

        // While editing, the same buttons as in the new task popup are shown right above the keyboard
        const shortcutBar = document.querySelector<HTMLElement>('#todo-shortcuts')
        const shortcuts = shortcutBar ? tagShortcuts(shortcutBar, () => view, true) : null

        if (shortcuts) {
            view.dispatch({
                effects: StateEffect.appendConfig.of([
                    EditorView.updateListener.of((update) => {
                        if (update.docChanged || update.selectionSet) {
                            shortcuts.update()
                        }
                    }),
                    // Keeps the line that's being edited clear of the buttons
                    EditorView.scrollMargins.of(() => ({ bottom: 64 })),
                ]),
            })
        }

        // Focusing opens the keyboard right away
        startEditingAt = (position: number) => {
            setEditing(true)
            shortcuts?.refreshDates()
            view.dispatch({ selection: { anchor: position } })
            view.focus()
        }

        editButton.addEventListener('click', () => {
            // The cursor goes in the middle of what's shown, so that the page doesn't scroll to wherever it was before
            if (! isEditing()) {
                const content = view.contentDOM.getBoundingClientRect()

                startEditingAt?.(view.posAtCoords({ x: content.left + content.width / 2, y: window.innerHeight / 2 }, false))

                return
            }

            // Blurring closes the keyboard again
            setEditing(false)
            view.contentDOM.blur()
        })

        // Closing the keyboard, like with the checkmark above it on iOS, or tapping outside of the editor, turns editing off as well.
        // Switching to another app doesn't, the page loses focus as a whole then.
        view.contentDOM.addEventListener('blur', () => {
            if (isEditing() && document.hasFocus()) {
                setEditing(false)
            }
        })
    }

    const addButton = document.querySelector<HTMLButtonElement>('#todo-add')
    const newTask = document.querySelector<HTMLDialogElement>('#new-task')
    const newTaskForm = newTask?.querySelector('form')
    const newTaskText = newTask?.querySelector<HTMLElement>('#new-task-text')
    const newTaskProject = newTask?.querySelector<HTMLSelectElement>('#new-task-project')
    const newTaskTags = newTask?.querySelector<HTMLElement>('#new-task-tags')

    if (addButton && newTask && newTaskForm && newTaskText && newTaskProject && newTaskTags && isTouchScreen && mode === 'workspace') {
        const titleOf = (line: Line) => line.text.slice(2).trim()

        // The project that's shown on its own is picked by default, and otherwise the one a task was last added to
        let lastProject = activeProject

        const shortcuts = tagShortcuts(newTaskTags, () => taskView)

        // The task is written in an editor of its own, so that its hashtags and mentions are suggested and styled like in the main editor.
        // A task is a single line, so Enter adds it, unless a suggestion is being picked.
        const taskView = new EditorView({
            parent: newTaskText,
            extensions: [
                history(),
                keymap.of([
                    { key: 'Enter', run: () => (newTaskForm.requestSubmit(), true) },
                    ...defaultKeymap,
                    ...historyKeymap,
                ]),
                placeholder('Task'),
                EditorView.lineWrapping,
                EditorView.contentAttributes.of({ 'aria-labelledby': 'new-task-label' }),
                replaceRelativeDates,
                autocompletion({ override: [tagCompletions(usedTags, () => writtenTags(view.state))], icons: false }),
                Prec.highest(keymap.of([{ key: 'Tab', run: expandRelativeDate }, { key: 'Tab', run: acceptCompletion }])),
                syntax,
                theme,
                EditorView.updateListener.of((update) => {
                    if (update.docChanged) {
                        shortcuts.update()
                    }
                }),
                // As high as the space it's given in the popup, instead of as high as its text
                Prec.highest(EditorView.theme({
                    '&': {
                        height: '100%',
                    },
                    '.cm-scroller': {
                        overflow: 'auto',
                    },
                })),
            ],
        })

        addButton.hidden = false

        addButton.addEventListener('click', () => {
            const titles = projectLines(view.state, markers).map(titleOf).filter((title) => title !== '')
            const options = titles.map((title) => new Option(title, title))

            if (options.length === 0) {
                options.push(new Option('No projects yet', '', true, true))
                options[0].disabled = true
            }

            newTaskProject.replaceChildren(...options)
            newTaskProject.value = titles.find((title) => title.toLowerCase() === lastProject?.toLowerCase()) ?? titles[0] ?? ''
            taskView.dispatch({ changes: { from: 0, to: taskView.state.doc.length } })
            shortcuts.refreshDates()
            newTask.showModal()
            taskView.focus()
        })

        newTaskForm.addEventListener('submit', (event) => {
            event.preventDefault()

            const project = projectLines(view.state, markers).find((line) => titleOf(line) === newTaskProject.value)
            // A task is a single line, so line breaks are written as spaces
            const text = taskView.state.doc.toString().replace(/\s+/g, ' ').trim().replace(/^- +/, '')

            if (! project || text === '') {
                return
            }

            addTask(view, markers, project, text)
            lastProject = newTaskProject.value
            newTask.close()
        })
    }

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

    // A project or task that was picked from the search results in another workspace, see `show`
    const jump = sessionStorage.getItem(JUMP_KEY)

    if (jump) {
        sessionStorage.removeItem(JUMP_KEY)

        const { type, id } = JSON.parse(jump) as { type: ItemType, id: number }
        const line = ownLineOf(view.state, markers, { type, id, key: '', completed: false })

        if (line) {
            const isCompleted = completedRanges(view.state, markers).some(({ from, to }) => line.from >= from && line.from <= to)

            if (isCompleted && view.state.field(completedHiding).hidden) {
                setCompletedHidden(false)
            }

            jumpTo(line.number)
        }
    }

    if (view.state.facet(EditorView.editable)) {
        view.focus()
    }
}
