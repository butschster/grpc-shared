<?php

declare(strict_types=1);

namespace Shared\gRPC\Validation\Mapper;

use Shared\gRPC\Attribute\ErrorMapper;
use Shared\gRPC\Command\Common\v1\DTO\ValidationErrors;
use Shared\gRPC\CommandMapper;
use Shared\gRPC\Exception\MapperInterface;
use Shared\gRPC\Services\Common\v1\DTO\Exception;
use Shared\gRPC\Services\Common\v1\DTO\ValidationErrors as ValidationErrorsMessage;
use Shared\gRPC\Services\Common\v1\DTO\ValidationException as ValidationExceptionMessage;
use Shared\gRPC\Validation\ValidationException;
use Spiral\RoadRunner\GRPC\Exception\GRPCException;
use Spiral\RoadRunner\GRPC\Exception\GRPCExceptionInterface;

#[ErrorMapper(type: 'validation_exception')]
final readonly class ValidationExceptionMapper implements MapperInterface
{
    public function __construct(
        private CommandMapper $mapper,
    ) {
    }

    /**
     * @param ValidationException $e
     */
    public function toGrpcException(\Throwable $e): GRPCExceptionInterface
    {
        \assert($e instanceof ValidationException);

        return new GRPCException(
            message: $e->getMessage(),
            code: $e->getCode(),
            details: [
                new ValidationExceptionMessage([
                    'type' => 'validation_exception',
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'errors' => \array_map(
                        fn (ValidationErrors $error): ValidationErrorsMessage => $this->mapper->toMessage($error),
                        $e->errors,
                    ),
                ]),
            ],
            previous: $e,
        );
    }

    public function fromError(Exception|ValidationExceptionMessage $error): \Throwable
    {
        \assert($error instanceof ValidationExceptionMessage);

        return new ValidationException(
            errors: \array_map(
                fn (ValidationErrorsMessage $error): ValidationErrors => $this->mapper->fromMessage($error),
                \iterator_to_array($error->getErrors()->getIterator()),
            )
        );
    }
}
