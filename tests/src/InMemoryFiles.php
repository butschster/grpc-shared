<?php

declare(strict_types=1);

namespace Tests;

use Spiral\Files\FilesInterface;

final class InMemoryFiles implements FilesInterface
{
    public function __construct(
        private array $files = [],
        private array $fileModes = [],
    ) {
    }

    public function assertFileExists(string $filename): void
    {
        \PHPUnit\Framework\TestCase::assertTrue(
            $this->exists($filename),
            'File "' . $filename . '" does not exist'
        );
    }

    public function assertFileContentSame(string $filename, string $content): void
    {
        $this->assertFileExists($filename);

        \PHPUnit\Framework\TestCase::assertSame(
            $content,
            $this->read($filename),
            'File "' . $filename . '" content is not same'
        );
    }

    public function assertFileContentContains(string $filename, string $content): void
    {
        $this->assertFileExists($filename);

        \PHPUnit\Framework\TestCase::assertStringContainsString(
            $content,
            $this->read($filename),
            'File "' . $filename . '" content does not contain "' . $content . '"'
        );
    }

    public function ensureDirectory(string $directory, int $mode = null): bool
    {
        return true;
    }

    public function read(string $filename): string
    {
        return $this->files[$filename] ?? '';
    }

    public function write(string $filename, string $data, int $mode = null, bool $ensureDirectory = false): bool
    {
        $this->files[$filename] = $data;
        $this->fileModes[$filename] = $mode;

        return true;
    }

    public function append(string $filename, string $data, int $mode = null, bool $ensureDirectory = false): bool
    {
        $data = $this->files[$filename] ?? '';
        $data .= $data;
        $this->files[$filename] = $data;
        $this->fileModes[$filename] = $mode;

        return true;
    }

    public function delete(string $filename): bool
    {
        unset($this->files[$filename]);
        unset($this->fileModes[$filename]);

        return true;
    }

    public function deleteDirectory(string $directory, bool $contentOnly = false): bool
    {
        return true;
    }

    public function move(string $filename, string $destination): bool
    {
        if (isset($this->files[$filename])) {
            $this->files[$destination] = $this->files[$filename];
            if (isset($this->fileModes[$filename])) {
                $this->fileModes[$destination] = $this->fileModes[$filename];
            }
            unset($this->files[$filename]);
            unset($this->fileModes[$filename]);
            return true;
        }

        return false;
    }

    public function copy(string $filename, string $destination): bool
    {
        if (isset($this->files[$filename])) {
            $this->files[$destination] = $this->files[$filename];
            if (isset($this->fileModes[$filename])) {
                $this->fileModes[$destination] = $this->fileModes[$filename];
            }

            return true;
        }

        return false;
    }

    public function touch(string $filename, int $mode = null): bool
    {
        $this->files[$filename] = '';
        $this->fileModes[$filename] = $mode;

        return true;
    }

    public function exists(string $filename): bool
    {
        return isset($this->files[$filename]);
    }

    public function size(string $filename): int
    {
        return \strlen($this->files[$filename] ?? '');
    }

    public function extension(string $filename): string
    {
        return \pathinfo($filename, \PATHINFO_EXTENSION);
    }

    public function md5(string $filename): string
    {
        return \md5($this->files[$filename] ?? '');
    }

    public function time(string $filename): int
    {
        return \time();
    }

    public function isDirectory(string $filename): bool
    {
        return false;
    }

    public function isFile(string $filename): bool
    {
        return isset($this->files[$filename]);
    }

    public function getPermissions(string $filename): int
    {
        return $this->fileModes[$filename] ?? 0;
    }

    public function setPermissions(string $filename, int $mode): bool
    {
        $this->fileModes[$filename] = $mode;

        return true;
    }

    public function getFiles(string $location, string $pattern = null): array
    {
        return \array_keys($this->files);
    }

    public function tempFilename(string $extension = '', string $location = null): string
    {
    }

    public function normalizePath(string $path, bool $asDirectory = false): string
    {
        return $path;
    }

    public function relativePath(string $path, string $from): string
    {
        return $path;
    }
}
