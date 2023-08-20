<?php

declare(strict_types=1);

namespace Tests\Generator\PHP;

use Generator\PHP\AttributesParser;
use Tests\TestCase;

final class AttributesParserTest extends TestCase
{
    public function testParse(): void
    {
        $parser = new AttributesParser();

        $attributes = $parser->parse(
            <<<'DOCKBLOCK'

&#64;Type(type="Ramsey\Uuid\UuidInterface")

#[Assert\Email]

#[Assert\Length(
       min: 2,
       max: 50,
       minMessage: 'Your first name must be at least {{ limit }} characters long',
       maxMessage: 'Your first name cannot be longer than {{ limit }} characters',
)]

#[Assert\NotBlank]

#[Assert\Blank]

#[Assert\NotNull]

#[Assert\IsFalse(
        message: "You've entered an invalid state."
    )]

#[SecurityAssert\UserPassword(
    message: 'Wrong value for your current password',
)]

#[Assert\LessThan(value: 80)]

Generated from protobuf field <code>string uuid = 1;</code>
DOCKBLOCK
        );

        $attributes;

        $this->assertCount(8, $attributes);

        $this->assertSame([
            [
                'class' => 'Symfony\Component\Validator\Constraints\Email',
                'arguments' => [],
            ],
            [
                'class' => 'Symfony\Component\Validator\Constraints\Length',
                'arguments' => [
                    'min' => '2',
                    'max' => '50',
                    'minMessage' => 'Your first name must be at least {{ limit }} characters long',
                    'maxMessage' => 'Your first name cannot be longer than {{ limit }} characters',
                ],
            ],
            [
                'class' => 'Symfony\Component\Validator\Constraints\NotBlank',
                'arguments' => [],
            ],
            [
                'class' => 'Symfony\Component\Validator\Constraints\Blank',
                'arguments' => [],
            ],
            [
                'class' => 'Symfony\Component\Validator\Constraints\NotNull',
                'arguments' => [],
            ],
            [
                'class' => 'Symfony\Component\Validator\Constraints\IsFalse',
                'arguments' => [
                    'message' => 'You\'ve entered an invalid state.'
                ],
            ],
            [
                'class' => 'Symfony\Component\Security\Core\Validator\Constraints\UserPassword',
                'arguments' => [
                    'message' => 'Wrong value for your current password'
                ],
            ],
            [
                'class' => 'Symfony\Component\Validator\Constraints\LessThan',
                'arguments' => [
                    'value' => '80'
                ],
            ],
        ], $attributes);
    }
}
