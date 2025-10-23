<?php

namespace App\PhpDocs;

use App\PhpDocs\Elements\CodeElement;
use App\PhpDocs\Elements\DefaultElement;
use App\PhpDocs\Elements\InlineCodeElement;
use App\PhpDocs\Elements\LinkElement;
use App\PhpDocs\Elements\NestedElement;
use App\PhpDocs\Elements\NoteElement;
use App\PhpDocs\Elements\ParagraphElement;
use App\PhpDocs\Elements\SimpleParagraphElement;
use App\PhpDocs\Elements\RootElement;
use App\PhpDocs\Elements\TextElement;
use App\PhpDocs\Elements\TitleElement;
use App\PhpDocs\Elements\VoidElement;
use App\PhpDocs\Elements\WarningElement;
use Dom\Node;
use Dom\XMLDocument;
use DOMException;

final class DocBookParser
{
    public function parse(string $contents): ?string
    {
        try {
            $dom = XMLDocument::createFromString($contents, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED);
        } catch (DOMException) {
            return null;
        }

        $element = $this->parseNode($dom);

        return $element->render();
    }

    private function parseNode(Node $node): Element
    {
        $element = match ($node->nodeName) {
            'simpara' => new TextElement($node->textContent),
            'para' => new ParagraphElement(),
            'title' => new TitleElement($node->textContent),
            'note' => new NoteElement($node->textContent),
            'warning' => new WarningElement($node->textContent),
            'literal' => new InlineCodeElement($node->textContent),
            'programlisting' => new CodeElement(
                $node->textContent,
                $node instanceof \Dom\Element ? $node->getAttribute('role') : null,
            ),
            'link' => new LinkElement($node->textContent, $node->getAttribute('linkend')),
            '#text' => new TextElement($node->textContent),
            'sect1', '#document', 'informalexample' => new NestedElement(),
            '#comment', 'phpdoc' => new VoidElement(),
            default => new DefaultElement($node->nodeName, $node->textContent),
        };

        if ($element instanceof HasChildren) {
            foreach ($node->childNodes as $child) {
                $element->children[] = $this->parseNode($child);
            }
        }

        return $element;
    }
}