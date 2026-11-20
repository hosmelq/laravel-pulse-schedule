<?php

declare(strict_types=1);

use HosmelQ\Laravel\Pulse\Schedule\Livewire\Schedule;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Schedule as Scheduler;
use Livewire\Livewire;

it('lists scheduled commands with their arguments and expression', function (): void {
    Date::setTestNow('2026-01-01 15:00:00');

    Scheduler::command('inspire --verbose')->dailyAt('03:00');

    Livewire::withoutLazyLoading()
        ->test(Schedule::class)
        ->assertViewHas('events', fn ($events): bool => $events->values()->all() === [[
            'arguments' => '--verbose',
            'command' => 'php artisan inspire',
            'expression' => '0 3 * * *',
            'next_due' => '12 hours from now',
            'repeat_expression' => '',
        ]])
        ->assertSeeText('php artisan inspire')
        ->assertSeeText('--verbose')
        ->assertSeeText('0 3 * * *');
});

it('lists sub-minute tasks with their repeat expression', function (): void {
    Scheduler::command('inspire')->everyThirtySeconds();

    Livewire::withoutLazyLoading()
        ->test(Schedule::class)
        ->assertViewHas('events', fn ($events): bool => $events->count() === 1
            && $events->first()['command'] === 'php artisan inspire'
            && $events->first()['expression'] === '* * * * *'
            && $events->first()['repeat_expression'] === '30s')
        ->assertSeeText('30s');
});

it('registers the card as a Livewire component', function (): void {
    Livewire::withoutLazyLoading()->test('pulse.schedule')->assertOk();
});
