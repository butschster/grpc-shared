<?php

declare(strict_types=1);

namespace Tests\Generator\PHP\Property;

use Generator\PHP\Property\BuiltInType;
use Generator\PHP\Property\ClassType;
use Generator\PHP\Property\RepeatableType;
use Tests\Fixtures\SimpleMessage;
use Tests\TestCase;

final class RepeatableTypeTest extends TestCase
{
    private RepeatableType $classType;
    private RepeatableType $builtinType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classType = new RepeatableType(new ClassType(SimpleMessage::class));
        $this->builtinType = new RepeatableType(new BuiltInType('string'));
    }

    public function testGetType(): void
    {
        $this->assertSame('array', $this->classType->type);
        $this->assertSame('array', $this->builtinType->type);
    }

    public function testGetDcoType(): void
    {
        $this->assertSame(SimpleMessage::class . '[]', $this->classType->docType);
        $this->assertSame('string[]', $this->builtinType->docType);
    }

    public function testGetIterableType(): void
    {
        $this->assertSame(SimpleMessage::class, $this->classType->iterableType->type);
        $this->assertSame('string', $this->builtinType->iterableType->type);
    }
}
