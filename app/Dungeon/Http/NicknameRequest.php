<?php

namespace App\Dungeon\Http;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use Tempest\Validation\Rules\HasLength;

final class NicknameRequest implements Request
{
    use IsRequest;

    #[HasLength(min: 3, max: 40)]
    public string $nickname;
}