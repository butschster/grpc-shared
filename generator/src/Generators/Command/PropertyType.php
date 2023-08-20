<?php

declare(strict_types=1);

namespace Generator\Generators\Command;

use Generator\Generators\Command\Annotation\DefaultValue;
use Generator\Generators\Command\Annotation\DocType;
use Generator\Generators\Command\Annotation\Optional;
use Generator\Generators\Command\Annotation\Type;
use Generator\PHP\Property\BuiltInType;
use Generator\PHP\Property\ClassType;
use Generator\PHP\Property\DateTime;
use Generator\PHP\Property\EnumClassType;
use Generator\PHP\Property\TypeFactory;
use Google\Protobuf\Internal\EnumDescriptor;
use Google\Protobuf\Internal\FieldDescriptor;
use Nette\PhpGenerator\Literal;

final class PropertyType
{
    /**
     * @param non-empty-string $variable
     * @param \Generator\PHP\Property\Type[] $types
     * @param string|null $comment
     */
    public function __construct(
        public readonly string $variable,
        public readonly array $types,
        public readonly ?string $comment = null,
        public array $annotations = [],
        public readonly array $attributes = [],
        public readonly ?FieldDescriptor $descriptor = null,
    ) {
    }

    public function hasType(string $type): bool
    {
        foreach ($this->getPropertyTypes() as $propertyType) {
            if ($propertyType->isEqual($type)) {
                return true;
            }
        }

        return false;
    }

    public function isRepeatable(): bool
    {
        return $this->hasType('array');
    }

    public function getPropertyType(): string
    {
        return \implode('|', $this->getPropertyTypes());
    }

    /**
     * @return array<\Generator\PHP\Property\Type>
     */
    public function getPropertyTypes(): array
    {
        if ($this->isEnumType()) {
            return [
                new EnumClassType(
                    $this->getEnumDescriptor()->getClass(),
                ),
            ];
        }

        if ($this->hasCustomType()) {
            $types = [...$this->getCustomType()];
        } else {
            $types = $this->types;
        }

        if ($this->isOptional()) {
            $types[] = new BuiltInType('null');
        }

        return $this->uniqueTypes($types);
    }

    public function getPropertyDocTypes(): array
    {
        $types = [];

        if ($this->isOptional()) {
            $types[] = new BuiltInType('null');
        }

        if ($this->hasCustomDocType()) {
            return $this->uniqueTypes([...$types, ...$this->getCustomDocType()]);
        }

        $types = [...$types, ...$this->types];

        return $this->uniqueTypes($types);
    }

    /**
     * @return array<\Generator\PHP\Property\Type>
     */
    private function uniqueTypes(array $types): array
    {
        $uniqueTypes = [];

        foreach ($types as $type) {
            $uniqueTypes[(string)$type] = $type;
        }

        return \array_values($uniqueTypes);
    }

    public function getCameCaseVariable(): string
    {
        return \lcfirst(\str_replace('_', '', \ucwords($this->variable, '_')));
    }

    public function isDateTimeType(): bool
    {
        foreach ($this->getPropertyTypes() as $type) {
            if ($type instanceof DateTime) {
                return true;
            }
        }

        return false;
    }

    public function isEnumType(): bool
    {
        return $this->descriptor?->getEnumType()?->getClass() !== null;
    }

    public function getEnumDescriptor(): ?EnumDescriptor
    {
        return $this->descriptor?->getEnumType();
    }

    public function isOptional(): bool
    {
        foreach ($this->annotations as $attribute) {
            if ($attribute instanceof Optional) {
                return true;
            }
        }

        return false;
    }

    public function hasDefaultValue(): bool
    {
        if ($this->isOptional()) {
            return true;
        }

        if ($this->hasType('int') || $this->hasType('float') || $this->hasType('bool') || $this->hasType('string') || $this->isRepeatable()) {
            return true;
        }

        foreach ($this->annotations as $attribute) {
            if ($attribute instanceof DefaultValue) {
                return true;
            }
        }

        return false;
    }

    public function getDefaultValue(): string|null|bool|int|float|array|Literal
    {
        foreach ($this->annotations as $attribute) {
            if ($attribute instanceof DefaultValue) {
                return $attribute->value;
            }
        }

        if ($this->hasType('string')) {
            return '';
        }

        if ($this->hasType('int')) {
            return 0;
        }

        if ($this->hasType('float')) {
            return 0.0;
        }

        if ($this->hasType('bool')) {
            return false;
        }

        if ($this->isRepeatable()) {
            return [];
        }

        return null;
    }

    public function hasCustomType(): bool
    {
        foreach ($this->annotations as $attribute) {
            if ($attribute instanceof Type) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<\Generator\PHP\Property\Type>
     */
    public function getCustomType(): array
    {
        $types = [];
        foreach ($this->annotations as $attribute) {
            if ($attribute instanceof Type) {
                $types = [...$types, ...\explode('|', $attribute->type)];
            }
        }

        return (new TypeFactory())->createMany(\implode('|', $types));
    }

    public function hasCustomDocType(): bool
    {
        foreach ($this->annotations as $attribute) {
            if ($attribute instanceof DocType) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<\Generator\PHP\Property\Type>
     */
    public function getCustomDocType(): array
    {
        $types = [];
        foreach ($this->annotations as $attribute) {
            if ($attribute instanceof DocType) {
                $types = [...$types, ...\explode('|', $attribute->type)];
            }
        }

        return (new TypeFactory())->createMany(\implode('|', $types));
    }

    /**
     * @return array<ClassType>
     */
    public function getClassTypes(): array
    {
        $types = [];

        foreach ($this->getPropertyTypes() as $type) {
            if ($type instanceof ClassType) {
                $types[] = $type;
            }
        }

        return $types;
    }

    public function setDefaultValue(mixed $value): void
    {
        $this->annotations[] = new DefaultValue($value);
    }
}
