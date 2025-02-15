<?php

declare(strict_types=1);

namespace HosmelQ\Laravel\Pulse\Schedule\Livewire;

use Illuminate\Console\Scheduling\ScheduleListCommand;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;

#[Lazy]
class Schedule extends Card
{
    public function render(): View
    {
        ScheduleListCommand::resolveTerminalWidthUsing(fn (): int => 120);

        Artisan::call('schedule:list');

        ScheduleListCommand::resolveTerminalWidthUsing(null);

        $events = collect(explode("\n", Artisan::output()))
            ->filter()
            ->map(function (string $line): ?array {
                $pattern = '/^\s*([*0-9,-\/]+\s+[*0-9,-\/]+\s+[*0-9,-\/]+\s+[*0-9,-\/]+\s+[*0-9,-\/]+)(?:\s+(\d+s))?\s+(.+?)\s+\.+\s+Next Due:\s+(.+)$/';

                if (! preg_match($pattern, $line, $matches)) {
                    return null;
                }

                $command = $matches[3];

                if (str_starts_with($command, 'php artisan')) {
                    preg_match("#(php artisan [\w\-:]+)\s*(.+)?#", $command, $parts);

                    $command = $parts[1] ?? 'no';
                    $arguments = $parts[2] ?? '';
                } else {
                    $arguments = '';
                }

                return [
                    'arguments' => $arguments,
                    'command' => $command,
                    'expression' => trim($matches[1]),
                    'next_due' => trim($matches[4]),
                    'repeat_expression' => $matches[2] ?? null,
                ];
            })
            ->filter();

        return view('pulse-schedule::livewire.schedule', [
            'events' => $events,
        ]);
    }
}
