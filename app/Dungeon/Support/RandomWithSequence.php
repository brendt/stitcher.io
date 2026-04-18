<?php

namespace App\Dungeon\Support;

use Closure;
use Exception;

final class RandomWithSequence implements Random
{
    public function __construct(
        private array $chanceSequence = [],
        private ?Closure $defaultChance = null,
        private array $itemSequence = [],
        private ?Closure $defaultItem = null,
        private array $chanceLog = [],
        private array $itemLog = [],
    ) {}

    public function chance(float $percentage): bool
    {

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        if (isset($trace[0]['file'])) {
            $this->chanceLog[] = $trace[0]['file'] . ':' . $trace[0]['line'];
        }

        $next = array_shift($this->chanceSequence);

        if ($next) {
            return $next;
        }

        if ($this->defaultChance) {
            return ($this->defaultChance)($percentage);
        }

        $logAsString = json_encode($this->chanceLog, JSON_PRETTY_PRINT);

        throw new Exception(
            "No more chances in the sequence to generate a random chance"
            . PHP_EOL
            . 'Called by:'
            . PHP_EOL
            . $logAsString,
        );
    }

    public function withChances(bool ...$chances): self
    {
        $this->chanceSequence = $chances;

        return $this;
    }

    public function withDefaultChance(Closure $defaultChance): self
    {
        $this->defaultChance = $defaultChance;

        return $this;
    }

    public function item(array $items): mixed
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        if (isset($trace[0]['file'])) {
            $this->itemLog[] = $trace[0]['file'] . ':' . $trace[0]['line'];
        }

        $next = array_shift($this->itemSequence);

        if ($next) {
            return $next;
        }

        if ($this->defaultItem) {
            return ($this->defaultItem)($items);
        }

        $logAsString = json_encode($this->itemLog, JSON_PRETTY_PRINT);

        throw new Exception(
            "No more items in the sequence to generate a random item"
            . PHP_EOL
            . 'Called by:'
            . PHP_EOL
            . $logAsString,
        );
    }

    public function withItems(array $items): self
    {
        $this->itemSequence = $items;

        return $this;
    }

    public function withDefaultItem(Closure $defaultItem): self
    {
        $this->defaultItem = $defaultItem;

        return $this;
    }
}
