<?php

declare(strict_types=1);

namespace HosmelQ\Laravel\Pulse\Schedule\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use WithWorkbench;
}
