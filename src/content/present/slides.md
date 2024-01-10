```php
$handlers = [ /* … */ ];

$middleware = [ /* … */ ];

$bus = <hljs type>CommandBus</hljs>::<hljs prop>new</hljs>()
    -><hljs prop>addHandlers</hljs>($handlers)
    -><hljs prop>addMiddleware</hljs>($middleware);
```

---

```php
$bus-><hljs prop>dispatch</hljs>(new <hljs type>PayInvoice</hljs>(
    <hljs prop>invoiceId</hljs>: $id,
    <hljs prop>paymentDate</hljs>: $date,
    // …
));
```

---

```php
final class CommandBus
{
    // …
    
    public function dispatch(<hljs type>object</hljs> $command): void
    {
        $handler = $this
            -><hljs prop>findHandlerForCommand</hljs>($command);

        $handler($command);
    }
}
```

---

```txt
<hljs blur>$bus-><hljs prop>dispatch</hljs>(</hljs><hljs keyword>new</hljs> <hljs type>PayInvoice</hljs>(
    <hljs prop>invoiceId</hljs>: $id,
    <hljs prop>paymentDate</hljs>: $date,
    // …
)<hljs blur>);</hljs>
```

---

```php
<hljs blur>final class CommandBus
{
    // …
    
    public function dispatch(<hljs type>object</hljs> $command): void
    {</hljs>
        $handler = $this
            -><hljs prop>findHandlerForCommand</hljs>($command);

        $handler($command);
<hljs blur>    }
}</hljs>
```

---

```php
public function moveToTile(<hljs type>Point</hljs> $point): Tile
{
    if (! $this-><hljs prop>getTile</hljs>($point)) {
        <hljs prop>command</hljs>(new <hljs type>GenerateTile</hljs>($point);
    }

    $tile = $this-><hljs prop>getTile</hljs>($point);
    
    // …
}
```

---

```php
final <hljs keyword>readonly</hljs> class SpawnArtifact
{
    use <hljs type>Serializable</hljs>;
    
    public function __construct(
        <hljs keyword>public</hljs> ?<hljs type>Point</hljs> <hljs prop>$point</hljs> = <hljs keyword>null</hljs>,
    ) {}
}

```
---

```php
public function dispatch(<hljs type>object</hljs> $command): void
{
    // …
    
    if ($handler instanceof <hljs type>HttpHandler</hljs>) {
        // …
    } else {
        $handler($command);
    }
}
```

---