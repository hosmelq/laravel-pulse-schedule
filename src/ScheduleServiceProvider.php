<?php

declare(strict_types=1);

namespace HosmelQ\Laravel\Pulse\Schedule;

use HosmelQ\Laravel\Pulse\Schedule\Livewire\Schedule;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Pulse;
use Livewire\LivewireManager;
use Override;

class ScheduleServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'pulse-schedule');

        $this->callAfterResolving('livewire', function (LivewireManager $livewire): void {
            $livewire->component('pulse.schedule', Schedule::class);
        });
    }

    /**
     * @return array<class-string>
     */
    #[Override]
    public function provides(): array
    {
        return [Pulse::class];
    }
}
