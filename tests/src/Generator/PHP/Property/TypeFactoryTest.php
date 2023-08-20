<?php

declare(strict_types=1);

namespace Tests\Generator\PHP\Property;

use Generator\PHP\Property\BuiltInType;
use Generator\PHP\Property\ClassType;
use Generator\PHP\Property\DateTime;
use Generator\PHP\Property\RepeatableType;
use Generator\PHP\Property\TypeFactory;
use Google\Protobuf\Internal\RepeatedField;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Fixtures\SimpleMessage;
use Tests\TestCase;

final class TypeFactoryTest extends TestCase
{
    private TypeFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new TypeFactory();
    }

    #[DataProvider(methodName: 'builtinTypesProvider')]
    public function testCreateBuiltinType(string $type): void
    {
        $this->assertInstanceOf(BuiltInType::class, $this->factory->create($type));
    }

    #[DataProvider(methodName: 'dateTimeTypesProvider')]
    public function testCreateDateTimeType(string $type): void
    {
        $this->assertInstanceOf(DateTime::class, $this->factory->create($type));
    }

    public function testCreatClassType(): void
    {
        $this->assertInstanceOf(ClassType::class, $this->factory->create(SimpleMessage::class));
    }

    public function tesRepeatedFieldShouldBeSkipped(): void
    {
        $this->assertNull($this->factory->create(RepeatedField::class));
    }

    #[DataProvider(methodName: 'repeatableTypesProvider')]
    public function testCreateRepeatableType(string $docType, string $type): void
    {
        $object = $this->factory->create($docType);

        $this->assertInstanceOf(RepeatableType::class, $object);
        $this->assertSame('array', $object->type);
        $this->assertSame($docType, $object->docType);
        $this->assertSame($type, $object->iterableType->type);
    }

    public static function repeatableTypesProvider(): iterable
    {
        yield 'string[]' => ['string[]', 'string'];
        yield 'int[]' => ['int[]', 'int'];
        yield 'float[]' => ['float[]', 'float'];
        yield 'bool[]' => ['bool[]', 'bool'];
        yield 'array[]' => ['array[]', 'array'];
        yield 'object[]' => ['object[]', 'object'];
        yield 'callable[]' => ['callable[]', 'callable'];
        yield 'iterable[]' => ['iterable[]', 'iterable'];
        yield 'class[]' => [SimpleMessage::class . '[]', SimpleMessage::class];
    }

    public static function dateTimeTypesProvider(): iterable
    {
        yield \DateTimeInterface::class => [\DateTimeInterface::class];
        yield \DateTimeImmutable::class => [\DateTimeImmutable::class];
        yield \DateTime::class => [\DateTime::class];
        yield \Google\Protobuf\Timestamp::class => [\Google\Protobuf\Timestamp::class];
    }

    public static function builtinTypesProvider(): iterable
    {
        yield 'string' => ['string'];
        yield 'int' => ['int'];
        yield 'float' => ['float'];
        yield 'bool' => ['bool'];
        yield 'array' => ['array'];
        yield 'object' => ['object'];
        yield 'callable' => ['callable'];
        yield 'iterable' => ['iterable'];
        yield 'void' => ['void'];
        yield 'mixed' => ['mixed'];
        yield 'null' => ['null'];
        yield 'false' => ['false'];
        yield 'resource' => ['resource'];
        yield 'static' => ['static'];
    }
}
