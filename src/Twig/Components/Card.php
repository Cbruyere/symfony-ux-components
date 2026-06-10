<?php

namespace ChrisDev\UxComponents\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'Card',
    template: '@ChrisDevUxComponents/components/Card.html.twig'
)]
final class Card
{
    public string $image = '';
    public string $title = '';
    public string $content = '';
    public string $buttonLabel = '';
    public string $buttonAction = '#';
}
