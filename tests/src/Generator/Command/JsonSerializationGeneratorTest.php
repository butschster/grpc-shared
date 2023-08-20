<?php

declare(strict_types=1);

namespace Tests\Generator\Command;

use Generator\Generators\Command\JsonSerializationGenerator;
use Generator\Generators\Command\PropertyType;
use Generator\PHP\ClassDeclarationFactory;
use Generator\PHP\Property\Type;
use Generator\PHP\Property\TypeFactory;
use Spiral\Files\FilesInterface;
use Tests\Fixtures\SimpleMessage;
use Tests\InMemoryFiles;
use Tests\TestCase;

final class JsonSerializationGeneratorTest extends TestCase
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

    public function testGenerateWithoutProperties(): void
    {
        $generator = new JsonSerializationGenerator();

        $generator->generate($this->declaration, []);
        $this->declaration->persist();

        $this->files->assertFileContentContains(
            $this->declaration->filePath,
            <<<'PHP'
    public function jsonSerialize(): array
    {
        return [];
    }
PHP
        );
    }

    public function testGenerateWithProperties(): void
    {
        $generator = new JsonSerializationGenerator();

        $generator->generate($this->declaration, [
            new PropertyType(
                variable: 'id',
                types: $this->buildTypes('int|string|DateTimeInterface'),
                comment: 'Some comment'
            ),
            new PropertyType(
                variable: 'message',
                types: $this->buildTypes(SimpleMessage::class),
            ),
            new PropertyType(
                variable: 'project_id',
                types: $this->buildTypes('int'),
            ),
            new PropertyType(
                variable: 'expires_at',
                types: $this->buildTypes('string'),
                annotations: [
                    new \Generator\Generators\Command\Annotation\Type('DateTimeInterface')
                ]
            ),
        ]);

        $this->declaration->persist();

        $this->files->assertFileContentContains(
            $this->declaration->filePath,
            <<<'PHP'
    public function jsonSerialize(): array
    {
        $data = [];
        $data['id'] = $this->id?->format(\DateTimeInterface::RFC3339);
        $data['message'] = $this->message;
        $data['project_id'] = $this->projectId;
        $data['expires_at'] = $this->expiresAt?->format(\DateTimeInterface::RFC3339);
        return $data;
    }
PHP
        );
    }

    /**
     * @return Type[]
     */
    public function buildTypes(string $type): array
    {
        $factory = new TypeFactory();
        $type = \explode('|', $type);
        return \array_map(fn(string $type): Type => $factory->create($type), $type);
    }
}
