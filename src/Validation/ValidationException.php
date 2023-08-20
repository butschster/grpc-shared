<?php

declare(strict_types=1);

namespace Shared\gRPC\Validation;

use Shared\gRPC\Command\Common\v1\DTO\ValidationErrors;

final class ValidationException extends \DomainException
{
    /** @param ValidationErrors[] $errors */
    public function __construct(
        public readonly array $errors,
        int $code = 422,
    ) {
        parent::__construct($this->generateMessage(), $code);
    }

    private function generateMessage(): string
    {
        $message = 'Invalid data provided' . PHP_EOL . 'Errors:' . PHP_EOL;

        foreach ($this->errors as $error) {
            $message .= '  ' . $error->field . ':' . PHP_EOL;
            foreach ($error->errors as $e) {
                $message .= '    ' . $e->message . PHP_EOL;

                if ($e->meta) {
                    foreach ($e->meta as $param) {
                        $message .= '      ' . $param->key . ': ' . $param->value . PHP_EOL;
                    }
                }
            }
        }

        return $message;
    }
}
