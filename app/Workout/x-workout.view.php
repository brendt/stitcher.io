<?php

use App\Workout\WorkoutController;
use function Tempest\Router\uri;

?>

<div
        :if="$workout === null"
        id="workout"
        class="w-full h-full bg-purple-50 flex justify-center items-center text-xl transition-all duration-250"
>
    <x-action-button
            class="p-2 px-4 bg-green-300 hover:bg-green-400 rounded font-bold text-green-900 text-2xl"
            :action="uri([WorkoutController::class, 'start'])"
            target="#workout"
    >start!
    </x-action-button>
</div>
<div
        :else
        id="workout"
        :hx-post="uri([WorkoutController::class, 'ping'])"
        hx-trigger="every 1s"
        class="
            w-full h-full text-2xl transition-all duration-250
            flex flex-col justify-between items-center
        "
        :class="$workout->isPaused ? 'bg-purple-50' : ($workout->isBreak ? 'bg-green-50' : 'bg-green-200')"
>
    <div class="flex flex-col gap-4 justify-center grow h-full items-center pt-8 px-4">
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center" :class="$workout->isPaused ? 'text-gray-500' : 'text-black'">
            <div class="font-bold text-4xl text-center">
                {{ $workout->currentExercise }}
            </div>

            <div class="font-mono p-2 px-4 rounded-lg shadow transition-all transition-200" :class="$workout->isPaused ? 'bg-gray-100 ' : 'bg-white'">
                {{ $workout->timeRemainingInPart }}s
            </div>
        </div>

        <div :if="$workout->isBreak" class="text-center">
            Next: {{ $workout->nextExercise }}
        </div>
    </div>

    <div class="relative w-full">
        <div class="absolute h-full bg-green-500 transition-all" style="width: {{ $workout->progress }}%;"></div>

        <div class="flex gap-4 p-4 relative w-full justify-center">
            <x-action-button
                    :if="!$workout->isPaused"
                    class="p-2 px-4 bg-purple-50 hover:bg-purple-200 rounded font-bold text-purple-900"
                    :action="uri([WorkoutController::class, 'pause'])"
                    target="#workout"
            >pause
            </x-action-button>
            <x-action-button
                    :else
                    class="p-2 px-4 bg-purple-300 hover:bg-purple-200 rounded font-bold text-purple-900"
                    :action="uri([WorkoutController::class, 'resume'])"
                    target="#workout"
            >resume
            </x-action-button>

<!--            <x-action-button-->
<!--                    class="p-2 px-4 bg-yellow-300 hover:bg-yellow-400 rounded font-bold text-yellow-900"-->
<!--                    :action="uri([WorkoutController::class, 'start'])"-->
<!--                    target="#workout"-->
<!--            >restart-->
<!--            </x-action-button>-->
            <x-action-button
                    class="p-2 px-4 bg-red-50 hover:bg-red-300 rounded font-bold text-red-900"
                    :action="uri([WorkoutController::class, 'stop'])"
                    target="#workout"
            >stop
            </x-action-button>
        </div>
    </div>
</div>