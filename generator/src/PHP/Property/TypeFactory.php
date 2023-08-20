<?php

declare(strict_types=1);

namespace Generator\PHP\Property;

use Google\Protobuf\Internal\RepeatedField;

final class TypeFactory
{
    /**
     * @return array<Type>
     */
    public function createMany(string $types): array
    {
        $types = \explode('|', $types);

        $result = [];

        foreach ($types as $type) {
            $type = \trim($type);
            $type = $this->create($type);
            if ($type === null) {
                continue;
            }
            $result[] = $type;
        }

        return $result;
    }

    public function create(string $type): ?Type
    {
        $type = \ltrim($type, '\\');

        if (\str_ends_with($type, '[]')) {
            $type = \substr($type, 0, -2);
            return new RepeatableType(
                $this->create($type)
            );
        }

        if ($type === RepeatedField::class) {
            return null;
        }

        if ($type === \Google\Protobuf\Timestamp::class) {
            return new DateTime(
                \DateTimeInterface::class
            );
        }

        if (\in_array($type, [\DateTimeInterface::class, \DateTimeImmutable::class, \DateTime::class])) {
            return new DateTime($type);
        }

        if (\class_exists($type) || \interface_exists($type) || \trait_exists($type)) {
            return new ClassType($type);
        }

        return new BuiltInType($type);
    }
}
