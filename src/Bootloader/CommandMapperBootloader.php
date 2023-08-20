<?php

declare(strict_types=1);

namespace Shared\gRPC\Bootloader;

use CuyZ\Valinor\Mapper\TreeMapper;
use Shared\gRPC\CommandMapper;
use Shared\gRPC\Exception\GrpcExceptionMapper;
use Shared\gRPC\Valinor\GrpcCommandMapperBuilder;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

/**
 * This class is read only. Please do not edit it directly.
 */
final class CommandMapperBootloader extends Bootloader
{
    public function defineSingletons(): array
    {
        return [
            TreeMapper::class => [self::class, 'createTreeMapper'],
            CommandMapper::class => CommandMapper::class,
            GrpcExceptionMapper::class => GrpcExceptionMapper::class,
        ];
    }

    public function init(
        TokenizerListenerRegistryInterface $listenerRegistry,
        CommandMapper $mapperRegistry,
        GrpcExceptionMapper $exceptionMapperRegistry,
    ): void {
        $listenerRegistry->addListener($mapperRegistry);
        $listenerRegistry->addListener($exceptionMapperRegistry);
    }

    public function createTreeMapper(GrpcCommandMapperBuilder $builder): TreeMapper
    {
        return $builder->build();
    }
}
