<?php

namespace App\Books;

use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\view;

final class BooksController
{
    #[Get('/books')]
    public function mail(): View
    {
        return view('book.view.php');
    }
}