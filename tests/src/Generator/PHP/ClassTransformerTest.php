<?php

declare(strict_types=1);

namespace Tests\Generator\PHP;

use Generator\PHP\ClassTransformer;
use Tests\TestCase;

final class ClassTransformerTest extends TestCase
{
    public function testGetShortName(): void
    {
        $transformer = new ClassTransformer(
            class: '\Shared\\gRPC\\Services\\Command\\Create\\CreateCommand'
        );

        $this->assertSame(
            'CreateCommand',
            $transformer->getShortName()
        );
    }

    public function testGetNamespace(): void
    {
        $transformer = new ClassTransformer(
            class: '\Shared\\gRPC\\Services\\Command\\Create\\CreateCommand'
        );

        $this->assertSame(
            'Shared\\gRPC\\Services\\Command\\Create',
            $transformer->getNamespace()
        );
    }

    public function testCleanNamespace(): void
    {
        $transformer = new ClassTransformer(
            class: 'Shared\\gRPC\\Services\\Command\\Create\\CreateCommand'
        );

        $this->assertSame(
            'Command\\Create\\CreateCommand',
            $transformer->cleanNamespace()->class
        );
    }

    public function testReplaceNamespace(): void
    {
        $transformer = new ClassTransformer(
            class: 'Shared\\gRPC\\Services\\Command\\Create\\CreateCommand'
        );

        $this->assertSame(
            'App\\Command\\Command\\Create\\CreateCommand',
            $transformer->cleanNamespace('App\\Command')->class
        );
    }

    public function testGetDirectoryPath(): void
    {
        $transformer = new ClassTransformer(
            class: 'Command\\Create\\CreateCommand'
        );

        $this->assertSame(
            'Command/Create',
            $transformer->getDirectoryPath()
        );
    }

    public function testGetFilePath(): void
    {
        $transformer = new ClassTransformer(
            class: 'Command\\Create\\CreateCommand'
        );

        $this->assertSame(
            'Command/Create/CreateCommand.php',
            $transformer->getFilePath()
        );

        $this->assertSame(
            'Command/Create/CreateCommandMapper.php',
            $transformer->getFilePath('Mapper')
        );
    }

    public function testToString(): void
    {
        $transformer = new ClassTransformer(
            class: $string = 'Shared\\gRPC\\Services\\Command\\Create\\CreateCommand'
        );

        $this->assertSame(
            $string,
            (string) $transformer
        );
    }
}
