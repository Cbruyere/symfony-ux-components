<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Diagram\ClassDiagram;

interface ClassDiagramOutputRendererInterface
{
    public function format(): string;

    public function extension(): string;

    /**
     * @param list<ClassDiagramClass> $classes
     */
    public function render(array $classes, ClassDiagramRenderProfile $profile): string;
}
