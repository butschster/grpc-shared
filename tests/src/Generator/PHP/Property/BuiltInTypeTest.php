<?php

declare(strict_types=1);

namespace Tests\Generator\PHP\Property;

use Generator\PHP\Property\BuiltInType;
use Tests\TestCase;

final class BuiltInTypeTest extends TestCase
{
    public function testGetType(): void
    {
        $type = new BuiltInType('string');

        $this->assertSame('string', $type->type);

        $this->assertSame('string', (string) $type);
    }

    public function testGetDocType(): void
    {
        $type = new BuiltInType('string');

        $this->assertSame('string', $type->docType);
    }

    public function testCustomDocType(): void
    {
        $type = new BuiltInType('string', 'non-empty-string');

        $this->assertSame('non-empty-string', $type->docType);
    }
}
