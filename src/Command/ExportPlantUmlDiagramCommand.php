<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Command;

use ChrisDev\UxComponents\Diagram\ClassDiagram\ClassDiagramClass;
use ChrisDev\UxComponents\Diagram\ClassDiagram\ClassDiagramExtractor;
use ChrisDev\UxComponents\Diagram\ClassDiagram\ClassDiagramOutputRendererInterface;
use ChrisDev\UxComponents\Diagram\ClassDiagram\ClassDiagramRenderProfile;
use ChrisDev\UxComponents\Diagram\ClassDiagram\ClassDiagramRendererRegistry;
use ChrisDev\UxComponents\Diagram\ClassDiagram\DiagramFileWriter;
use ChrisDev\UxComponents\Diagram\ClassDiagram\PngOutputRenderer;
use ChrisDev\UxComponents\Diagram\ClassDiagram\PlantUmlOutputRenderer;
use ChrisDev\UxComponents\Diagram\ClassDiagram\PlantUmlClassDiagramRenderer;
use ChrisDev\UxComponents\Diagram\ClassDiagram\SvgOutputRenderer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'export:diagram:plantuml',
    description: 'Generate class diagrams from PHP files or directories.',
)]
final class ExportPlantUmlDiagramCommand extends Command
{
    public function __construct(
        private readonly ClassDiagramExtractor $extractor,
        private readonly PlantUmlClassDiagramRenderer $renderer,
        private readonly DiagramFileWriter $writer,
        private ?ClassDiagramRendererRegistry $rendererRegistry = null,
    ) {
        $this->rendererRegistry ??= new ClassDiagramRendererRegistry([
            new PlantUmlOutputRenderer($this->renderer),
            new SvgOutputRenderer($this->renderer),
            new PngOutputRenderer($this->renderer),
        ]);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Chemin du fichier a exporter.')
            ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Chemin du dossier contenant les classes a exporter.')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Chemin du dossier de sortie.')
            ->addOption('format', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Format de sortie: puml, plantuml, svg ou png.', ['puml'])
            ->addOption('mode', null, InputOption::VALUE_REQUIRED, 'Mode de sortie: detailed ou architecture.', 'detailed')
            ->addOption('component', null, InputOption::VALUE_REQUIRED, 'Nom de classe, namespace ou module a exporter.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $outputDirectory = $this->stringOption($input, 'output');

        if ($outputDirectory === null || $outputDirectory === '') {
            $io->error('Dossier de sortie manquant');

            return Command::FAILURE;
        }

        $file = $this->stringOption($input, 'file');
        $directory = $this->stringOption($input, 'directory');

        if (($file === null || $file === '') && ($directory === null || $directory === '')) {
            $io->error('Fichier ou dossier source manquant');

            return Command::FAILURE;
        }

        $renderers = $this->resolveRenderers($this->arrayOption($input, 'format'));

        if ($renderers === []) {
            $io->error('Format de sortie non supporte');

            return Command::FAILURE;
        }

        $profile = ClassDiagramRenderProfile::fromMode($this->stringOption($input, 'mode') ?? 'detailed');

        if ($profile === null) {
            $io->error('Mode de sortie non supporte');

            return Command::FAILURE;
        }

        $this->writer->ensureDirectory($outputDirectory);
        $classes = [];
        $component = $this->stringOption($input, 'component');

        try {
            if ($file !== null && $file !== '') {
                $classes = $this->extractor->extractFromFile($file);
                $classes = $this->filterByComponent($classes, $component);
                $this->writeClassFiles($classes, $outputDirectory, $renderers, $profile);
            }

            if ($directory !== null && $directory !== '') {
                $classes = $this->extractor->extractFromDirectory($directory);
                $classes = $this->filterByComponent($classes, $component);
                $this->writeClassFiles($classes, $outputDirectory, $renderers, $profile);
                $this->writeGlobalFiles($classes, $outputDirectory, $renderers, $profile);
            }
        } catch (\RuntimeException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('%d diagramme(s) genere(s).', count($classes)));

        return Command::SUCCESS;
    }

    /**
     * @param list<ClassDiagramClass> $classes
     * @param list<ClassDiagramOutputRendererInterface> $renderers
     */
    private function writeClassFiles(array $classes, string $outputDirectory, array $renderers, ClassDiagramRenderProfile $profile): void
    {
        foreach ($classes as $class) {
            foreach ($renderers as $renderer) {
                $this->writer->write(
                    $outputDirectory,
                    $class->shortName . '.' . $renderer->extension(),
                    $renderer->render([$class], $profile),
                );
            }
        }
    }

    /**
     * @param list<ClassDiagramClass> $classes
     * @param list<ClassDiagramOutputRendererInterface> $renderers
     */
    private function writeGlobalFiles(array $classes, string $outputDirectory, array $renderers, ClassDiagramRenderProfile $profile): void
    {
        foreach ($renderers as $renderer) {
            $this->writer->write(
                $outputDirectory,
                'classes.' . $renderer->extension(),
                $renderer->render($classes, $profile),
            );
        }
    }

    /**
     * @param list<string> $formats
     * @return list<ClassDiagramOutputRendererInterface>
     */
    private function resolveRenderers(array $formats): array
    {
        $renderers = [];

        foreach ($formats === [] ? ['puml'] : $formats as $format) {
            $renderer = $this->rendererRegistry?->get($format);

            if ($renderer === null) {
                return [];
            }

            $renderers[$renderer->format()] = $renderer;
        }

        return array_values($renderers);
    }

    /**
     * @param list<ClassDiagramClass> $classes
     * @return list<ClassDiagramClass>
     */
    private function filterByComponent(array $classes, ?string $component): array
    {
        if ($component === null || $component === '') {
            return $classes;
        }

        $component = strtolower($component);

        return array_values(array_filter(
            $classes,
            static fn (ClassDiagramClass $class): bool => str_contains(strtolower($class->name), $component)
                || str_contains(strtolower($class->shortName), $component),
        ));
    }

    private function stringOption(InputInterface $input, string $name): ?string
    {
        $value = $input->getOption($name);

        return is_string($value) ? $value : null;
    }

    /**
     * @return list<string>
     */
    private function arrayOption(InputInterface $input, string $name): array
    {
        $value = $input->getOption($name);

        if (is_string($value)) {
            return [$value];
        }

        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, is_string(...)));
    }
}
