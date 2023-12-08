<?php

declare(strict_types=1);

namespace HosmelQ\Pulse\Schedule;

use HosmelQ\Pulse\Schedule\Livewire\Schedule;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireManager;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'pulse-schedule');

        $this->callAfterResolving('livewire', function (LivewireManager $livewire): void {
            $livewire->component('pulse.schedule', Schedule::class);
        });
    }
}
