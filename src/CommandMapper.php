<?php

declare(strict_types=1);

namespace Shared\gRPC;

use CuyZ\Valinor\Mapper\MappingError;
use Google\Protobuf\Internal\Message;
use Shared\gRPC\Attribute\Mapper;
use Shared\gRPC\Valinor\ValidationExceptionMapper;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\FactoryInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(attribute: Mapper::class)]
#[Singleton]
final class CommandMapper implements TokenizationListenerInterface
{
    /** @var array<class-string, class-string<MapperInterface>> */
    private array $mappers = [];
    private array $classMap = [];

    /** @var array<class-string, MapperInterface> */
    private array $resolvedMappers = [];

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly FactoryInterface $factory,
        private readonly ValidationExceptionMapper $validationExceptionMapper,
    ) {
    }

    public function toMessage(object $object): Message
    {
        if (!isset($this->classMap[$object::class])) {
            throw new \InvalidArgumentException('Mapper not found');
        }

        $message = $this->classMap[$object::class];

        $mapper = $this->resolvedMappers[$object::class] ??= $this->factory->make($this->mappers[$object::class]);

        return $mapper->toMessage($object, $message);
    }

    public function fromMessage(Message $message): object
    {
        if (!isset($this->classMap[$message::class])) {
            throw new \InvalidArgumentException('Mapper not found');
        }

        $class = $this->classMap[$message::class];
        $mapper = $this->resolvedMappers[$class] ??= $this->factory->make($this->mappers[$class]);

        try {
            return $mapper->fromMessage($message, $class);
        } catch (MappingError $error) {
            throw $this->validationExceptionMapper->map($error);
        }
    }

    public function listen(\ReflectionClass $class): void
    {
        $this->register($class->getName());
    }

    /**
     * @param class-string<MapperInterface> $mapperClass
     *
     * @throws \ReflectionException
     */
    public function register(string $mapperClass): void
    {
        $class = new \ReflectionClass($mapperClass);
        $mapper = $this->reader->firstClassMetadata($class, Mapper::class);

        $this->classMap[$mapper->class] = $mapper->messageClass;
        $this->classMap[$mapper->messageClass] = $mapper->class;

        $this->mappers[$mapper->class] = $class->getName();
    }

    public function finalize(): void
    {
        // do nothing
    }
}
