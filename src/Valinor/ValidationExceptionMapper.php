<?php

declare(strict_types=1);

namespace Shared\gRPC\Valinor;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Mapper\Tree\Node;
use Shared\gRPC\Command\Common\v1\DTO\ValidationError;
use Shared\gRPC\Command\Common\v1\DTO\ValidationErrorMeta;
use Shared\gRPC\Command\Common\v1\DTO\ValidationErrors;
use Shared\gRPC\Validation\ValidationException;

final class ValidationExceptionMapper
{
    public function map(MappingError $error): ValidationException
    {
        return new ValidationException(
            errors: \iterator_to_array($this->getValidationErrorsFromNode($error->node()), false),
            // TODO: подумать над кодом ошибки
            code: 417
        );
    }

    /**
     * @return \Traversable<ValidationErrors>
     */
    private function getValidationErrorsFromNode(Node $node): \Traversable
    {
        foreach ($node->children() as $childrenNode) {
            $errors = [];

            foreach ($childrenNode->messages() as $message) {
                $originalMessage = $message->originalMessage();
                $msgRefl = new \ReflectionClass($originalMessage);

                $meta = [];
                if ($originalMessage instanceof HasParameters) {
                    foreach ($originalMessage->parameters() as $key => $parameter) {
                        $meta[] = new ValidationErrorMeta(
                            key: $key,
                            value: (string)$parameter,
                        );
                    }
                }

                $errors[] = new ValidationError(
                // TODO: добавить марринг на универсальные ключи валидации, как в классе Assert
                    message: $message->toString(),
                    meta: $meta
                );
            }

            if ($errors !== []) {
                yield new ValidationErrors(
                    field: $childrenNode->path(),
                    errors: $errors
                );
            }

            yield from $this->getValidationErrorsFromNode($childrenNode);
        }
    }

    private function toSnakeCase(string $value): string
    {
        return \strtolower(\preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
    }
}
