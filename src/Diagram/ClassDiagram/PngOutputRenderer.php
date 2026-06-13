<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Diagram\ClassDiagram;

final readonly class PngOutputRenderer implements ClassDiagramOutputRendererInterface
{
    public function __construct(
        private PlantUmlClassDiagramRenderer $renderer,
        private PlantUmlCliRenderer $cliRenderer = new PlantUmlCliRenderer(),
    ) {
    }

    public function format(): string
    {
        return 'png';
    }

    public function extension(): string
    {
        return 'png';
    }

    /**
     * @param list<ClassDiagramClass> $classes
     */
    public function render(array $classes, ClassDiagramRenderProfile $profile): string
    {
        return $this->cliRenderer->render(
            $this->renderer->renderPlantUml($classes, $profile),
            'png',
        );
    }
}
