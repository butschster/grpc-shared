<?php

declare(strict_types=1);

namespace Tests\Generator\Command;

use Generator\Generators\Command\Annotation\DefaultValue;
use Generator\Generators\Command\Annotation\Optional;
use Generator\Generators\Command\PropertyType;
use Generator\PHP\Property\Type;
use Generator\PHP\Property\TypeFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Fixtures\SimpleMessage;
use Tests\TestCase;

final class PropertyTypeTest extends TestCase
{

    public function testGetCameCaseVariable(): void
    {
        $obj = new PropertyType('variable', []);
        $this->assertSame('variable', $obj->getCameCaseVariable());

        $obj = new PropertyType('variable_name_test', []);
        $this->assertSame('variableNameTest', $obj->getCameCaseVariable());
    }

    public function testHasType(): void
    {
        $obj = new PropertyType('variable', $this->buildTypes('int'));
        $this->assertTrue($obj->hasType('int'));
        $this->assertFalse($obj->hasType('string'));

        $obj = new PropertyType('variable', $this->buildTypes('int|string'));
        $this->assertTrue($obj->hasType('int'));
        $this->assertTrue($obj->hasType('string'));
    }

    #[DataProvider(methodName: 'repeatableDataProvider')]
    public function testIsRepeatable(string $type, bool $expected): void
    {
        $types = $this->buildTypes($type);

        $obj = new PropertyType('variable', $types);
        $this->assertSame($expected, $obj->isRepeatable());
    }

    public static function repeatableDataProvider(): iterable
    {
        yield ['array', true];
        yield ['int[]', true];
        yield ['string[]', true];
        yield ['int|string', false];
        yield ['string|int', false];
        yield ['int', false];
        yield ['string', false];
    }

    public function testGetPropertyType(): void
    {
        $obj = new PropertyType('variable', $this->buildTypes('int|string|bool|null|string'));

        $this->assertSame('int|string|bool|null', $obj->getPropertyType());
    }

    #[DataProvider(methodName: 'dateTimeTypesDataProvider')]
    public function testIsDateTime(string $type, bool $expected): void
    {
        $obj = new PropertyType('variable', $this->buildTypes($type));
        $this->assertSame($expected, $obj->isDateTimeType());
    }

    public static function dateTimeTypesDataProvider(): iterable
    {
        yield [\DateTimeInterface::class, true];
        yield [\DateTimeImmutable::class, true];
        yield [\DateTime::class, true];
        yield [\DateTimeInterface::class . '|int', true];
        yield [\DateTimeImmutable::class . '|int', true];
        yield [\DateTime::class . '|int', true];
        yield ['int|string|bool|null|string', false];
    }

    public function testCheckIfOptional(): void
    {
        $obj = new PropertyType('variable', []);
        $this->assertFalse($obj->isOptional());

        $obj = new PropertyType('variable', [], annotations: [new Optional()]);
        $this->assertTrue($obj->isOptional());
    }

    public function testDefaultValue(): void
    {
        $obj = new PropertyType('variable', []);
        $this->assertFalse($obj->hasDefaultValue());
        $this->assertNull($obj->getDefaultValue());

        $obj = new PropertyType('variable', [], annotations: [new DefaultValue('test')]);
        $this->assertTrue($obj->hasDefaultValue());
        $this->assertSame('test', $obj->getDefaultValue());
    }

    public function testCustomType(): void
    {
        $obj = new PropertyType('variable', [], annotations: [
            new \Generator\Generators\Command\Annotation\Type(
                'int|string'
            )
        ]);
        $this->assertTrue($obj->hasCustomType());
        $this->assertSame('int|string', \implode('|', $obj->getCustomType()));
    }

    public function testGetClassTypes(): void
    {
        $obj = new PropertyType(
            'variable',
            $this->buildTypes('int|string|' . SimpleMessage::class . '|bool|null|string|' . \DateTimeInterface::class)
        );

        $types = \implode('|', $obj->getClassTypes());

        $this->assertSame(SimpleMessage::class . '|' . \DateTimeInterface::class, $types);
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
