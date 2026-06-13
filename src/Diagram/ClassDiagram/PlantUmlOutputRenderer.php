<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Diagram\ClassDiagram;

final readonly class PlantUmlOutputRenderer implements ClassDiagramOutputRendererInterface
{
    public function __construct(
        private PlantUmlClassDiagramRenderer $renderer,
    ) {
    }

    public function format(): string
    {
        return 'puml';
    }

    public function extension(): string
    {
        return 'puml';
    }

    /**
     * @param list<ClassDiagramClass> $classes
     */
    public function render(array $classes, ClassDiagramRenderProfile $profile): string
    {
        return $this->renderer->renderPlantUml($classes, $profile);
    }
}
