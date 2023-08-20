<?php

declare(strict_types=1);

namespace Shared\gRPC\Attribute;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_METHOD), NamedArgumentConstructor]
final class Guarded
{
    public function __construct(
        public readonly string $tokenField = 'token',
    ) {
    }
}
