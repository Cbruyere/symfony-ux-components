<?php

namespace ChrisDev\UxComponents\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'UxBadge',
    template: '@ChrisDevUxComponents/components/Badge.html.twig'
)]
final class Badge
{
    public string $variant = 'neutral';
    public string $label = '';

    public function getVariantClasses(): string
    {
        return match ($this->variant) {
            'info' => 'bg-blue-800 text-blue-300',
            'success' => 'bg-green-800 text-green-300',
            'warning' => 'bg-amber-800 text-amber-300',
            'danger' => 'bg-red-800 text-red-300',
            default => 'bg-slate-800 text-slate-300',
        };
    }
}
