<?php

declare(strict_types=1);

namespace Tests;

use Shared\gRPC\RequestContext;

final class RequestContextTest extends TestCase
{
    public function testWithTelemetry(): void
    {
        $ctx = new RequestContext();
        $this->assertSame([], $ctx->getTelemetry());

        $headers = ['foo' => 'bar',];
        $this->assertSame(
            $headers,
            $ctx->withTelemetry($headers)->getTelemetry()
        );
    }

    public function testWithToken(): void
    {
        $ctx = new RequestContext();
        $this->assertNull($ctx->getToken());

        $this->assertSame(
            'secret-token',
            $ctx->withToken(token: 'secret-token')->getToken()
        );

        $this->assertNull(
            $ctx->withToken(token: 'secret-token')->getToken(key: 'token-field')
        );

        $this->assertNull(
            $ctx->withToken(token: 'secret-token', key: 'token-field')->getToken()
        );

        $this->assertSame(
            'secret-token',
            $ctx->withToken(token: 'secret-token', key: 'token-field')->getToken(key: 'token-field')
        );
    }
}
