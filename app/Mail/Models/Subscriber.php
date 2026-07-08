<?php

namespace App\Mail\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\DateTime\DateTime;

final class Subscriber
{
    use IsDatabaseModel;

    public string $uuid;
    public string $email;
    public string $name;
    public DateTime $subscribedAt;
    public ?DateTime $unsubscribedAt = null;
}
