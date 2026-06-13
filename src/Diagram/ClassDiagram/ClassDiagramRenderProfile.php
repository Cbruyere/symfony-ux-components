<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Diagram\ClassDiagram;

final readonly class ClassDiagramRenderProfile
{
    private const MODE_DETAILED = 'detailed';
    private const MODE_ARCHITECTURE = 'architecture';

    private function __construct(
        public string $mode,
        public bool $includeMembers,
    ) {
    }

    public static function detailed(): self
    {
        return new self(self::MODE_DETAILED, true);
    }

    public static function architecture(): self
    {
        return new self(self::MODE_ARCHITECTURE, false);
    }

    public static function fromMode(string $mode): ?self
    {
        return match (strtolower($mode)) {
            self::MODE_DETAILED => self::detailed(),
            self::MODE_ARCHITECTURE => self::architecture(),
            default => null,
        };
    }

    public function allowsRelation(ClassDiagramRelation $relation): bool
    {
        if ($this->mode === self::MODE_DETAILED) {
            return true;
        }

        if (!in_array($relation->type, ['extends', 'implements', 'property'], true)) {
            return false;
        }

        return !$this->isDataCarrier($relation->target);
    }

    private function isDataCarrier(string $className): bool
    {
        $shortName = $this->shortName($className);

        foreach (['Dto', 'DTO', 'Result', 'State', 'ValueObject'] as $suffix) {
            if (str_ends_with($shortName, $suffix)) {
                return true;
            }
        }

        return false;
    }

    private function shortName(string $className): string
    {
        $parts = explode('\\', $className);

        return (string) end($parts);
    }
}
