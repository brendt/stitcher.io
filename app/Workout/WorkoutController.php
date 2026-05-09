<?php

namespace App\Workout;

use Tempest\DateTime\DateTime;
use Tempest\Http\Session\Session;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class WorkoutController
{
    public function __construct(
        private Session $session,
    ) {}

    #[Get('/workout')]
    public function index(): View
    {
        return view(
            'workout.view.php',
            workout: $this->resolveWorkout(),
        );
    }

    #[Post('/workout/start')]
    public function start(): View
    {
        $workout = new Workout(DateTime::now());

        $this->storeWorkout($workout);

        return $this->render();
    }

    #[Post('/workout/pause')]
    public function pause(): View
    {
        $workout = $this->resolveWorkout();

        if (! $workout) {
            return $this->render();
        }

        $workout->pause();

        $this->storeWorkout($workout);

        return $this->render();
    }

    #[Post('/workout/resume')]
    public function resume(): View
    {
        $workout = $this->resolveWorkout();

        $workout->resume();

        $this->storeWorkout($workout);

        return $this->render();
    }

    #[Post('/workout/stop')]
    public function stop(): View
    {
        $this->removeWorkout();

        return $this->render();
    }

    #[Post('/workout/ping')]
    public function ping(): View
    {
        $workout = $this->resolveWorkout();

        if ($workout?->hasEnded) {
            $this->removeWorkout();
        }

        if ($workout->isPaused) {
            $workout->updatePauseTime();
        }

        return $this->render();
    }

    private function render(): View
    {
        return view(
            'x-workout.view.php',
            workout: $this->resolveWorkout(),
        );
    }

    private function storeWorkout(Workout $workout): void
    {
        $this->session->set('workout', $workout);
    }

    private function removeWorkout(): void
    {
        $this->session->remove('workout');
    }

    private function resolveWorkout(): ?Workout
    {
//        return new Workout(DateTime::now()->minusSeconds(6));
        return $this->session->get('workout');
    }
}