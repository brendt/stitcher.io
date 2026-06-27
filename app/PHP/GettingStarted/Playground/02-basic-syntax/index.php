<?php

class Book
{
    private string $title;
    private Author $author;
    private int $categoryId;

    public function __construct(
        string $title,
        Author $author,
        int $categoryId,
    ) {
        if ($categoryId < 1 || $categoryId > 5) {
            throw new Exception("Category ID must be between 1 and 5.");
        }

        $this->title = $title;
        $this->author = $author;
        $this->categoryId = $categoryId;
    }
}

class Author
{
    private string $name;

    public function __construct(
        string $name,
    ) {
        $this->name = $name;
    }
}

$total = 20;
$failed = 0;

foreach (range(1, $total) as $i) {
    $author = new Author("Author {$i}");

    $categoryId = random_int(0, 10);

    try {
        $book = new Book("Book {$i}", $author, $categoryId);
    } catch (Exception $exception) {
        $failed += 1;
    }
}

echo "{$failed} out of {$total} books failed";