<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Diagram\ClassDiagram;

final readonly class ClassDiagramClass
{
    /**
     * @param list<string> $stereotypes
     * @param list<array{name: string, visibility: string, type: string|null}> $properties
     * @param list<array{name: string, visibility: string, parameters: list<array{name: string, type: string|null}>, returnType: string|null}> $methods
     * @param list<ClassDiagramRelation> $relations
     */
    public function __construct(
        public string $name,
        public string $shortName,
        public string $kind,
        public array $stereotypes = [],
        public array $properties = [],
        public array $methods = [],
        public array $relations = [],
    ) {
    }
}
