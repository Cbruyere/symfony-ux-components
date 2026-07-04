<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Tests\Twig\Components;

use ChrisDev\UxComponents\Twig\Components\Alert;
use PHPUnit\Framework\TestCase;

final class AlertTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function variantProvider(): iterable
    {
        yield 'info' => ['info', 'bg-blue-900/30 border-blue-700 text-blue-300', 'bi:info-circle'];
        yield 'success' => ['success', 'bg-green-900/30 border-green-700 text-green-300', 'bi:check-circle'];
        yield 'warning' => ['warning', 'bg-amber-900/30 border-amber-700 text-amber-300', 'bi:exclamation-triangle'];
        yield 'danger' => ['danger', 'bg-red-900/30 border-red-700 text-red-300', 'bi:x-circle'];
        yield 'unknown falls back to info' => ['not-a-variant', 'bg-blue-900/30 border-blue-700 text-blue-300', 'bi:info-circle'];
    }

    /**
     * @dataProvider variantProvider
     */
    public function testGetVariantClassesAndIcon(string $variant, string $expectedClasses, string $expectedIcon): void
    {
        $alert = new Alert();
        $alert->variant = $variant;

        self::assertSame($expectedClasses, $alert->getVariantClasses());
        self::assertSame($expectedIcon, $alert->getIcon());
    }

    public function testIconClassesAreFixed(): void
    {
        $alert = new Alert();

        self::assertSame('h-5 w-5', $alert->getIconClasses());
    }
}
