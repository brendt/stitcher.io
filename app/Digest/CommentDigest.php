<?php

namespace App\Digest;

use Tempest\Database\IsDatabaseModel;
use Tempest\DateTime\DateTime;

final class CommentDigest
{
    use IsDatabaseModel;

    public DateTime $createdAt;
}