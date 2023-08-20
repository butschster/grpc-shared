<?php

declare(strict_types=1);

namespace Tests\Config;

use Shared\gRPC\Config\GRPCServicesConfig;
use Tests\TestCase;

final class GRPCServicesConfigTest extends TestCase
{
    public function testGetCredentials(): void
    {
        $config = new GRPCServicesConfig();

        $this->assertNull(
            actual: $config->getDefaultCredentials()
        );
    }

    public function testGetInterceptors(): void
    {
        $config = new GRPCServicesConfig();

        $this->assertIsArray(
            actual: $config->getInterceptors()
        );
    }

    public function testGetService(): void
    {
        $config = new GRPCServicesConfig();

        $this->assertSame([
            'host' => 'localhost'
        ], $config->getService('service'));
    }
}
