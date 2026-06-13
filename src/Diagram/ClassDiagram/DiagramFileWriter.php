<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Diagram\ClassDiagram;

final class DiagramFileWriter
{
    public function ensureDirectory(string $path): void
    {
        if (is_dir($path)) {
            return;
        }

        if (!mkdir($path, 0777, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Impossible de creer le dossier de sortie: %s', $path));
        }
    }

    public function write(string $directory, string $filename, string $contents): string
    {
        $this->ensureDirectory($directory);

        $path = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, $contents);

        return $path;
    }
}
