<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Tests\Command;

use ChrisDev\UxComponents\Command\ExportPlantUmlDiagramCommand;
use ChrisDev\UxComponents\Diagram\ClassDiagram\ClassDiagramExtractor;
use ChrisDev\UxComponents\Diagram\ClassDiagram\ClassDiagramRendererRegistry;
use ChrisDev\UxComponents\Diagram\ClassDiagram\DiagramFileWriter;
use ChrisDev\UxComponents\Diagram\ClassDiagram\PlantUmlCliRenderer;
use ChrisDev\UxComponents\Diagram\ClassDiagram\PlantUmlClassDiagramRenderer;
use ChrisDev\UxComponents\Diagram\ClassDiagram\PlantUmlOutputRenderer;
use ChrisDev\UxComponents\Diagram\ClassDiagram\PngOutputRenderer;
use ChrisDev\UxComponents\Diagram\ClassDiagram\SvgOutputRenderer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ExportPlantUmlDiagramCommandTest extends TestCase
{
    private string $workspace;

    protected function setUp(): void
    {
        $this->workspace = sys_get_temp_dir() . '/ux-components-diagram-' . bin2hex(random_bytes(6));
        mkdir($this->workspace, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->workspace);
    }

    public function testItFailsWhenOutputDirectoryIsMissing(): void
    {
        $inputFile = $this->writeFile('InputClass.php', <<<'PHP'
            <?php

            namespace App\Demo;

            final class InputClass
            {
            }
            PHP);

        $tester = new CommandTester($this->createCommandWithMissingPlantUmlCli());
        $statusCode = $tester->execute([
            '-f' => $inputFile,
        ]);

        self::assertSame(Command::FAILURE, $statusCode);
        self::assertStringContainsString('Dossier de sortie manquant', $tester->getDisplay());
    }

    public function testItExportsOnePlantUmlFileFromOnePhpFile(): void
    {
        $inputFile = $this->writeFile('DemoCard.php', <<<'PHP'
            <?php

            namespace App\Demo;

            final class DemoCard implements RenderableCard
            {
                public string $title;

                public function render(): string
                {
                    return $this->title;
                }
            }
            PHP);
        $outputDirectory = $this->workspace . '/exports';

        $tester = new CommandTester($this->createCommand());
        $statusCode = $tester->execute([
            '-f' => $inputFile,
            '-o' => $outputDirectory,
        ]);

        self::assertSame(Command::SUCCESS, $statusCode);

        $diagramPath = $outputDirectory . '/DemoCard.puml';
        self::assertFileExists($diagramPath);

        $diagram = (string) file_get_contents($diagramPath);
        self::assertStringContainsString('@startuml', $diagram);
        self::assertStringContainsString('class "App\\Demo\\DemoCard"', $diagram);
        self::assertStringContainsString('+title : string', $diagram);
        self::assertStringContainsString('+render() : string', $diagram);
        self::assertStringContainsString('"App\\Demo\\DemoCard" ..|> "App\\Demo\\RenderableCard"', $diagram);
    }

    public function testItExportsEachClassAndGlobalDiagramFromDirectory(): void
    {
        $sourceDirectory = $this->workspace . '/src';
        mkdir($sourceDirectory . '/Nested', 0777, true);

        $this->writeFile('src/BaseWidget.php', <<<'PHP'
            <?php

            namespace App\Demo;

            abstract class BaseWidget
            {
            }
            PHP);
        $this->writeFile('src/Nested/PrimaryWidget.php', <<<'PHP'
            <?php

            namespace App\Demo\Nested;

            use App\Demo\BaseWidget;

            final class PrimaryWidget extends BaseWidget
            {
                private BaseWidget $parent;
            }
            PHP);
        $outputDirectory = $this->workspace . '/exports';

        $tester = new CommandTester($this->createCommandWithPlantUmlCli());
        $statusCode = $tester->execute([
            '-d' => $sourceDirectory,
            '-o' => $outputDirectory,
        ]);

        self::assertSame(Command::SUCCESS, $statusCode);
        self::assertFileExists($outputDirectory . '/BaseWidget.puml');
        self::assertFileExists($outputDirectory . '/PrimaryWidget.puml');
        self::assertFileExists($outputDirectory . '/classes.puml');

        $globalDiagram = (string) file_get_contents($outputDirectory . '/classes.puml');
        self::assertStringContainsString('class "App\\Demo\\BaseWidget"', $globalDiagram);
        self::assertStringContainsString('class "App\\Demo\\Nested\\PrimaryWidget"', $globalDiagram);
        self::assertStringContainsString('"App\\Demo\\Nested\\PrimaryWidget" --|> "App\\Demo\\BaseWidget"', $globalDiagram);
        self::assertStringContainsString('"App\\Demo\\Nested\\PrimaryWidget" --> "App\\Demo\\BaseWidget"', $globalDiagram);
    }

    public function testItExportsSvgWhenRequested(): void
    {
        $inputFile = $this->writeFile('SvgDemo.php', <<<'PHP'
            <?php

            namespace App\Demo;

            final class SvgDemo
            {
            }
            PHP);
        $outputDirectory = $this->workspace . '/exports';

        $tester = new CommandTester($this->createCommandWithPlantUmlCli());
        $statusCode = $tester->execute([
            '-f' => $inputFile,
            '-o' => $outputDirectory,
            '--format' => 'svg',
        ]);

        self::assertSame(Command::SUCCESS, $statusCode);

        $diagramPath = $outputDirectory . '/SvgDemo.svg';
        self::assertFileExists($diagramPath);

        $diagram = (string) file_get_contents($diagramPath);
        self::assertStringStartsWith('<svg', $diagram);
        self::assertStringContainsString('Rendered PlantUML', $diagram);
    }

    public function testItExportsPromotedConstructorProperties(): void
    {
        $inputFile = $this->writeFile('ReadonlyResult.php', <<<'PHP'
            <?php

            namespace App\Demo;

            final readonly class ReadonlyResult
            {
                public function __construct(
                    public array $rows,
                    public int $totalItems,
                    public int $currentPage = 1,
                ) {
                }
            }
            PHP);
        $outputDirectory = $this->workspace . '/exports';

        $tester = new CommandTester($this->createCommandWithPlantUmlCli());
        $statusCode = $tester->execute([
            '-f' => $inputFile,
            '-o' => $outputDirectory,
        ]);

        self::assertSame(Command::SUCCESS, $statusCode);

        $diagram = (string) file_get_contents($outputDirectory . '/ReadonlyResult.puml');
        self::assertStringContainsString('class "App\\Demo\\ReadonlyResult" <<final>> <<readonly>>', $diagram);
        self::assertStringContainsString('+rows : array', $diagram);
        self::assertStringContainsString('+totalItems : int', $diagram);
        self::assertStringContainsString('+currentPage : int', $diagram);
        self::assertStringNotContainsString('+__construct()', $diagram);
    }

    public function testItKeepsInterfaceMethodsAsMembersOnly(): void
    {
        $sourceDirectory = $this->workspace . '/src';
        mkdir($sourceDirectory, 0777, true);

        $this->writeFile('src/DataTableResult.php', <<<'PHP'
            <?php

            namespace App\Demo;

            final readonly class DataTableResult
            {
            }
            PHP);
        $this->writeFile('src/DataSourceInterface.php', <<<'PHP'
            <?php

            namespace App\Demo;

            interface DataSourceInterface
            {
                public function supports(mixed $source): bool;

                public function fetch(mixed $source): DataTableResult;
            }
            PHP);
        $this->writeFile('src/ArrayDataSource.php', <<<'PHP'
            <?php

            namespace App\Demo;

            final class ArrayDataSource implements DataSourceInterface
            {
                public function supports(mixed $source): bool
                {
                    return is_array($source);
                }

                public function fetch(mixed $source): DataTableResult
                {
                    return new DataTableResult();
                }
            }
            PHP);
        $outputDirectory = $this->workspace . '/exports';

        $tester = new CommandTester($this->createCommandWithPlantUmlCli());
        $statusCode = $tester->execute([
            '-d' => $sourceDirectory,
            '-o' => $outputDirectory,
        ]);

        self::assertSame(Command::SUCCESS, $statusCode);

        $interfaceDiagram = (string) file_get_contents($outputDirectory . '/DataSourceInterface.puml');
        self::assertStringContainsString('interface "App\\Demo\\DataSourceInterface"', $interfaceDiagram);
        self::assertStringContainsString('+supports(source: mixed) : bool', $interfaceDiagram);
        self::assertStringContainsString('+fetch(source: mixed) : DataTableResult', $interfaceDiagram);
        self::assertStringNotContainsString('+source : supports(mixed', $interfaceDiagram);
        self::assertStringNotContainsString('"App\\Demo\\DataSourceInterface" --> "App\\Demo\\supports(mixed"', $interfaceDiagram);
        self::assertStringNotContainsString('"App\\Demo\\DataSourceInterface" --> "App\\Demo\\fetch(mixed"', $interfaceDiagram);

        $globalDiagram = (string) file_get_contents($outputDirectory . '/classes.puml');
        self::assertStringContainsString('"App\\Demo\\ArrayDataSource" ..|> "App\\Demo\\DataSourceInterface"', $globalDiagram);
        self::assertStringNotContainsString('"App\\Demo\\supports(mixed"', $globalDiagram);
        self::assertStringNotContainsString('"App\\Demo\\fetch(mixed"', $globalDiagram);
    }

    public function testItCanExportArchitectureModeWithoutMembers(): void
    {
        $sourceDirectory = $this->workspace . '/src';
        mkdir($sourceDirectory, 0777, true);

        $this->writeFile('src/Contract.php', <<<'PHP'
            <?php

            namespace App\Demo;

            interface Contract
            {
                public function execute(string $value): ExecutionResult;
            }
            PHP);
        $this->writeFile('src/ExecutionResult.php', <<<'PHP'
            <?php

            namespace App\Demo;

            final readonly class ExecutionResult
            {
            }
            PHP);
        $this->writeFile('src/Service.php', <<<'PHP'
            <?php

            namespace App\Demo;

            final class Service implements Contract
            {
                private ExecutionResult $lastResult;

                public function execute(string $value): ExecutionResult
                {
                    $this->lastResult = new ExecutionResult();

                    return $this->lastResult;
                }
            }
            PHP);
        $outputDirectory = $this->workspace . '/exports';

        $tester = new CommandTester($this->createCommandWithPlantUmlCli());
        $statusCode = $tester->execute([
            '-d' => $sourceDirectory,
            '-o' => $outputDirectory,
            '--mode' => 'architecture',
        ]);

        self::assertSame(Command::SUCCESS, $statusCode);

        $globalDiagram = (string) file_get_contents($outputDirectory . '/classes.puml');
        self::assertStringContainsString('interface "App\\Demo\\Contract"', $globalDiagram);
        self::assertStringContainsString('class "App\\Demo\\Service" <<final>>', $globalDiagram);
        self::assertStringContainsString('class "App\\Demo\\ExecutionResult" <<final>> <<readonly>>', $globalDiagram);
        self::assertStringContainsString('"App\\Demo\\Service" ..|> "App\\Demo\\Contract"', $globalDiagram);
        self::assertStringNotContainsString('+execute(value: string) : ExecutionResult', $globalDiagram);
        self::assertStringNotContainsString('"App\\Demo\\Contract" --> "App\\Demo\\ExecutionResult"', $globalDiagram);
        self::assertStringNotContainsString('"App\\Demo\\Service" --> "App\\Demo\\ExecutionResult"', $globalDiagram);
    }

    public function testItExportsMultipleFormatsIncludingPng(): void
    {
        $inputFile = $this->writeFile('MultiFormatDemo.php', <<<'PHP'
            <?php

            namespace App\Demo;

            final class MultiFormatDemo
            {
                public function label(): string
                {
                    return 'Demo';
                }
            }
            PHP);
        $outputDirectory = $this->workspace . '/exports';

        $tester = new CommandTester($this->createCommandWithPlantUmlCli());
        $statusCode = $tester->execute([
            '-f' => $inputFile,
            '-o' => $outputDirectory,
            '--format' => ['puml', 'svg', 'png'],
        ]);

        self::assertSame(Command::SUCCESS, $statusCode);
        self::assertFileExists($outputDirectory . '/MultiFormatDemo.puml');
        self::assertFileExists($outputDirectory . '/MultiFormatDemo.svg');
        self::assertFileExists($outputDirectory . '/MultiFormatDemo.png');

        self::assertStringStartsWith('@startuml', (string) file_get_contents($outputDirectory . '/MultiFormatDemo.puml'));
        self::assertStringStartsWith('<svg', (string) file_get_contents($outputDirectory . '/MultiFormatDemo.svg'));
        self::assertSame("\x89PNG\r\n\x1A\n", substr((string) file_get_contents($outputDirectory . '/MultiFormatDemo.png'), 0, 8));
    }

    public function testItFailsClearlyWhenPlantUmlCliIsMissingForImageFormats(): void
    {
        $inputFile = $this->writeFile('MissingCliDemo.php', <<<'PHP'
            <?php

            namespace App\Demo;

            final class MissingCliDemo
            {
            }
            PHP);
        $outputDirectory = $this->workspace . '/exports';

        $tester = new CommandTester($this->createCommand());
        $statusCode = $tester->execute([
            '-f' => $inputFile,
            '-o' => $outputDirectory,
            '--format' => 'svg',
        ]);

        self::assertSame(Command::FAILURE, $statusCode);
        self::assertStringContainsString('PlantUML rendering requires the plantuml CLI', $tester->getDisplay());
        self::assertStringContainsString('--format=puml or install plantuml', $tester->getDisplay());
    }

    public function testItFiltersDirectoryExportByComponent(): void
    {
        $sourceDirectory = $this->workspace . '/src';
        mkdir($sourceDirectory . '/DataTable', 0777, true);
        mkdir($sourceDirectory . '/Twig/Components', 0777, true);

        $this->writeFile('src/DataTable/DataTableResult.php', <<<'PHP'
            <?php

            namespace App\Demo\DataTable;

            final readonly class DataTableResult
            {
            }
            PHP);
        $this->writeFile('src/Twig/Components/DataTable.php', <<<'PHP'
            <?php

            namespace App\Demo\Twig\Components;

            use App\Demo\DataTable\DataTableResult;

            final class DataTable
            {
                private DataTableResult $result;
            }
            PHP);
        $this->writeFile('src/Twig/Components/Card.php', <<<'PHP'
            <?php

            namespace App\Demo\Twig\Components;

            final class Card
            {
            }
            PHP);
        $outputDirectory = $this->workspace . '/exports';

        $tester = new CommandTester($this->createCommand());
        $statusCode = $tester->execute([
            '-d' => $sourceDirectory,
            '-o' => $outputDirectory,
            '--component' => 'DataTable',
        ]);

        self::assertSame(Command::SUCCESS, $statusCode);
        self::assertFileExists($outputDirectory . '/DataTable.puml');
        self::assertFileExists($outputDirectory . '/DataTableResult.puml');
        self::assertFileDoesNotExist($outputDirectory . '/Card.puml');

        $globalDiagram = (string) file_get_contents($outputDirectory . '/classes.puml');
        self::assertStringContainsString('class "App\\Demo\\Twig\\Components\\DataTable"', $globalDiagram);
        self::assertStringContainsString('class "App\\Demo\\DataTable\\DataTableResult"', $globalDiagram);
        self::assertStringNotContainsString('class "App\\Demo\\Twig\\Components\\Card"', $globalDiagram);
    }

    private function createCommand(): ExportPlantUmlDiagramCommand
    {
        return new ExportPlantUmlDiagramCommand(
            new ClassDiagramExtractor(),
            new PlantUmlClassDiagramRenderer(),
            new DiagramFileWriter(),
        );
    }

    private function createCommandWithPlantUmlCli(): ExportPlantUmlDiagramCommand
    {
        $plantUmlRenderer = new PlantUmlClassDiagramRenderer();
        $cliRenderer = new PlantUmlCliRenderer($this->createFakePlantUmlCli());

        return new ExportPlantUmlDiagramCommand(
            new ClassDiagramExtractor(),
            $plantUmlRenderer,
            new DiagramFileWriter(),
            new ClassDiagramRendererRegistry([
                new PlantUmlOutputRenderer($plantUmlRenderer),
                new SvgOutputRenderer($plantUmlRenderer, $cliRenderer),
                new PngOutputRenderer($plantUmlRenderer, $cliRenderer),
            ]),
        );
    }

    private function createCommandWithMissingPlantUmlCli(): ExportPlantUmlDiagramCommand
    {
        $plantUmlRenderer = new PlantUmlClassDiagramRenderer();
        $cliRenderer = new PlantUmlCliRenderer($this->workspace . '/missing-plantuml');

        return new ExportPlantUmlDiagramCommand(
            new ClassDiagramExtractor(),
            $plantUmlRenderer,
            new DiagramFileWriter(),
            new ClassDiagramRendererRegistry([
                new PlantUmlOutputRenderer($plantUmlRenderer),
                new SvgOutputRenderer($plantUmlRenderer, $cliRenderer),
                new PngOutputRenderer($plantUmlRenderer, $cliRenderer),
            ]),
        );
    }

    private function createFakePlantUmlCli(): string
    {
        $path = $this->workspace . '/plantuml';
        file_put_contents($path, <<<'PHP'
#!/usr/bin/env php
<?php

$format = null;
$source = null;

foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '-t')) {
        $format = substr($argument, 2);
        continue;
    }

    $source = $argument;
}

if ($format === null || $source === null) {
    exit(1);
}

$output = substr($source, 0, -5) . '.' . $format;

if ($format === 'svg') {
    file_put_contents($output, '<svg xmlns="http://www.w3.org/2000/svg"><text>Rendered PlantUML</text></svg>');
    exit(0);
}

if ($format === 'png') {
    file_put_contents($output, "\x89PNG\r\n\x1A\nrendered");
    exit(0);
}

exit(1);
PHP);
        chmod($path, 0755);

        return $path;
    }

    private function writeFile(string $relativePath, string $contents): string
    {
        $path = $this->workspace . '/' . $relativePath;
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($path, $contents);

        return $path;
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            if (!$file instanceof \SplFileInfo) {
                continue;
            }

            if ($file->isDir()) {
                rmdir($file->getPathname());

                continue;
            }

            unlink($file->getPathname());
        }

        rmdir($directory);
    }
}
