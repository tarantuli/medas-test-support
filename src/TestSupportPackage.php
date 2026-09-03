<?php

declare(strict_types=1);

namespace Medas\TestSupport;

use Medas\Core\{AsSingleton, BasePackage};

class TestSupportPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
