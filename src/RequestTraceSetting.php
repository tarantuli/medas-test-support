<?php

declare(strict_types=1);

namespace Medas\TestSupport;

use Medas\Core\Attributes\{ConfigValue, Service};

// Carries the print-request-trace option to FunctionalTestCase, which is a PHPUnit
// test case and not a service, so it cannot take a #[ConfigValue] itself.
#[Service]
readonly class RequestTraceSetting
{
    public function __construct(
        #[ConfigValue(ConfigOptions\PrintRequestTrace::class)]
        public bool $printsOnFailure,
    )
    {
    }
}
