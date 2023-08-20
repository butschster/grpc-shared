<?php

declare(strict_types=1);

namespace Tests\Interceptor\Incoming;

use Shared\gRPC\Interceptor\Incoming\ContextInterceptor;
use Shared\gRPC\RequestContext;
use Mockery as m;
use Spiral\Core\CoreInterface;
use Spiral\RoadRunner\GRPC\Context;
use Tests\TestCase;

final class ContextInterceptorTest extends TestCase
{
    public function testContextShouldBeWrappedIntoRequestContext(): void
    {
        $interceptor = new ContextInterceptor();

        $core = m::mock(CoreInterface::class);

        $core->shouldReceive('callAction')
            ->once()
            ->withArgs(function (string $controller, string $action, array $parameters) {
                return $controller = 'controller'
                    && $action = 'action'
                        && $parameters['ctx'] instanceof RequestContext
                        && $parameters['ctx']->getToken() === 'secret-token';
            });

        $interceptor->process('controller', 'action', [
            'ctx' => new Context([
                'token' => ['secret-token'],
            ]),
        ], $core);
    }
}
