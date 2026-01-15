<?php

namespace App\Workout;

use Tempest\DateTime\DateTime;
use function Tempest\Support\arr;

final class Workout
{
    private int $secondsPerPart = 30;
    private int $totalWorkoutTime = 60 * 15;

    private(set) array $exercises;

    private array $possibleExercises = [
        'March on the spot',
        'Squat',
        'Step + Punch',
        'Reverse lunge with arm raises',
        'Knee smashes',
        'Star Jumps',
        'Lateral squats',
        'Power knees (R)',
        'Power knees (L)',
        'Plank',
        'Push ups',
        'Lateral shuffle',
        'Light feet + straight punches',
        'Knee smashes',
        'Climb the Rope',
        'Squat to lateral kick',
        'Front kicks',
        'Running man',
        'Curtsy lunges',
        'Jog',
    ];

    private(set) ?DateTime $pauseTime = null;

    public function __construct(
        private(set) DateTime $startTime,
    )
    {
        $amount = $this->totalWorkoutTime / $this->secondsPerPart / 2;

        $this->exercises = arr($this->possibleExercises)
            ->shuffle()
            ->slice(0, $amount)
            ->toArray();
    }

    public DateTime $currentTime {
        get => DateTime::now();
    }

    public DateTime $endTime {
        get => $this->startTime->plusSeconds($this->totalWorkoutTime);
    }

    public string $currentExercise {
        get {
            if ($this->isPaused) {
                return 'Paused';
            }

            if ($this->isWarmup) {
                return 'Warmup';
            }

            if ($this->isBreak) {
                return 'Break';
            }

            return $this->exercises[$this->currentExerciseIndex];
        }
    }

    public string $nextExercise {
        get {
            return $this->exercises[$this->nextExerciseIndex];
        }
    }

    public int $timeRemainingInPart {
        get => $this->secondsPerPart - $this->timeElapsed % $this->secondsPerPart;
    }

    public int $currentExerciseIndex {
        get => $this->part % count($this->exercises);
    }


    public int $nextExerciseIndex {
        get => ($this->currentExerciseIndex + 1) % count($this->exercises);
    }

    public bool $isPaused {
        get => $this->pauseTime !== null;
    }

    public bool $isWarmup {
        get => ! $this->isPaused && $this->part === 1;
    }

    public bool $isExercise {
        get => ! $this->isPaused && $this->part % 2 === 0;
    }

    public bool $isBreak {
        get => ! $this->isPaused && ! $this->isExercise;
    }

    public int $part {
        get => floor($this->timeElapsed / $this->secondsPerPart) + 1;
    }

    public int $totalTime {
        get => $this->endTime->getTimestamp()->getSeconds() - $this->startTime->getTimestamp()->getSeconds();
    }

    public int $timeLeft {
        get => $this->endTime->getTimestamp()->getSeconds() - $this->currentTime->getTimestamp()->getSeconds();
    }

    public int $timeElapsed {
        get => $this->totalTime - $this->timeLeft;
    }

    public bool $hasEnded {
        get => $this->currentTime->after($this->endTime);
    }

    public float $progress {
        get => round($this->timeElapsed / $this->totalTime * 100, 3);
    }

    public function pause(): self
    {
        $this->pauseTime = DateTime::now();

        return $this;
    }

    public function resume(): self
    {
        $this->pauseTime = null;

        return $this;
    }

    public function updatePauseTime(): self
    {
        $now = DateTime::now();

        $delta = $now->getTimestamp()->getSeconds() - $this->pauseTime->getTimestamp()->getSeconds();
        $this->pauseTime = $now;
        $this->startTime = $this->startTime->plusSeconds($delta);

        return $this;
    }
}