<?php

declare(strict_types=1);

namespace Tests\Testo\Support;

use Closure;
use Tempest\Container\Container;
use Tempest\Testing\Exceptions\TestHasFailed;
use Tempest\Testing\Testers\Console\ConsoleTester as TempestConsoleTester;
use Testo\Assert;

/**
 * Thin adapter over `Tempest\Testing\Testers\Console\ConsoleTester`.
 *
 * The Tempest tester signals failures by throwing `TestHasFailed` and keeps its exit code private,
 * so its assertions are re-published here as `Testo\Assert` records.
 */
final class ConsoleTester
{
    private TempestConsoleTester $tester;

    public function __construct(Container $container)
    {
        $this->tester = new TempestConsoleTester($container);
    }

    public function call(string|Closure|array $command, string|array $arguments = []): self
    {
        $clone = clone $this;
        $clone->tester = $this->tester->call($command, $arguments);

        return $clone;
    }

    public function succeeds(): self
    {
        return $this->assertThrough($this->tester->succeeds(...), 'console command succeeded');
    }

    public function fails(): self
    {
        return $this->assertThrough($this->tester->fails(...), 'console command failed');
    }

    public function contains(string $text): self
    {
        return $this->assertThrough(
            fn (): mixed => $this->tester->contains($text),
            sprintf('console output contains "%s"', $text),
        );
    }

    public function containsNot(string $text): self
    {
        return $this->assertThrough(
            fn (): mixed => $this->tester->containsNot($text),
            sprintf('console output does not contain "%s"', $text),
        );
    }

    public function submit(int|string $input = ''): self
    {
        $this->tester->submit($input);

        return $this;
    }

    public function withoutPrompting(): self
    {
        $this->tester->withoutPrompting();

        return $this;
    }

    /**
     * Runs a Tempest tester assertion and mirrors its outcome into Testo's assertion history.
     */
    private function assertThrough(Closure $assertion, string $description): self
    {
        try {
            $assertion();
        } catch (TestHasFailed $exception) {
            Assert::fail($exception->getMessage());
        }

        Assert::true(true, $description);

        return $this;
    }
}
