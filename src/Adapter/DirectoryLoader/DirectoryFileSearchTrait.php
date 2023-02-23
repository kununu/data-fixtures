<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter\DirectoryLoader;

use ReflectionClass;

trait DirectoryFileSearchTrait
{
    protected function fileNames(): array
    {
        return $this->searchFileNames($this->getFileExtension(), $this->getDirectory());
    }

    protected function searchFileNames(string $extension, string $subDirectory): array
    {
        $root = $this->root($subDirectory);
        $files = [];
        if ($handle = opendir($root)) {
            while (false !== ($file = readdir($handle))) {
                if ('.' !== $file &&
                    '..' !== $file &&
                    $extension === strtolower(substr($file, strrpos($file, '.') + 1))
                ) {
                    $files[] = sprintf('%s/%s', $root, $file);
                }
            }
            closedir($handle);
        }
        sort($files);

        return $files;
    }

    abstract protected function getFileExtension(): string;

    abstract protected function getDirectory(): string;

    private function root(string $subDirectory): string
    {
        [, , $feature] = array_slice(explode('\\', static::class), -3);

        return sprintf('%s/%s/%s', dirname((new ReflectionClass($this))->getFilename()), $subDirectory, $feature);
    }
}
