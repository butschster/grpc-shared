<?php

declare(strict_types=1);

namespace Shared\gRPC\Service;

final class NullServiceLocator implements ServiceLocatorInterface
{
    public function getServices(): array
    {
        return [];
    }
}
