<?php

namespace App\Books;

use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final class BooksController
{
    #[Get('/books')]
    public function mail(): View
    {
        return \Tempest\View\view('books-overview.view.php');
    }
}