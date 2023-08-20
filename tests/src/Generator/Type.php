<?php

declare(strict_types=1);

namespace Tests\Generator;

/**
 * @Annotation
 */
final class Type
{
    public function __construct(array $values)
    {
        var_dump($values);
    }
}
