<?php

declare(strict_types=1);

namespace Shared\gRPC\Interceptor\Outgoing;

use Google\Protobuf\Internal\Message;
use Shared\gRPC\Attribute\Guarded;
use Shared\gRPC\RequestContext;
use Shared\gRPC\Services\Auth\v1\DTO\User;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;

final class TokenExtractorInterceptor implements CoreInterceptorInterface
{
    /** @var array<non-empty-string, \ReflectionMethod> */
    private array $cache = [];

    public function __construct(
        private readonly ReaderInterface $reader,
    ) {
    }

    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        $message = $parameters['in'];
        \assert($message instanceof Message);

        $context = $parameters['ctx'];
        \assert($context instanceof RequestContext);

        // Here we cache the result of the check for the presence of the Guarded attribute
        // It is help to improve performance and don't use reflection for each request
        $cacheKey = \sprintf('%s::%s', $controller, $message::class);
        if (isset($this->cache[$cacheKey])) {
            $guarded = $this->cache[$cacheKey];
        } else {
            $reflMethod = $this->getMethod($controller, $message);
            /** @var Guarded|null $guard */
            $guard = $this->reader->firstFunctionMetadata($reflMethod, Guarded::class);
            $guarded = $this->cache[$cacheKey] = ($guard !== null);
        }

        if ($guarded) {
            // TODO: переделать на интерсепторы для ServiceServerTrait
            /** @var User $user */
            $user = \method_exists($message, 'getUser') ? $message->getUser() : new User();
            $parameters['ctx'] = $context->withToken($user->getToken());
        }

        return $core->callAction($controller, $action, $parameters);
    }

    public function getMethod(string $controller, Message $message): \ReflectionMethod
    {
        $refl = new \ReflectionClass($controller);

        // We need to check all interfaces because the Guarded attribute is placed on the interface
        foreach ($refl->getInterfaces() as $interface) {
            foreach ($interface->getMethods() as $method) {
                foreach ($method->getParameters() as $parameter) {
                    $methodRequestClass = $parameter->getType()->getName();
                    if ($message instanceof $methodRequestClass) {
                        return $method;
                    }
                }
            }
        }

        throw new \RuntimeException(\sprintf('Method for message `%s` not found', $message::class));
    }
}
