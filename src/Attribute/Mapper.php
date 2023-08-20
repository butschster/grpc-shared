<?php

declare(strict_types=1);

namespace Shared\gRPC\Attribute;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_CLASS), NamedArgumentConstructor]
final readonly class Mapper
{
    public function __construct(
        public string $class,
        public string $messageClass,
    ) {
    }
}
