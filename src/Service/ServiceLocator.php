<?php

declare(strict_types=1);

namespace Shared\gRPC\Service;

use Shared\gRPC\Attribute\ServiceClient;
use Psr\Container\ContainerInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\RoadRunner\GRPC\ServiceInterface;
use Spiral\RoadRunnerBridge\GRPC\LocatorInterface;
use Spiral\Tokenizer\ClassesInterface;

final readonly class ServiceLocator implements ServiceLocatorInterface, LocatorInterface
{
    public function __construct(
        private ClassesInterface $classes,
        private ContainerInterface $container,
    ) {
    }

    public function getServices(): array
    {
        $result = [];

        foreach ($this->classes->getClasses(ServiceInterface::class) as $service) {
            if (!$service->isInstantiable()) {
                continue;
            }

            if ($service->getAttributes(ServiceClient::class) !== []) {
                continue;
            }

            try {
                $instance = $this->container->get($service->getName());
            } catch (ContainerException) {
                continue;
            }

            foreach ($service->getInterfaces() as $interface) {
                if ($interface->isSubclassOf(ServiceInterface::class)) {
                    $result[$interface->getName()] = $instance;
                }
            }
        }

        return $result;
    }
}
