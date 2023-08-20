<?php

declare(strict_types=1);

namespace Tests;

use CuyZ\Valinor\Mapper\TreeMapper;
use Shared\gRPC\CommandMapper;
use Shared\gRPC\Valinor\GrpcCommandMapperBuilder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    public function getFixturePath(string $filename): string
    {
        return __DIR__ . '/Fixtures/' . $filename;
    }

    public function fakeFiles(array $classes = []): FilesInterface|InMemoryFiles
    {
        $fileContents = [];
        $fileManager = new Files();

        foreach ($classes as $class) {
            $class = new \ReflectionClass($class);
            $fileContents[$class->getFileName()] = $fileManager->read($class->getFileName());
        }

        return new InMemoryFiles($fileContents);
    }

    public function makeCommandMapper(): CommandMapper
    {
        return new CommandMapper(
            \Mockery::mock(ReaderInterface::class),
            \Mockery::mock(FactoryInterface::class),
        );
    }

    public function makeTreeMapper(): TreeMapper
    {
        return (new GrpcCommandMapperBuilder())->build();
    }
}
