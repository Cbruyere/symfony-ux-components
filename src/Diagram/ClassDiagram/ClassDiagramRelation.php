<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Diagram\ClassDiagram;

final readonly class ClassDiagramRelation
{
    public function __construct(
        public string $source,
        public string $target,
        public string $type,
    ) {
    }
}
