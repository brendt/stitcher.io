<?php

namespace App\Todo;

use App\Support\Authentication\Admin;
use Tempest\Router\Get;
use Tempest\Router\Prefix;
use Tempest\View\View;

use function Tempest\View\view;

#[Prefix('/todo'), Admin]
final readonly class TodoController
{
    #[Get('/')]
    public function index(): View
    {
        return view('todo.view.php');
    }
}
