<?php

declare(strict_types=1);

namespace HosmelQ\Pulse\Schedule\Livewire;

use function Safe\preg_replace;

use Closure;
use Cron\CronExpression;
use DateTimeZone;
use Exception;
use Illuminate\Console\Application;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule as IlluminateSchedule;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;
use ReflectionClass;
use ReflectionFunction;

#[Lazy]
class Schedule extends Card
{
    public function render(): View
    {
        if (! class_exists(\App\Console\Kernel::class)) {
            throw new Exception('Class App\Console\Kernel not found.');
        }

        resolve(\App\Console\Kernel::class);

        $timezone = new DateTimeZone(config('app.timezone')); // @phpstan-ignore-line
        $events = collect(resolve(IlluminateSchedule::class)->events())
            ->map(function (Event $event) use ($timezone): array {
                return [
                    'command' => $this->getCommand($event),
                    'expression' => $this->getExpression($event),
                    'next_due' => $this->getNextDueDateForEvent($event, $timezone)
                        ->diffForHumans(),
                ];
            });

        return view('pulse-schedule::livewire.schedule', [
            'events' => $events,
            'poll_interval' => config('pulse-schedule.poll_interval'),
        ]);
    }

    private function getClosureLocation(CallbackEvent $event): string
    {
        $callback = (new ReflectionClass($event))->getProperty('callback')->getValue($event);

        if ($callback instanceof Closure) {
            $function = new ReflectionFunction($callback);

            return sprintf(
                '%s:%s',
                str_replace(app()->basePath().DIRECTORY_SEPARATOR, '', $function->getFileName() ?: ''),
                $function->getStartLine()
            );
        }

        if (is_string($callback)) {
            return $callback;
        }

        if (is_array($callback)) {
            $className = is_string($callback[0]) ? $callback[0] : $callback[0]::class;

            return sprintf('%s::%s', $className, $callback[1]);
        }

        return sprintf('%s::__invoke', $callback::class); // @phpstan-ignore-line
    }

    private function getCommand(Event $event): string
    {
        $command = str_replace([Application::phpBinary(), Application::artisanBinary()], [
            'php',
            preg_replace("#['\"]#", '', Application::artisanBinary()),
        ], $event->command ?? '');

        if ($event instanceof CallbackEvent) {
            $command = $event->getSummaryForDisplay();

            if (in_array($command, ['Closure', 'Callback'], true)) {
                $command = 'Closure at: '.$this->getClosureLocation($event);
            }
        }

        return mb_strlen($command) > 1 ? "{$command} " : '';
    }

    private function getExpression(Event $event): string
    {
        if (! $event->isRepeatable()) {
            return $event->getExpression();
        }

        return "{$event->getExpression()} ({$event->repeatSeconds}s)";
    }

    private function getNextDueDateForEvent(Event $event, DateTimeZone $timezone): Carbon
    {
        $nextDueDate = Carbon::instance(
            (new CronExpression($event->expression))
                ->getNextRunDate(Carbon::now()->setTimezone($event->timezone))
                ->setTimezone($timezone)
        );

        if (! $event->isRepeatable()) {
            return $nextDueDate;
        }

        $previousDueDate = Carbon::instance(
            (new CronExpression($event->expression))
                ->getPreviousRunDate(Carbon::now()->setTimezone($event->timezone), allowCurrentDate: true)
                ->setTimezone($timezone)
        );

        $now = Carbon::now()->setTimezone($event->timezone);

        if (! $now->copy()->startOfMinute()->eq($previousDueDate)) {
            return $nextDueDate;
        }

        return $now
            ->endOfSecond()
            ->ceilSeconds($event->repeatSeconds); // @phpstan-ignore-line
    }
}
