<?php

declare(strict_types=1);

namespace Tests\Generator\PHP\Property;

use Generator\PHP\Property\ClassType;
use Tests\Fixtures\SimpleMessage;
use Tests\TestCase;

final class ClassTypeTest extends TestCase
{
    private ClassType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new ClassType(SimpleMessage::class);
    }

    public function testGetType(): void
    {
        $this->assertSame(SimpleMessage::class, $this->type->type);
        $this->assertSame(SimpleMessage::class, (string)$this->type);
    }

    public function testGetDocType(): void
    {
        $this->assertSame(SimpleMessage::class, $this->type->docType);
    }

    public function testGetShortName(): void
    {
        $this->assertSame('SimpleMessage', $this->type->getShortName());
    }

    public function testGetNamespace(): void
    {
        $this->assertSame('Tests\Fixtures', $this->type->getNamespace());
    }
}
