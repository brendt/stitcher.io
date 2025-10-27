<?php

namespace App\PhpDocs;

use App\PhpDocs\Elements\CodeElement;
use App\PhpDocs\Elements\DefaultElement;
use App\PhpDocs\Elements\DivElement;
use App\PhpDocs\Elements\InlineCodeElement;
use App\PhpDocs\Elements\LinkElement;
use App\PhpDocs\Elements\ListElement;
use App\PhpDocs\Elements\MemberElement;
use App\PhpDocs\Elements\NestedElement;
use App\PhpDocs\Elements\ParagraphElement;
use App\PhpDocs\Elements\MethodSynopsisElement;
use App\PhpDocs\Elements\SimpleParagraphElement;
use App\PhpDocs\Elements\TextElement;
use App\PhpDocs\Elements\TitleElement;
use App\PhpDocs\Elements\VoidElement;
use Dom\Node;
use Dom\XMLDocument;
use DOMException;
use Tempest\Highlight\Highlighter;

final class PhpDocsParser
{
    private array $entities;
    private string $path;
    private string $slug;
    private int $titleLevel = 1;

    public function __construct(
        private Highlighter $highlighter,
    )
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

    public function parse(string $slug, string $path): ?string
    {
        $this->titleLevel = 1;
        $this->path = $path;
        $this->slug = $slug;
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
            $contents,
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
            '#text', 'acronym' => new TextElement($node->textContent),
            'simpara' => new SimpleParagraphElement($node->textContent),
            'para' => new ParagraphElement(),
            'title', 'refname' => new TitleElement($node->textContent, $this->titleLevel),
            'itemizedlist', 'simplelist' => new ListElement(),
            'code', 'literal', 'classname', 'function', 'type', 'constant', 'parameter', 'filename' => new InlineCodeElement($node->textContent, $this->highlighter),
            'methodsynopsis' => new MethodSynopsisElement($node, $this->highlighter),
            'member' => new MemberElement($this->slug, $node),
            'screen', 'programlisting' => new CodeElement(
                $node->textContent,
                $node instanceof \Dom\Element ? $node->getAttribute('role') : 'php',
                $this->highlighter,
            ),
            'link' => new LinkElement($node),
            'refsect1' => new DivElement($node->getAttribute('role') ?? $node->nodeName),

            'varlistentry', 'term', 'listitem', 'caution', 'note', 'warning', 'refpurpose',
            'appendix', 'info' => new DivElement($node->nodeName),

            'refentry', 'chapter', 'reference', 'sect2', 'sect1', '#document',
            'informalexample', 'partintro', 'section', 'refnamediv',
            'variablelist', 'example' => new NestedElement(),

            'titleabbrev', '#comment', 'phpdoc' => new VoidElement(),

            default => new DefaultElement($node->nodeName, $node->textContent),
        };

        if ($element instanceof HasChildren) {
            foreach ($node->childNodes as $child) {
                $element->children[] = $this->parseNode($child);
            }
        }

        if ($element instanceof TitleElement && $this->titleLevel === 1) {
            $this->titleLevel = 2;
        }

        return $element;
    }
}