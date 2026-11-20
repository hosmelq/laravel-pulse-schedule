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

it('lists tasks too long to fit on one line', function (): void {
    Scheduler::command('inspire --'.str_repeat('a', 150))->hourly();
    Scheduler::call(fn (): null => null)->hourly();

    Livewire::withoutLazyLoading()
        ->test(Schedule::class)
        ->assertViewHas(
            'events',
            fn ($events): bool => $events->pluck('arguments')->contains('--'.str_repeat('a', 150))
                && $events->pluck('command')->contains(
                    fn (string $command): bool => str_starts_with($command, 'Closure at:'),
                ),
        );
});

it('lists tasks scheduled with month and weekday names', function (): void {
    Scheduler::command('inspire')->cron('0 1 * JAN MON');

    Livewire::withoutLazyLoading()
        ->test(Schedule::class)
        ->assertViewHas(
            'events',
            fn ($events): bool => $events->pluck('expression')->all() === ['0 1 * JAN MON'],
        );
});

it('lists commands that contain console markup as written', function (): void {
    Scheduler::exec('printf \\<ok')->hourly();
    Scheduler::exec('echo <info>done</info>')->daily();

    Livewire::withoutLazyLoading()
        ->test(Schedule::class)
        ->assertViewHas('events', fn ($events): bool => $events->pluck('command')->all() === [
            'printf \\<ok',
            'echo <info>done</info>',
        ]);
});

it('renders one row per task when tasks share a command', function (): void {
    Scheduler::command('inspire --first')->hourly();
    Scheduler::command('inspire --second')->daily();

    Livewire::withoutLazyLoading()
        ->test(Schedule::class)
        ->assertSeeHtmlInOrder(['wire:key="0-row"', '--first', 'wire:key="1-row"', '--second']);
});

it('shows no results when nothing is scheduled', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Schedule::class)
        ->assertViewHas('events', fn ($events): bool => $events->isEmpty())
        ->assertSeeText('No results')
        ->assertDontSeeText('Next Due');
});

it('registers the card as a Livewire component', function (): void {
    Livewire::withoutLazyLoading()->test('pulse.schedule')->assertOk();
});
