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

            if ($sql = $this->getSql($file)) {
                $connection->executeStatement($sql);
            }
        }
    }

    abstract protected function fileNames() : array;

    private function getSql(\SplFileInfo $fileInfo) : ?string
    {
        $contents = trim($this->getFileContents($fileInfo));

        return $contents !== '' ? $contents : null;
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
