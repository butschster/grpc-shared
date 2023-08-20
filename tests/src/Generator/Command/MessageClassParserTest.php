<?php

declare(strict_types=1);

namespace Tests\Generator\Command;

use Generator\Generators\Message\MessageClassParser;
use Tests\Fixtures\SimpleMessage;
use Tests\Fixtures\WrongMessage;
use Tests\TestCase;

final class MessageClassParserTest extends TestCase
{
    public function testParseClass(): void
    {
        $parser = new MessageClassParser();

        $parsed = $parser->parse(SimpleMessage::class);

        $this->assertSame(SimpleMessage::class, $parsed->class);
        $this->assertCount(2, $parsed->properties);

        $this->assertSame('email', $parsed->properties[0]->variable);
        $this->assertSame(false, $parsed->properties[0]->getDefaultValue());


        $this->assertSame('password', $parsed->properties[1]->variable);

        $this->assertTrue($parsed->isGuarded());
    }

    public function testParseWrongCLass(): void
    {
        $parser = new MessageClassParser();

        $this->expectException(\InvalidArgumentException::class);
        $parser->parse(WrongMessage::class);
    }
}
