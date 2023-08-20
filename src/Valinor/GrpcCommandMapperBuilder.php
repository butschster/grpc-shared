<?php

declare(strict_types=1);

namespace Shared\gRPC\Valinor;

use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class GrpcCommandMapperBuilder
{
    public function build(): TreeMapper
    {
        return (new MapperBuilder())
            ->infer(UuidInterface::class, fn () => Uuid::class)
            ->registerConstructor(Uuid::class, Uuid::fromString(...))
            ->enableFlexibleCasting()
            ->allowSuperfluousKeys()
            ->allowPermissiveTypes()
            ->mapper();
    }
}
