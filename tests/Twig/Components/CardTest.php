<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Tests\Twig\Components;

use ChrisDev\UxComponents\Twig\Components\Card;
use PHPUnit\Framework\TestCase;

final class CardTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $card = new Card();

        self::assertSame('', $card->image);
        self::assertSame('', $card->title);
        self::assertSame('', $card->content);
        self::assertSame('', $card->buttonLabel);
        self::assertSame('#', $card->buttonAction);
    }

    public function testPropertiesAreSettable(): void
    {
        $card = new Card();
        $card->image = 'https://example.com/image.jpg';
        $card->title = 'Projet';
        $card->content = 'Contenu';
        $card->buttonLabel = 'Voir';
        $card->buttonAction = '/projet';

        self::assertSame('https://example.com/image.jpg', $card->image);
        self::assertSame('Projet', $card->title);
        self::assertSame('Contenu', $card->content);
        self::assertSame('Voir', $card->buttonLabel);
        self::assertSame('/projet', $card->buttonAction);
    }
}
