<?php

declare(strict_types=1);

namespace Medas\TestSupport;

use Medas\Core\{AsSingleton, BasePackage, Interfaces};
use Medas\HttpClient\Interfaces\RequestController;

/**
 * The test-support service package. Register it from the app's test bootstrap via
 * the TEST_PACKAGES env var (see ServiceConfigBuilder::addTestPackages) - never as
 * a normal package, since its bindings replace real services with test doubles.
 * isTestPackage() marks it accordingly.
 *
 * Currently, it binds the recording outgoing-HTTP stub, so tests never make real
 * external calls, without that binding ever reaching the on-disk service-config
 * cache that dev and production share.
 */
class TestSupportPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [];
    }

    public function isTestPackage(): bool
    {
        return true;
    }

    public function initialize(Interfaces\ServiceConfigBuilder $config): void
    {
        // Route all outgoing HTTP through the recording stub so tests never make
        // real external calls.
        $config->addTypeBinding(
            RecordingRequestController::class,
            RequestController::class,
        );

        parent::initialize($config);
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
