<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use InvalidArgumentException;
use Kununu\DataFixtures\Exception\InvalidFileException;
use SplFileInfo;
use Throwable;

abstract class AbstractFileLoaderFixture
{
    protected const LOAD_MODE_INCLUDE = 'include';
    protected const LOAD_MODE_LOAD = 'load';
    protected const LOAD_MODE_LOAD_JSON = 'loadJson';

    abstract protected function fileNames(): array;

    abstract protected function getFileExtension(): string;

    abstract protected function getLoadMode(): string;

    protected function loadFiles(callable $contentLoader): void
    {
        $extension = $this->getFileExtension();
        $loadMode = $this->getLoadMode();

        foreach ($this->fileNames() as $fileName) {
            $file = new SplFileInfo($fileName);

            if ($file->getExtension() !== $extension) {
                continue;
            }

            switch ($loadMode) {
                case self::LOAD_MODE_INCLUDE:
                    $content = $this->includeFile($fileName);
                    break;
                case self::LOAD_MODE_LOAD:
                    $content = $this->loadFile($file);
                    break;
                case self::LOAD_MODE_LOAD_JSON:
                    if (is_string($content = $this->loadFile($file))) {
                        $content = json_decode($content, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new InvalidArgumentException(sprintf('Error decoding JSON file: "%s"', $fileName));
                        }
                    } else {
                        $content = [];
                    }

                    break;
                default:
                    throw new InvalidArgumentException(sprintf('Invalid load mode: "%s"', $loadMode));
            }

            $contentLoader($content);
        }
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
