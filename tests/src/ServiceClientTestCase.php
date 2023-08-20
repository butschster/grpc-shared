<?php

declare(strict_types=1);

namespace Tests;

use Google\Protobuf\Internal\Message;
use Shared\gRPC\Exception\ResponseException;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Core\CoreInterface;
use Spiral\RoadRunner\GRPC\Context;
use Spiral\RoadRunner\GRPC\StatusCode;

abstract class ServiceClientTestCase extends TestCase
{
    private CoreInterface|m\MockInterface $core;

    public function getCore(): m\MockInterface|CoreInterface
    {
        return $this->core;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->core = m::mock(CoreInterface::class);
        $this->client = $this->makeClient();
    }

    abstract protected function makeClient(): object;

    abstract public static function methodsDataProvider(): \Generator;

    #[DataProvider(methodName: 'methodsDataProvider')]
    public function testMethodsCall(string $action, Message $request, Message $response): void
    {
        $client = $this->makeClient();

        $context = new Context([]);

        $this->getCore()->shouldReceive('callAction')
            ->once()
            ->with($client::class, '/' . $client::NAME . '/' . $action, [
                'ctx' => $context,
                'in' => $request,
                'responseClass' => $response::class,
            ])
            ->andReturn([$response, $this->createStatus()]);

        $this->assertSame($response, \call_user_func_array([$client, $action], [$context, $request]));
    }

    #[DataProvider(methodName: 'methodsDataProvider')]
    public function testMethodsCallWithBadStatus(string $action, Message $request, Message $response): void
    {
        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage('Not found');

        $this->getCore()->shouldReceive('callAction')
            ->once()
            ->andReturn([$response, $this->createStatus(StatusCode::NOT_FOUND, 'Not found')]);

        \call_user_func_array([$this->makeClient(), $action], [new Context([]), $request]);
    }

    /**
     * @param positive-int $code
     * @param non-empty-string $message
     */
    protected function createStatus(int $code = StatusCode::OK, string $message = 'OK'): \stdClass
    {
        $status = new \stdClass();
        $status->code = $code;
        $status->details = $message;
        $status->metadata = [];

        return $status;
    }
}
