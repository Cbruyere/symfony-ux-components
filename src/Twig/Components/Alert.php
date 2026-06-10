<?php

namespace ChrisDev\UxComponents\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'Alert',
    template: '@ChrisDevUxComponents/components/Alert.html.twig'
)]
final class Alert
{
    public string $variant = 'info';
    public string $title = '';
    public string $message = '';

    public function getVariantClasses(): string
    {
        return match ($this->variant) {
            'info' => 'bg-blue-900/30 border-blue-700 text-blue-300',
            'success' => 'bg-green-900/30 border-green-700 text-green-300',
            'warning' => 'bg-amber-900/30 border-amber-700 text-amber-300',
            'danger' => 'bg-red-900/30 border-red-700 text-red-300',
            default => 'bg-blue-900/30 border-blue-700 text-blue-300',
        };
    }

    public function getIcon(): string
    {
        return match ($this->variant) {
            'success' => 'bi:check-circle',
            'warning' => 'bi:exclamation-triangle',
            'danger' => 'bi:x-circle',
            default => 'bi:info-circle',
        };
    }

    public function getIconClasses(): string
    {
        return 'h-5 w-5';
    }
}
