<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Exception\InvalidFileException;

abstract class ConnectionSqlFixture implements ConnectionFixtureInterface
{
    final public function load(Connection $connection): void
    {
        foreach ($this->fileNames() as $fileName) {
            $file = new \SplFileInfo($fileName);

            if ($file->getExtension() !== 'sql') {
                continue;
            }

            foreach ($this->getSql($file) as $sql) {
                if (empty($sql)) {
                    continue;
                }
                $connection->exec($sql);
            }
        }
    }

    abstract protected function fileNames() : array;

    private function getSql(\SplFileInfo $fileInfo) : array
    {
        return array_map('trim', (explode(";", $this->getFileContents($fileInfo))));
    }

    private function getFileContents(\SplFileInfo $fileInfo) : string
    {
        set_error_handler(function ($type, $msg) use (&$error) {
            $error = $msg;
        });
        $content = file_get_contents($fileInfo->getPathname());
        restore_error_handler();

        if (false === $content) {
            throw new InvalidFileException($error);
        }

        return $content;
    }
}
