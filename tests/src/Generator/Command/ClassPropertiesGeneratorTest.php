<?php

declare(strict_types=1);

namespace Tests\Generator\Command;

use Generator\Generators\Command\ClassPropertiesGenerator;
use Generator\Generators\Command\PropertyType;
use Generator\PHP\ClassDeclarationFactory;
use Generator\PHP\Property\Type;
use Generator\PHP\Property\TypeFactory;
use Ramsey\Uuid\UuidInterface;
use Spiral\Files\Files;
use Tests\Fixtures\SimpleMessage;
use Tests\TestCase;

final class ClassPropertiesGeneratorTest extends TestCase
{
    public function testGenerateClassWithEmptyProperties(): void
    {
        $files = $this->fakeFiles();

        $class = (new ClassDeclarationFactory($files))->createFromClass(
            'Shared\\gRPC\\Services\\Command\\TestCommand',
        );

        $generator = new ClassPropertiesGenerator(new Files(), '', 'App\\Commands');

        $generator->generate($class, []);
        $class->persist();

        $files->assertFileContentSame(
            $class->filePath,
            <<<'PHP'
<?php

declare(strict_types=1);

namespace Shared\gRPC\Services\Command;

class TestCommand
{
}

PHP
        );
    }

    public function testGenerateClassWithProperties(): void
    {
        $files = $this->fakeFiles();

        $class = (new ClassDeclarationFactory($files))->createFromClass(
            'Shared\\gRPC\\Services\\Command\\TestCommand',
        );

        $generator = new ClassPropertiesGenerator(new Files(), '', 'App\\Commands');

        $generator->generate($class, [
            new PropertyType(
                variable: 'id',
                types: $this->buildTypes('int|string|' . \DateTimeInterface::class),
                comment: 'Some comment'
            ),
            new PropertyType(
                variable: 'message',
                types: $this->buildTypes(SimpleMessage::class)
            ),
            new PropertyType(
                variable: 'project_id',
                types: $this->buildTypes('int'),
                annotations: [
                    new \Generator\Generators\Command\Annotation\Type(UuidInterface::class)
                ]
            )
        ]);

        $class->persist();

        $files->assertFileContentSame(
            $class->filePath,
            <<<'PHP'
<?php

declare(strict_types=1);

namespace Shared\gRPC\Services\Command;

use DateTimeInterface;
use Ramsey\Uuid\UuidInterface;
use Tests\Fixtures\SimpleMessage;

class TestCommand
{
    public function __construct(
        public SimpleMessage $message,
        public UuidInterface $projectId,
        public int|string|DateTimeInterface $id = '',
    ) {
    }
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
