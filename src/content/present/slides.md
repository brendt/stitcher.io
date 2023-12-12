
```txt
[20, 25]
```

---

```txt
<hljs blur>[20, 25]</hljs>

20, 21, 22, 23, 24, 25
```

---

```txt
<hljs blur>[20, 25]</hljs>

<hljs blur>20, 21, </hljs>22<hljs blur>, 23, 24, 25</hljs>
```

---

```txt
<hljs blur>[20, 25]</hljs>

<hljs blur>20, 21, 22, 23, 24, </hljs>25
```

---

```txt
[2023-12-20, 2023-12-25]
```

---

```txt
<hljs blur>[2023-12-20, 2023-12-25]</hljs>

<hljs blur>12-20, 12-21, </hljs>12-22<hljs blur>, 12-23, 
12-24, 12-25</hljs>
```

---

```txt
<hljs blur>[2023-12-20, 2023-12-25]</hljs>

<hljs blur>12-20, 12-21, 12-22, 12-23, 
12-24, </hljs>12-25
```

---



```txt
<hljs blur>[20, 25]</hljs>

25.5 ?
```

---

```txt
[20, 25]

<hljs blur>25.5 ?</hljs>
```

---

```txt
[20.00, 25.99]

<hljs blur>25.5 ?</hljs>
```

---

```txt
[2023-12-20, 2023-12-25]

<hljs blur>12-20, 12-21, 12-22, 12-23, 
12-24, 12-25</hljs>
```

---

```txt
[2023-12-20, 2023-12-26)

<hljs blur>12-20, 12-21, 12-22, 12-23, 
12-24, 12-25</hljs>
```

---

```php
$period = new <hljs type>DatePeriod</hljs>(
    <hljs prop>start</hljs>: new <hljs type>DateTimeImmutable</hljs>('2023-12-20'),
    <hljs prop>interval</hljs>: new <hljs type>DateInterval</hljs>('P1D'),
    <hljs prop>end</hljs>: new <hljs type>DateTimeImmutable</hljs>('2023-12-25'),
);

<hljs blur>foreach ($period as $day) {
    echo $day-><hljs prop>format</hljs>('Y-m-d');
}

// 12-20, 12-21, 12-22, 12-23, 12-24</hljs>
```
---

```php
<hljs blur>$period = new <hljs type>DatePeriod</hljs>(
    <hljs prop>start</hljs>: new <hljs type>DateTimeImmutable</hljs>('2023-12-20'),
    <hljs prop>interval</hljs>: new <hljs type>DateInterval</hljs>('P1D'),
    <hljs prop>end</hljs>: new <hljs type>DateTimeImmutable</hljs>('2023-12-25'),
);</hljs>

foreach ($period as $day) {
    echo $day-><hljs prop>format</hljs>('Y-m-d');
}

// 12-20, 12-21, 12-22, 12-23, 12-24
```

---

```php
<hljs blur>$period = new <hljs type>DatePeriod</hljs>(
    <hljs prop>start</hljs>: new <hljs type>DateTimeImmutable</hljs>('2023-12-20'),
    <hljs prop>interval</hljs>: new <hljs type>DateInterval</hljs>('P1D'),
    <hljs prop>end</hljs>: new <hljs type>DateTimeImmutable</hljs>('2023-12-25'),</hljs>
    <hljs prop>options</hljs>: <hljs type>DatePeriod</hljs>::<hljs prop>INCLUDE_END_DATE</hljs>,
<hljs blur>);</hljs>
```

---

```txt
[2023-12-20, 2023-12-25]

<hljs blur>12-20, 12-21, 12-22, 12-23, 
12-24, 12-25</hljs>
```

---

```txt
<hljs blur>[2023-12-20, 2023-12-25]</hljs>

<hljs blur>12-20, 12-21, 12-22, 12-23, 
12-24,</hljs> 12-25
```

---

```txt
[2023-12-20 00:00:00, 2023-12-25 10:38:23]
```