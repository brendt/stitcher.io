import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands'
import { RangeSetBuilder } from '@codemirror/state'
import { Decoration, EditorView, keymap, placeholder, ViewPlugin } from '@codemirror/view'
import type { DecorationSet, ViewUpdate } from '@codemirror/view'

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
    },
    '.cm-content': {
        minHeight: '70vh',
        padding: '0',
        caretColor: 'var(--color-gray-900)',
    },
    '.cm-line': {
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
})

const textarea = document.querySelector<HTMLTextAreaElement>('#todo-markdown')

if (textarea) {
    const view = new EditorView({
        doc: textarea.value,
        extensions: [
            history(),
            keymap.of([...defaultKeymap, ...historyKeymap, indentWithTab]),
            placeholder(textarea.placeholder),
            EditorView.lineWrapping,
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
