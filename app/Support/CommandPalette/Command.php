<?php

namespace App\Support\CommandPalette;

use Override;
use JsonSerializable;

final readonly class Command implements JsonSerializable
{
    public function __construct(
        public string $title,
        public Type $type,
        public array $hierarchy,
        public ?string $uri = null,
        public ?string $javascript = null,
        public array $fields = [],
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type->value,
            'title' => $this->title,
            'uri' => $this->uri,
            'javascript' => $this->javascript,
            'hierarchy' => $this->hierarchy,
            'fields' => $this->fields,
        ];
    }
}
