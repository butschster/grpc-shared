<?php

declare(strict_types=1);

namespace Shared\gRPC\Attribute;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_CLASS), NamedArgumentConstructor]
final readonly class ErrorMapper
{
    /**
     * @param non-empty-string $type
     */
    public function __construct(
        public string $type,
    ) {
    }
}
