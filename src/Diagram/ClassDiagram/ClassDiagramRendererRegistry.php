<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Diagram\ClassDiagram;

final readonly class ClassDiagramRendererRegistry
{
    /**
     * @var array<string, ClassDiagramOutputRendererInterface>
     */
    private array $renderers;

    /**
     * @param iterable<ClassDiagramOutputRendererInterface> $renderers
     */
    public function __construct(iterable $renderers = [])
    {
        $indexedRenderers = [];

        foreach ($renderers as $renderer) {
            $indexedRenderers[$renderer->format()] = $renderer;
        }

        if ($indexedRenderers === []) {
            $plantUmlRenderer = new PlantUmlClassDiagramRenderer();
            $indexedRenderers = [
                'puml' => new PlantUmlOutputRenderer($plantUmlRenderer),
                'svg' => new SvgOutputRenderer($plantUmlRenderer),
                'png' => new PngOutputRenderer($plantUmlRenderer),
            ];
        }

        $this->renderers = $indexedRenderers;
    }

    public function get(string $format): ?ClassDiagramOutputRendererInterface
    {
        $normalizedFormat = strtolower($format);

        if ($normalizedFormat === 'plantuml') {
            $normalizedFormat = 'puml';
        }

        return $this->renderers[$normalizedFormat] ?? null;
    }
}
