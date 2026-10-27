<?php

declare(strict_types=1);

namespace Medas\TestSupport\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class PrintRequestTrace implements ConfigOption
{
    public function __construct(
        private TestSupportGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'print-request-trace';
    }

    public function description(): string
    {
        return 'Whether a failing functional test prints the debug events of the requests it made '
            . '(which flush committed what, which listener fired)';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): false
    {
        return false;
    }
}
