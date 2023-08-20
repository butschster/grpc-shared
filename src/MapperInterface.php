<?php

declare(strict_types=1);

namespace Shared\gRPC;

use Google\Protobuf\Internal\Message;

interface MapperInterface
{
    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public function fromMessage(Message $message, string $class): object;

    /**
     * @template T of Message
     * @param object $object
     * @param class-string<T> $message
     * @return T
     */
    public function toMessage(object $object, string $message): Message;
}
