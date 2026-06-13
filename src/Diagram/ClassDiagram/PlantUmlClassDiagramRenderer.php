<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Diagram\ClassDiagram;

final class PlantUmlClassDiagramRenderer
{
    /**
     * @param list<ClassDiagramClass> $classes
     */
    public function renderPlantUml(array $classes, ?ClassDiagramRenderProfile $profile = null): string
    {
        $profile ??= ClassDiagramRenderProfile::detailed();
        $lines = [
            '@startuml',
            'skinparam classAttributeIconSize 0',
            'hide empty members',
            '',
        ];

        foreach ($classes as $class) {
            $stereotypes = $class->stereotypes === []
                ? ''
                : ' ' . implode(' ', array_map(static fn (string $stereotype): string => '<<' . $stereotype . '>>', $class->stereotypes));

            $lines[] = sprintf('%s "%s"%s {', $class->kind, $class->name, $stereotypes);

            if ($profile->includeMembers) {
                foreach ($class->properties as $property) {
                    $lines[] = sprintf(
                        '  %s%s%s',
                        $this->visibilitySymbol($property['visibility']),
                        $property['name'],
                        $property['type'] === null ? '' : ' : ' . $property['type'],
                    );
                }

                foreach ($class->methods as $method) {
                    $lines[] = sprintf(
                        '  %s%s(%s)%s',
                        $this->visibilitySymbol($method['visibility']),
                        $method['name'],
                        $this->renderParameters($method['parameters']),
                        $method['returnType'] === null ? '' : ' : ' . $method['returnType'],
                    );
                }
            }

            $lines[] = '}';
            $lines[] = '';
        }

        foreach ($this->uniqueRelations($classes, $profile) as $relation) {
            $lines[] = sprintf(
                '"%s" %s "%s"',
                $relation->source,
                $this->relationArrow($relation->type),
                $relation->target,
            );
        }

        $lines[] = '@enduml';

        return implode("\n", $lines) . "\n";
    }

    private function visibilitySymbol(string $visibility): string
    {
        return match ($visibility) {
            'private' => '-',
            'protected' => '#',
            default => '+',
        };
    }

    private function relationArrow(string $type): string
    {
        return match ($type) {
            'extends' => '--|>',
            'implements' => '..|>',
            default => '-->',
        };
    }

    /**
     * @param list<array{name: string, type: string|null}> $parameters
     */
    private function renderParameters(array $parameters): string
    {
        return implode(', ', array_map(
            static fn (array $parameter): string => $parameter['type'] === null
                ? $parameter['name']
                : $parameter['name'] . ': ' . $parameter['type'],
            $parameters,
        ));
    }

    /**
     * @param list<ClassDiagramClass> $classes
     * @return list<ClassDiagramRelation>
     */
    private function uniqueRelations(array $classes, ClassDiagramRenderProfile $profile): array
    {
        $relations = [];

        foreach ($classes as $class) {
            foreach ($class->relations as $relation) {
                if (!$profile->allowsRelation($relation)) {
                    continue;
                }

                $key = $relation->source . ':' . $relation->type . ':' . $relation->target;
                $relations[$key] = $relation;
            }
        }

        return array_values($relations);
    }
}
