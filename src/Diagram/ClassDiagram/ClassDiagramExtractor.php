<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Diagram\ClassDiagram;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ClassDiagramExtractor
{
    /**
     * @return list<ClassDiagramClass>
     */
    public function extractFromFile(string $path): array
    {
        if (!is_file($path)) {
            throw new \InvalidArgumentException(sprintf('Fichier introuvable: %s', $path));
        }

        $tokens = token_get_all((string) file_get_contents($path));
        $namespace = $this->extractNamespace($tokens);
        $uses = $this->extractUses($tokens);
        $classes = [];

        foreach ($tokens as $index => $token) {
            if (!$this->isClassLikeToken($token) || $this->isAnonymousClass($tokens, $index)) {
                continue;
            }

            $className = $this->nextTokenValue($tokens, $index, [T_STRING]);

            if ($className === null) {
                continue;
            }

            $kind = strtolower((string) token_name($token[0]));
            $kind = str_replace('t_', '', $kind);
            $fullName = $namespace === '' ? $className : $namespace . '\\' . $className;
            $headerEnd = $this->findNextChar($tokens, $index, '{');

            if ($headerEnd === null) {
                continue;
            }

            $headerTokens = array_slice($tokens, $index, $headerEnd - $index);
            $relations = $this->extractHeaderRelations($headerTokens, $fullName, $namespace, $uses);
            $bodyEnd = $this->findMatchingBrace($tokens, $headerEnd);

            if ($bodyEnd === null) {
                continue;
            }

            [$properties, $methods, $bodyRelations] = $this->extractBodyMembers(
                array_slice($tokens, $headerEnd + 1, $bodyEnd - $headerEnd - 1),
                $fullName,
                $namespace,
                $uses,
            );

            $classes[] = new ClassDiagramClass(
                name: $fullName,
                shortName: $className,
                kind: $kind,
                stereotypes: $this->extractStereotypes($tokens, $index),
                properties: $properties,
                methods: $methods,
                relations: $this->uniqueRelations([...$relations, ...$bodyRelations]),
            );
        }

        return $classes;
    }

    /**
     * @return list<ClassDiagramClass>
     */
    public function extractFromDirectory(string $path): array
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException(sprintf('Dossier introuvable: %s', $path));
        }

        $classes = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            array_push($classes, ...$this->extractFromFile($file->getPathname()));
        }

        usort($classes, static fn (ClassDiagramClass $first, ClassDiagramClass $second): int => $first->name <=> $second->name);

        return $classes;
    }

    /**
     * @param list<mixed> $tokens
     */
    private function extractNamespace(array $tokens): string
    {
        foreach ($tokens as $index => $token) {
            if (!is_array($token) || $token[0] !== T_NAMESPACE) {
                continue;
            }

            return $this->collectName($tokens, $index + 1);
        }

        return '';
    }

    /**
     * @param list<mixed> $tokens
     * @return array<string, string>
     */
    private function extractUses(array $tokens): array
    {
        $uses = [];
        $classStarted = false;

        foreach ($tokens as $index => $token) {
            if ($this->isClassLikeToken($token)) {
                $classStarted = true;
            }

            if ($classStarted || !is_array($token) || $token[0] !== T_USE) {
                continue;
            }

            $name = $this->collectName($tokens, $index + 1);

            if ($name === '') {
                continue;
            }

            $alias = $this->extractUseAlias($tokens, $index + 1) ?? $this->shortName($name);
            $uses[$alias] = $name;
        }

        return $uses;
    }

    /**
     * @param list<mixed> $tokens
     */
    private function extractUseAlias(array $tokens, int $start): ?string
    {
        for ($index = $start; $index < count($tokens); ++$index) {
            $token = $tokens[$index];

            if ($token === ';') {
                return null;
            }

            if (is_array($token) && $token[0] === T_AS) {
                return $this->nextTokenValue($tokens, $index, [T_STRING]);
            }
        }

        return null;
    }

    /**
     * @param list<mixed> $tokens
     * @param array<string, string> $uses
     * @return list<ClassDiagramRelation>
     */
    private function extractHeaderRelations(array $tokens, string $source, string $namespace, array $uses): array
    {
        $relations = [];

        foreach ($tokens as $index => $token) {
            if (!is_array($token)) {
                continue;
            }

            if ($token[0] === T_EXTENDS) {
                $target = $this->collectName($tokens, $index + 1);

                if ($target !== '') {
                    $relations[] = new ClassDiagramRelation($source, $this->resolveType($target, $namespace, $uses), 'extends');
                }
            }

            if ($token[0] === T_IMPLEMENTS) {
                foreach ($this->collectNameList($tokens, $index + 1) as $target) {
                    $relations[] = new ClassDiagramRelation($source, $this->resolveType($target, $namespace, $uses), 'implements');
                }
            }
        }

        return $relations;
    }

    /**
     * @param list<mixed> $tokens
     * @param array<string, string> $uses
     * @return array{0: list<array{name: string, visibility: string, type: string|null}>, 1: list<array{name: string, visibility: string, parameters: list<array{name: string, type: string|null}>, returnType: string|null}>, 2: list<ClassDiagramRelation>}
     */
    private function extractBodyMembers(array $tokens, string $source, string $namespace, array $uses): array
    {
        $properties = [];
        $methods = [];
        $relations = [];
        $depth = 0;
        $statement = [];

        $skipMethodDeclaration = false;

        foreach ($tokens as $index => $token) {
            if ($skipMethodDeclaration) {
                if ($token === ';') {
                    $skipMethodDeclaration = false;
                }

                if ($token === '{') {
                    ++$depth;
                    $skipMethodDeclaration = false;
                }

                continue;
            }

            if ($token === '{') {
                ++$depth;
                continue;
            }

            if ($token === '}') {
                --$depth;
                continue;
            }

            if ($depth !== 0) {
                continue;
            }

            if (is_array($token) && $token[0] === T_FUNCTION) {
                $promotedProperties = $this->extractPromotedProperties($tokens, $index);

                foreach ($promotedProperties as $promotedProperty) {
                    $properties[] = $promotedProperty;

                    if ($promotedProperty['type'] !== null && $this->isClassType($promotedProperty['type'])) {
                        $relations[] = new ClassDiagramRelation(
                            $source,
                            $this->resolveType($promotedProperty['type'], $namespace, $uses),
                            'property',
                        );
                    }
                }

                $method = $this->extractMethod($tokens, $index, $statement);

                if ($method !== null) {
                    $methods[] = $method;

                    if ($method['returnType'] !== null && $this->isClassType($method['returnType'])) {
                        $relations[] = new ClassDiagramRelation(
                            $source,
                            $this->resolveType($method['returnType'], $namespace, $uses),
                            'method-return',
                        );
                    }
                }

                $statement = [];
                $skipMethodDeclaration = true;
                continue;
            }

            $statement[] = $token;

            if ($token === ';') {
                $property = $this->extractProperty($statement);

                if ($property !== null) {
                    $properties[] = $property;

                    if ($property['type'] !== null && $this->isClassType($property['type'])) {
                        $relations[] = new ClassDiagramRelation(
                            $source,
                            $this->resolveType($property['type'], $namespace, $uses),
                            'property',
                        );
                    }
                }

                $statement = [];
            }
        }

        return [$properties, $methods, $this->uniqueRelations($relations)];
    }

    /**
     * @param list<mixed> $tokens
     * @param list<mixed> $statement
     * @return array{name: string, visibility: string, parameters: list<array{name: string, type: string|null}>, returnType: string|null}|null
     */
    private function extractMethod(array $tokens, int $functionIndex, array $statement): ?array
    {
        $name = $this->nextTokenValue($tokens, $functionIndex, [T_STRING]);

        if ($name === null || $name === '__construct') {
            return null;
        }

        $parametersEnd = $this->findParametersEnd($tokens, $functionIndex);
        $returnType = null;

        if ($parametersEnd !== null) {
            $returnType = $this->extractReturnType($tokens, $parametersEnd + 1);
        }

        return [
            'name' => $name,
            'visibility' => $this->extractVisibility($statement),
            'parameters' => $this->extractMethodParameters($tokens, $functionIndex),
            'returnType' => $returnType,
        ];
    }

    /**
     * @param list<mixed> $tokens
     * @return list<array{name: string, type: string|null}>
     */
    private function extractMethodParameters(array $tokens, int $functionIndex): array
    {
        $parametersStart = $this->findNextChar($tokens, $functionIndex, '(');
        $parametersEnd = $this->findParametersEnd($tokens, $functionIndex);

        if ($parametersStart === null || $parametersEnd === null) {
            return [];
        }

        $parameters = [];

        foreach ($this->splitParameterTokens(array_slice($tokens, $parametersStart + 1, $parametersEnd - $parametersStart - 1)) as $parameterTokens) {
            $parameter = $this->extractMethodParameter($parameterTokens);

            if ($parameter !== null) {
                $parameters[] = $parameter;
            }
        }

        return $parameters;
    }

    /**
     * @param list<mixed> $tokens
     * @return array{name: string, type: string|null}|null
     */
    private function extractMethodParameter(array $tokens): ?array
    {
        foreach ($tokens as $index => $token) {
            if (!is_array($token) || $token[0] !== T_VARIABLE) {
                continue;
            }

            return [
                'name' => ltrim($token[1], '$'),
                'type' => $this->extractPropertyType(array_slice($tokens, 0, $index)),
            ];
        }

        return null;
    }

    /**
     * @param list<mixed> $tokens
     * @return list<array{name: string, visibility: string, type: string|null}>
     */
    private function extractPromotedProperties(array $tokens, int $functionIndex): array
    {
        $name = $this->nextTokenValue($tokens, $functionIndex, [T_STRING]);

        if ($name !== '__construct') {
            return [];
        }

        $parametersStart = $this->findNextChar($tokens, $functionIndex, '(');
        $parametersEnd = $this->findParametersEnd($tokens, $functionIndex);

        if ($parametersStart === null || $parametersEnd === null) {
            return [];
        }

        $properties = [];

        foreach ($this->splitParameterTokens(array_slice($tokens, $parametersStart + 1, $parametersEnd - $parametersStart - 1)) as $parameterTokens) {
            $property = $this->extractPromotedProperty($parameterTokens);

            if ($property !== null) {
                $properties[] = $property;
            }
        }

        return $properties;
    }

    /**
     * @param list<mixed> $tokens
     * @return list<list<mixed>>
     */
    private function splitParameterTokens(array $tokens): array
    {
        $parameters = [];
        $current = [];
        $depth = 0;

        foreach ($tokens as $token) {
            if ($token === '[' || $token === '(') {
                ++$depth;
            }

            if ($token === ']' || $token === ')') {
                --$depth;
            }

            if ($token === ',' && $depth === 0) {
                $parameters[] = $current;
                $current = [];
                continue;
            }

            $current[] = $token;
        }

        if ($current !== []) {
            $parameters[] = $current;
        }

        return $parameters;
    }

    /**
     * @param list<mixed> $tokens
     * @return array{name: string, visibility: string, type: string|null}|null
     */
    private function extractPromotedProperty(array $tokens): ?array
    {
        if (!$this->hasVisibility($tokens)) {
            return null;
        }

        foreach ($tokens as $index => $token) {
            if (!is_array($token) || $token[0] !== T_VARIABLE) {
                continue;
            }

            return [
                'name' => ltrim($token[1], '$'),
                'visibility' => $this->extractVisibility($tokens),
                'type' => $this->extractPropertyType(array_slice($tokens, 0, $index)),
            ];
        }

        return null;
    }

    /**
     * @param list<mixed> $statement
     * @return array{name: string, visibility: string, type: string|null}|null
     */
    private function extractProperty(array $statement): ?array
    {
        foreach ($statement as $index => $token) {
            if (!is_array($token) || $token[0] !== T_VARIABLE) {
                continue;
            }

            return [
                'name' => ltrim($token[1], '$'),
                'visibility' => $this->extractVisibility($statement),
                'type' => $this->extractPropertyType(array_slice($statement, 0, $index)),
            ];
        }

        return null;
    }

    /**
     * @param list<mixed> $statement
     */
    private function extractVisibility(array $statement): string
    {
        foreach ($statement as $token) {
            if (!is_array($token)) {
                continue;
            }

            if ($token[0] === T_PRIVATE) {
                return 'private';
            }

            if ($token[0] === T_PROTECTED) {
                return 'protected';
            }
        }

        return 'public';
    }

    /**
     * @param list<mixed> $statement
     */
    private function hasVisibility(array $statement): bool
    {
        foreach ($statement as $token) {
            if (is_array($token) && in_array($token[0], [T_PUBLIC, T_PROTECTED, T_PRIVATE], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<mixed> $tokens
     */
    private function extractPropertyType(array $tokens): ?string
    {
        $parts = [];

        foreach ($tokens as $token) {
            if (is_array($token) && in_array($token[0], [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_READONLY], true)) {
                $parts = [];
                continue;
            }

            if (is_array($token) && $token[0] === T_WHITESPACE) {
                continue;
            }

            $value = is_array($token) ? $token[1] : $token;

            if ($value === '?') {
                continue;
            }

            $parts[] = $value;
        }

        $type = trim(implode('', $parts));

        return $type === '' ? null : $type;
    }

    /**
     * @param list<mixed> $tokens
     */
    private function extractReturnType(array $tokens, int $start): ?string
    {
        $parts = [];
        $typeStarted = false;

        for ($index = $start; $index < count($tokens); ++$index) {
            $token = $tokens[$index];

            if ($token === ':') {
                $typeStarted = true;
                continue;
            }

            if (!$typeStarted) {
                continue;
            }

            if ($token === '{' || $token === ';') {
                break;
            }

            if (is_array($token) && $token[0] === T_WHITESPACE) {
                continue;
            }

            $value = is_array($token) ? $token[1] : $token;

            if ($value === '?') {
                continue;
            }

            $parts[] = $value;
        }

        $type = trim(implode('', $parts));

        return $type === '' ? null : $type;
    }

    /**
     * @param list<mixed> $tokens
     */
    private function findParametersEnd(array $tokens, int $functionIndex): ?int
    {
        $start = $this->findNextChar($tokens, $functionIndex, '(');

        if ($start === null) {
            return null;
        }

        $depth = 0;

        for ($index = $start; $index < count($tokens); ++$index) {
            if ($tokens[$index] === '(') {
                ++$depth;
            }

            if ($tokens[$index] === ')') {
                --$depth;

                if ($depth === 0) {
                    return $index;
                }
            }
        }

        return null;
    }

    /**
     * @param list<mixed> $tokens
     * @param list<int> $acceptedTypes
     */
    private function nextTokenValue(array $tokens, int $start, array $acceptedTypes): ?string
    {
        for ($index = $start + 1; $index < count($tokens); ++$index) {
            $token = $tokens[$index];

            if (is_array($token) && $token[0] === T_WHITESPACE) {
                continue;
            }

            if (is_array($token) && in_array($token[0], $acceptedTypes, true)) {
                return $token[1];
            }
        }

        return null;
    }

    /**
     * @param list<mixed> $tokens
     */
    private function collectName(array $tokens, int $start): string
    {
        $parts = [];

        for ($index = $start; $index < count($tokens); ++$index) {
            $token = $tokens[$index];

            if (is_array($token) && $token[0] === T_WHITESPACE) {
                if ($parts === []) {
                    continue;
                }

                break;
            }

            if ($token === '\\') {
                $parts[] = '\\';
                continue;
            }

            if (is_array($token) && in_array($token[0], [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_NS_SEPARATOR], true)) {
                $parts[] = $token[1];
                continue;
            }

            if ($parts !== []) {
                break;
            }
        }

        return trim(implode('', $parts), '\\');
    }

    /**
     * @param list<mixed> $tokens
     * @return list<string>
     */
    private function collectNameList(array $tokens, int $start): array
    {
        $names = [];
        $parts = [];

        for ($index = $start; $index < count($tokens); ++$index) {
            $token = $tokens[$index];

            if ($token === '{') {
                break;
            }

            if ($token === ',') {
                $name = trim(implode('', $parts), '\\ ');

                if ($name !== '') {
                    $names[] = $name;
                }

                $parts = [];
                continue;
            }

            if (is_array($token) && $token[0] === T_WHITESPACE) {
                continue;
            }

            if ($token === '\\') {
                $parts[] = '\\';
                continue;
            }

            if (is_array($token) && in_array($token[0], [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_NS_SEPARATOR], true)) {
                $parts[] = $token[1];
            }
        }

        $name = trim(implode('', $parts), '\\ ');

        if ($name !== '') {
            $names[] = $name;
        }

        return $names;
    }

    /**
     * @param list<mixed> $tokens
     */
    private function findNextChar(array $tokens, int $start, string $char): ?int
    {
        for ($index = $start; $index < count($tokens); ++$index) {
            if ($tokens[$index] === $char) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param list<mixed> $tokens
     */
    private function findMatchingBrace(array $tokens, int $start): ?int
    {
        $depth = 0;

        for ($index = $start; $index < count($tokens); ++$index) {
            if ($tokens[$index] === '{') {
                ++$depth;
            }

            if ($tokens[$index] === '}') {
                --$depth;

                if ($depth === 0) {
                    return $index;
                }
            }
        }

        return null;
    }

    /**
     * @param mixed $token
     */
    private function isClassLikeToken(mixed $token): bool
    {
        return is_array($token) && in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM], true);
    }

    /**
     * @param list<mixed> $tokens
     */
    private function isAnonymousClass(array $tokens, int $index): bool
    {
        for ($previous = $index - 1; $previous >= 0; --$previous) {
            $token = $tokens[$previous];

            if (is_array($token) && $token[0] === T_WHITESPACE) {
                continue;
            }

            return is_array($token) && $token[0] === T_NEW;
        }

        return false;
    }

    /**
     * @param list<mixed> $tokens
     * @return list<string>
     */
    private function extractStereotypes(array $tokens, int $classIndex): array
    {
        $stereotypes = [];

        for ($previous = $classIndex - 1; $previous >= 0; --$previous) {
            $token = $tokens[$previous];

            if (is_array($token) && $token[0] === T_WHITESPACE) {
                continue;
            }

            if (is_array($token) && $token[0] === T_READONLY) {
                $stereotypes[] = 'readonly';
                continue;
            }

            if (is_array($token) && $token[0] === T_FINAL) {
                $stereotypes[] = 'final';
                continue;
            }

            break;
        }

        return array_reverse($stereotypes);
    }

    /**
     * @param array<string, string> $uses
     */
    private function resolveType(string $type, string $namespace, array $uses): string
    {
        $type = trim($type, '\\');
        $firstPart = explode('\\', $type)[0];

        if (isset($uses[$firstPart])) {
            return $uses[$firstPart] . substr($type, strlen($firstPart));
        }

        if (str_contains($type, '\\')) {
            return $type;
        }

        return $namespace === '' ? $type : $namespace . '\\' . $type;
    }

    private function isClassType(string $type): bool
    {
        $type = ltrim($type, '?\\');
        $nativeTypes = ['array', 'bool', 'callable', 'false', 'float', 'int', 'iterable', 'mixed', 'never', 'null', 'object', 'self', 'static', 'string', 'true', 'void'];

        return !in_array(strtolower($type), $nativeTypes, true) && !str_contains($type, '|') && !str_contains($type, '&');
    }

    /**
     * @param list<ClassDiagramRelation> $relations
     * @return list<ClassDiagramRelation>
     */
    private function uniqueRelations(array $relations): array
    {
        $unique = [];

        foreach ($relations as $relation) {
            $key = $relation->source . ':' . $relation->type . ':' . $relation->target;
            $unique[$key] = $relation;
        }

        return array_values($unique);
    }

    private function shortName(string $className): string
    {
        $parts = explode('\\', $className);

        return (string) end($parts);
    }
}
