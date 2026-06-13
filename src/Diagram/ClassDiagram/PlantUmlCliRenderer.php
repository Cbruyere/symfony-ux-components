<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Diagram\ClassDiagram;

final readonly class PlantUmlCliRenderer
{
    private const ERROR_MESSAGE = 'PlantUML rendering requires the plantuml CLI for svg/png output. Use --format=puml or install plantuml.';

    public function __construct(
        private ?string $plantUmlBinary = null,
    ) {
    }

    public function render(string $plantUml, string $format): string
    {
        $binary = $this->plantUmlBinary ?? $this->findExecutable('plantuml');

        if ($binary === null || !is_executable($binary)) {
            throw new \RuntimeException(self::ERROR_MESSAGE);
        }

        $inputFile = tempnam(sys_get_temp_dir(), 'ux-components-plantuml-');

        if ($inputFile === false) {
            throw new \RuntimeException('Unable to create a temporary PlantUML source file.');
        }

        $sourceFile = $inputFile . '.puml';
        rename($inputFile, $sourceFile);
        file_put_contents($sourceFile, $plantUml);

        $command = escapeshellarg($binary) . ' ' . escapeshellarg('-t' . $format) . ' ' . escapeshellarg($sourceFile);
        $output = [];
        $statusCode = 0;
        exec($command, $output, $statusCode);

        $outputFile = substr($sourceFile, 0, -5) . '.' . $format;

        try {
            if ($statusCode !== 0 || !is_file($outputFile)) {
                throw new \RuntimeException(self::ERROR_MESSAGE);
            }

            return (string) file_get_contents($outputFile);
        } finally {
            if (is_file($sourceFile)) {
                unlink($sourceFile);
            }

            if (is_file($outputFile)) {
                unlink($outputFile);
            }
        }
    }

    private function findExecutable(string $name): ?string
    {
        $path = getenv('PATH');

        if ($path === false || $path === '') {
            return null;
        }

        foreach (explode(PATH_SEPARATOR, $path) as $directory) {
            $candidate = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;

            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
