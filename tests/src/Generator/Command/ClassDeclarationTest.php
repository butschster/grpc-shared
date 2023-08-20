<?php

declare(strict_types=1);

namespace Tests\Generator\Command;

use Generator\PHP\ClassDeclarationFactory;
use Spiral\Files\FilesInterface;
use Tests\Fixtures\SimpleMessage;
use Tests\InMemoryFiles;
use Tests\TestCase;

final class ClassDeclarationTest extends TestCase
{
    private FilesInterface|InMemoryFiles $files;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = $this->fakeFiles([
            SimpleMessage::class,
        ]);

        $this->declaration = (new ClassDeclarationFactory($this->files))
            ->createFromExistsClass(SimpleMessage::class);
    }

    public function testGetName(): void
    {
        $this->assertSame('SimpleMessage', $this->declaration->getName());
    }

    public function testGetNameWithNamespace(): void
    {
        $this->assertSame('Tests\Fixtures\SimpleMessage', $this->declaration->getNameWithNamespace());
    }

    public function testGetNamespace(): void
    {
        $this->assertSame('Tests\Fixtures', $this->declaration->getNamespace());
    }

    public function testIsClassNameEndsWith(): void
    {
        $this->assertTrue($this->declaration->isClassNameEndsWith('Message'));
        $this->assertFalse($this->declaration->isClassNameEndsWith('Command'));
    }

    public function testMarkAsFinal(): void
    {
        $this->declaration->markAsFinal();
        $this->declaration->persist();

        $this->files->assertFileContentContains(
            $this->declaration->filePath,
            'final class SimpleMessage extends Message'
        );
    }

    public function testMarkAsReadonly(): void
    {
        $this->declaration->markAsReadonly();
        $this->declaration->persist();

        $this->files->assertFileContentContains(
            $this->declaration->filePath,
            'readonly class SimpleMessage extends Message'
        );
    }
}
