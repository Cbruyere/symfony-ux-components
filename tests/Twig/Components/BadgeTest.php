<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Tests\Twig\Components;

use ChrisDev\UxComponents\Twig\Components\Badge;
use PHPUnit\Framework\TestCase;

final class BadgeTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function variantProvider(): iterable
    {
        yield 'info' => ['info', 'bg-blue-800 text-blue-300'];
        yield 'success' => ['success', 'bg-green-800 text-green-300'];
        yield 'warning' => ['warning', 'bg-amber-800 text-amber-300'];
        yield 'danger' => ['danger', 'bg-red-800 text-red-300'];
        yield 'unknown falls back to neutral' => ['not-a-variant', 'bg-slate-800 text-slate-300'];
    }

    /**
     * @dataProvider variantProvider
     */
    public function testGetVariantClasses(string $variant, string $expectedClasses): void
    {
        $badge = new Badge();
        $badge->variant = $variant;

        self::assertSame($expectedClasses, $badge->getVariantClasses());
    }

    public function testDefaultVariantIsNeutral(): void
    {
        $badge = new Badge();

        self::assertSame('bg-slate-800 text-slate-300', $badge->getVariantClasses());
    }
}
