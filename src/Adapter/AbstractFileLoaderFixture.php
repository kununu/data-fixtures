<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use InvalidArgumentException;
use Kununu\DataFixtures\Exception\InvalidFileException;
use SplFileInfo;
use Throwable;

abstract class AbstractFileLoaderFixture
{
    protected function loadFiles(callable $contentLoader): void
    {
        $extension = $this->getFileExtension();
        $loadMode = $this->getLoadMode();

        foreach ($this->fileNames() as $fileName) {
            $file = new SplFileInfo($fileName);

            if ($file->getExtension() !== $extension) {
                continue;
            }

            $content = match ($loadMode) {
                LoadMode::Include  => $this->includeFile($fileName),
                LoadMode::Load     => $this->loadFile($file),
                LoadMode::LoadJson => $this->loadJson($file, $fileName),
            };

            $contentLoader($content);
        }
    }

    abstract protected function fileNames(): array;

    abstract protected function getFileExtension(): string;

    abstract protected function getLoadMode(): LoadMode;

    private function loadJson(SplFileInfo $file, string $fileName): array
    {
        if (is_string($content = $this->loadFile($file))) {
            try {
                $content = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
            } catch (Throwable) {
                throw new InvalidArgumentException(sprintf('Error decoding JSON file: "%s"', $fileName));
            }

            return $content;
        }

        return [];
    }

    private function includeFile(string $fileName): array
    {
        if (file_exists($fileName) && is_readable($fileName)) {
            try {
                return include $fileName;
            } catch (Throwable $e) {
                throw new InvalidFileException($e->getMessage(), $e->getCode());
            }
        }

        throw new InvalidFileException(sprintf('Invalid file: %s', $fileName));
    }

    private function loadFile(SplFileInfo $fileInfo): ?string
    {
        $contents = trim($this->getFileContent($fileInfo));

        return $contents === '' ? null : $contents;
    }

    private function getFileContent(SplFileInfo $fileInfo): string
    {
        set_error_handler(function(int $type, string $msg) use (&$errorNumber, &$error): void {
            $errorNumber = $type;
            $error = $msg;
        });

        $content = file_get_contents($fileInfo->getPathname());
        restore_error_handler();

        if (false === $content) {
            throw new InvalidFileException($error, $errorNumber);
        }

        return $content;
    }
}
