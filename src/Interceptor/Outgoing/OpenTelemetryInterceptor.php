<?php

declare(strict_types=1);

namespace Shared\gRPC\Interceptor\Outgoing;

use Shared\gRPC\RequestContext;
use Psr\Container\ContainerInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Telemetry\TraceKind;
use Spiral\Telemetry\TracerInterface;

final class OpenTelemetryInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        $tracer = $this->container->get(TracerInterface::class);
        \assert($tracer instanceof TracerInterface);

        if (isset($parameters['ctx']) and $parameters['ctx'] instanceof RequestContext) {
            $parameters['ctx'] = $parameters['ctx']->withTelemetry($tracer->getContext());
        }

        return $tracer->trace(
            name: \sprintf('GRPC request %s', $action),
            callback: fn () => $core->callAction($controller, $action, $parameters),
            attributes: compact('controller', 'action'),
            traceKind: TraceKind::PRODUCER
        );
    }
}
