<?php

namespace App\PhpDocs;

use App\PhpDocs\Elements\CodeElement;
use App\PhpDocs\Elements\DefaultElement;
use App\PhpDocs\Elements\ExampleElement;
use App\PhpDocs\Elements\InlineCodeElement;
use App\PhpDocs\Elements\LinkElement;
use App\PhpDocs\Elements\ListElement;
use App\PhpDocs\Elements\ListItemElement;
use App\PhpDocs\Elements\NestedElement;
use App\PhpDocs\Elements\NoteElement;
use App\PhpDocs\Elements\ParagraphElement;
use App\PhpDocs\Elements\TextElement;
use App\PhpDocs\Elements\TitleElement;
use App\PhpDocs\Elements\VoidElement;
use App\PhpDocs\Elements\WarningElement;
use Dom\Node;
use Dom\XMLDocument;
use DOMException;

final class DocBookParser
{
    private array $entities;
    private string $path;

    public function __construct()
    {
        $entityFiles = glob(__DIR__ . '/xml/**.ent');
        $entityFiles[] = __DIR__ . '/global/global.ent';

        foreach ($entityFiles as $file) {
            $entityFile = file_get_contents($file);

            preg_match_all("/<!ENTITY (?<name>[\w.]+)\s*'(?<value>.*?)'>/m", $entityFile, $matches);

            foreach ($matches['name'] as $index => $name) {
                $this->entities["&{$name};"] = $matches['value'][$index];
            }
        }

        $this->entities['&url.pecl.package;'] = 'TODO';
        $this->entities['&link.pecl;'] = 'TODO';
        $this->entities['&example.outputs.similar;'] = 'TODO';
        $this->entities['&url.rfc;'] = 'TODO';
        $this->entities['&warn.deprecated.feature-7-2-0.removed-8-0-0;'] = 'TODO';
        $this->entities['&url.floating.point.guide;'] = 'TODO';
        $this->entities['&Description;'] = 'TODO';
        $this->entities['&Version;'] = 'TODO';
        $this->entities['&Changelog;'] = 'TODO';
        $this->entities['&Methods;'] = 'TODO';
    }

    public function parse(string $path): ?string
    {
        $this->path = $path;
        $contents = file_get_contents($path);

        // Stripping
        $contents = str_replace(
            array_keys($this->entities),
            array_values($this->entities),
            $contents,
        );

        $contents = preg_replace_callback(
            '/&([\w.-]+);/m',
            function (array $matches) {
                return '{{ ' . $matches[1] . ' }}';
            },
            $contents
        );

        try {
            $dom = XMLDocument::createFromString($contents);
        } catch (DOMException) {
            return null;
        }

        $element = $this->parseNode($dom);

        return $element->render();
    }

    private function parseNode(Node $node): Element
    {
        $element = match ($node->nodeName) {
            'simpara', '#text' => new TextElement($node->textContent),
            'para' => new ParagraphElement(),
            'title' => new TitleElement($node->textContent),
            'note' => new NoteElement($node->textContent),
            'warning' => new WarningElement($node->textContent),
            'example' => new ExampleElement($node->textContent),
            'itemizedlist' => new ListElement(),
            'listitem' => new ListItemElement(),
            'code', 'literal', 'classname', 'function', 'type', 'constant' => new InlineCodeElement($node->textContent),
            'screen', 'programlisting' => new CodeElement(
                $node->textContent,
                $node instanceof \Dom\Element ? $node->getAttribute('role') : 'php',
            ),
            'link' => $node instanceof \Dom\Element
                ? new LinkElement($node->textContent, $node->getAttribute('linkend') ?? $node->getAttribute('xlink:href'))
                : new LinkElement($node->textContent, null),
            'reference', 'sect2', 'sect1', '#document', 'informalexample', 'partintro', 'section' => new NestedElement(),
            'titleabbrev', '#comment', 'phpdoc' => new VoidElement(),
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