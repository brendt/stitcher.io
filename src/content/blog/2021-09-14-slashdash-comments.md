```txt
<hljs keyword>final</hljs> <hljs keyword>class</hljs> <hljs type>Foo</hljs> <hljs comment>/-implements Bar</hljs>
{
    <hljs keyword>public <hljs comment>/-readonly</hljs></hljs> <hljs type>string</hljs> <hljs prop>$prop</hljs>;
    
    <hljs keyword>public</hljs> <hljs keyword>function</hljs> <hljs prop>fromInterface</hljs>(<hljs type>int</hljs> $i): <hljs type>int</hljs> {
        <hljs keyword>return</hljs> $i + 1;
    }
}
```

```txt
<hljs comment>/-final</hljs> <hljs keyword>class</hljs> <hljs type>Foo</hljs> <hljs comment>/-implements Bar</hljs>
{
    <hljs keyword>public <hljs comment>/-readonly</hljs></hljs> <hljs type>string</hljs> <hljs prop>$prop</hljs>;
    
    <hljs comment>/--public function fromInterface(int $i): int {
        return $i + 1;
    }</hljs>
}
```

```txt
<hljs comment>/--final class Foo implements Bar
{
    public readonly string $prop;
    
    public function fromInterface(int $i): int {
        return $i + 1;
    }
}</hljs>
```

https://kdl.dev/
