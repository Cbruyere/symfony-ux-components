<?php

namespace ChrisDev\UxComponents\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'Carousel',
    template: '@ChrisDevUxComponents/components/Carousel.html.twig'
)]
final class Carousel
{
    /**
     * @var list<array{title?: string, description?: string, image?: string, alt?: string}>
     */
    public array $items = [];

    public string $id = 'carousel';
    public bool $withControls = true;
    public bool $withIndicators = true;

    /**
     * @return array{loadingClasses: string, dotsItemClasses: string}
     */
    public function getOptions(): array
    {
        return [
            'loadingClasses' => 'opacity-0',
            'dotsItemClasses' => 'hs-carousel-active:bg-blue-500 hs-carousel-active:border-blue-500 size-3 border border-slate-500 rounded-full cursor-pointer',
        ];
    }
}
