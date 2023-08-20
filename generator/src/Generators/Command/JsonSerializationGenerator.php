<?php

declare(strict_types=1);

namespace Generator\Generators\Command;

use Generator\PHP\ClassDeclaration;

final class JsonSerializationGenerator
{
    public function generate(ClassDeclaration $class, array $properties): void
    {
        $class->class->addImplement(\JsonSerializable::class);

        $serializeMethod = $class->class->addMethod('jsonSerialize');
        $serializeMethod->setReturnType('array');

        if ($properties === []) {
            $serializeMethod->addBody('return [];');
            return;
        }

        $serializeMethod->addBody('$data = [];');

        foreach ($properties as $property) {
            $serializeMethod->addBody(
                '$data[\'' . $property->variable . '\'] = ' . $this->getJsonSerializeVariable($property) . ';'
            );
        }

        $serializeMethod->addBody('return $data;');
    }

    private function getJsonSerializeVariable(PropertyType $property): string
    {
        if ($property->isEnumType()) {
            return '$this->' . $property->getCameCaseVariable() . '->value';
        }

        if ($property->isDateTimeType()) {
            return '$this->' . $property->getCameCaseVariable() . '?->format(\DateTimeInterface::RFC3339)';
        }

        return '$this->' . $property->getCameCaseVariable();
    }
}
