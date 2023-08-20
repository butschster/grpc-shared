<?php

declare(strict_types=1);

namespace Shared\gRPC\Interceptor\Incoming;

use CQRS\CommandBusInterface;
use Shared\gRPC\Attribute\Guarded;
use Shared\gRPC\Command\Auth\v1\Request\GetUserByTokenRequest;
use Shared\gRPC\CommandMapper;
use Shared\gRPC\RequestContext;
use Shared\gRPC\Services\Auth\v1\DTO\User;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\RoadRunner\GRPC\ContextInterface;
use Spiral\RoadRunner\GRPC\Exception\UnauthenticatedException;
use Spiral\RoadRunner\GRPC\ServiceInterface;

final class GuardInterceptor implements CoreInterceptorInterface
{
    /** @var array<non-empty-string, Guarded> */
    private array $cached = [];

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly CommandBusInterface $bus,
        private readonly CommandMapper $mapper,
    ) {
    }

    /**
     * Check auth token for service methods with Guarded attribute.
     *
     * @param array{service: ServiceInterface, ctx: ContextInterface, input: string} $parameters
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        $cacheKey = $controller . '::' . $action;

        if (!isset($this->cached[$cacheKey])) {
            $guard = $this->findAttribute($controller, $action);
            $this->cached[$cacheKey] = $guard;
        } else {
            $guard = $this->cached[$cacheKey];
        }

        if ($guard !== null) {
            $user = $this->checkGuard($guard, $parameters['ctx']);
            $parameters['ctx'] = $parameters['ctx']->withValue(User::class, $user);
        }

        return $core->callAction($controller, $action, $parameters);
    }

    /**
     * @throws UnauthenticatedException
     */
    private function checkGuard(?Guarded $guard, RequestContext $ctx): User
    {
        $token = $ctx->getToken($guard->tokenField);

        if (!$token) {
            throw new UnauthenticatedException('token_is_missing');
        }

        $response = $this->bus->dispatch(
            new GetUserByTokenRequest($token),
        );

        return $this->mapper->toMessage($response->user);
    }


    public function findAttribute(string $controller, string $action): ?Guarded
    {
        $refl = new \ReflectionClass($controller);
        $classes = [$refl, ...$refl->getInterfaces()];

        // We need to check all interfaces because the Guarded attribute is placed on the interface
        foreach ($classes as $class) {
            try {
                $method = $class->getMethod($action);
            } catch (\ReflectionException $e) {
                continue;
            }

            if ($guard = $this->reader->firstFunctionMetadata($method, Guarded::class)) {
                return $guard;
            }
        }

        return null;
    }
}
