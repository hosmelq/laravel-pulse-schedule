<?php

declare(strict_types=1);

namespace HosmelQ\Laravel\Pulse\Schedule\Livewire;

use function Safe\json_decode;

use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;
use Override;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\BufferedOutput;

#[Lazy]
class Schedule extends Card
{
    public function render(): View
    {
        $output = new BufferedOutput(formatter: new class () extends OutputFormatter {
            #[Override]
            public function format(null|string $message): null|string
            {
                return $message;
            }
        });

        Artisan::call('schedule:list', ['--json' => true], $output);

        /** @var list<array{command: string, expression: string, next_due_date_human: string, repeat_seconds: null|int}> $tasks */
        $tasks = json_decode($output->fetch(), true);
        $events = collect($tasks)->map(function (array $task): array {
            $command = $task['command'];
            $arguments = '';

            if (str_starts_with($command, 'php artisan ')) {
                $parts = explode(' ', $command, 4);
                $command = implode(' ', array_slice($parts, 0, 3));
                $arguments = $parts[3] ?? '';
            }

            return [
                'arguments' => $arguments,
                'command' => $command,
                'expression' => $task['expression'],
                'next_due' => $task['next_due_date_human'],
                'repeat_expression' => $task['repeat_seconds'] === null
                    ? ''
                    : $task['repeat_seconds'].'s',
            ];
        });

        return view('pulse-schedule::livewire.schedule', [
            'events' => $events,
        ]);
    }
}
