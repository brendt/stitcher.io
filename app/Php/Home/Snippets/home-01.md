```php
#[Get('/php/{slug:.*}')]
public function show(string $slug): View|Redirect
{
    if (is_dir(__DIR__ . '/md/' . $slug)) {
        return $this->directory($slug);
    } elseif (is_file(__DIR__ . '/md/' . $slug . '.md')) {
        return $this->file($slug);
    }

    return new Redirect('/php');
}
```