<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Tests\Twig\Components;

use ChrisDev\UxComponents\Twig\Components\Carousel;
use PHPUnit\Framework\TestCase;

final class CarouselTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $carousel = new Carousel();

        self::assertSame([], $carousel->items);
        self::assertSame('carousel', $carousel->id);
        self::assertTrue($carousel->withControls);
        self::assertTrue($carousel->withIndicators);
    }

    public function testGetOptions(): void
    {
        $carousel = new Carousel();

        self::assertSame([
            'loadingClasses' => 'opacity-0',
            'dotsItemClasses' => 'hs-carousel-active:bg-blue-500 hs-carousel-active:border-blue-500 size-3 border border-slate-500 rounded-full cursor-pointer',
        ], $carousel->getOptions());
    }

    public function testItemsAreSettable(): void
    {
        $carousel = new Carousel();
        $carousel->items = [
            ['title' => 'Slide 1', 'image' => 'https://example.com/1.jpg'],
        ];

        self::assertSame([
            ['title' => 'Slide 1', 'image' => 'https://example.com/1.jpg'],
        ], $carousel->items);
    }
}
