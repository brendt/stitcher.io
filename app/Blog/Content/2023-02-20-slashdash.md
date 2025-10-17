---
title: Slashdash
disableAds: true
---

A year or so ago, I stumbled upon a website for yet another document language similar to YAML, JSON or XML; it was called [KDL](https://kdl.dev/). I don't know what became of it, but it had one little feature that stood out: something they called "slashdash comments": `/-`. You can use it to mark code as comments, but it is scope aware.

For example, you can use it to comment out individual keywords and statements like `<hljs keyword>final</hljs>` or `<hljs keyword>implements</hljs> <hljs type>X</hljs>`:

```txt
<hljs comment>/-final</hljs> <hljs keyword>class</hljs> <hljs type>Foo</hljs> <hljs comment>/-implements Bar</hljs>
```

But you could also use the `/--` variant to comment out a whole section based on its scope:

```txt
<hljs comment>/--public function fromInterface(int $i): int {
    return $i + 1;
}</hljs>
```

It's just a small thing, but when I saw it, it immediately clicked. Especially in a language like PHP where "dump and die debugging" is the de-facto standard, it would be nice to have a slightly shorter and more convenient way to comment out code.

```txt
<hljs comment>/-final</hljs> <hljs keyword>class</hljs> <hljs type>Foo</hljs> <hljs comment>/-implements Bar</hljs>
{
    <hljs keyword>public <hljs comment>/-readonly</hljs></hljs> <hljs type>string</hljs> <hljs prop>$prop</hljs>;
    
    <hljs comment>/--public function fromInterface(int $i): int {
        return $i + 1;
    }</hljs>
}
```

{{ cta:dynamic }}