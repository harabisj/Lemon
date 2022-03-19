<?php

declare(strict_types=1);

namespace Lemon\Support;

use Lemon\Exceptions\FilesystemException;
use Lemon\Support\Types\Arr;
use Lemon\Support\Types\Str;

class Filesystem
{
    /**
     * Returns content of given file.
     *
     * @throws \Lemon\Exceptions\FilesystemException
     */
    public static function read(string $file): string
    {
        if (! is_file($file)) {
            throw FilesystemException::explainFileNotFound($file);
        }
        
        return file_get_contents($file);
    }

    /**
     * Writes content to given file.
     *
     * @throws \Lemon\Exceptions\FilesystemException
     */
    public static function write(string $file, string $content): void
    {
        if (! is_file($file)) {
            throw FilesystemException::explainFileNotFound($file);
        }
        
        file_put_contents($file, $content);
    }

    /**
     * Creates new directory.
     *
     * @throws \Lemon\Exceptions\FilesystemException
     */
    public static function makeDir(string $dir): void
    {
        if (is_dir($dir)) {
            throw new FilesystemException("Directory {$dir} already exist");
        }

        mkdir($dir, recursive: true);
    }

    /**
     * Creates new file.
     *
     * @throws \Lemon\Exceptions\FilesystemException
     */
    public static function create(string $file): void
    {
        if (is_file($file)) {
            throw new FilesystemException("File {$file} already exist");
        }

        file_put_contents($file, '');
    }

    /**
     * Returns array of paths in given directory.
     */
    public static function listDir(string $dir): array
    {
        if (! self::isDir($dir)) {
            throw FilesystemException::explainDirectoryNotFound($dir);
        }

        $result = [];
        foreach (scandir($dir) as $file) {
            $file = Filesystem::join($dir, $file);
            if (Filesystem::isFile($file)) {
                $result[] = $file;
            }

            if (Filesystem::isDir($file)) {
                $result = Arr::merge(
                    $result,
                    self::listDir($file)
                );
            }
        }

        return $result;
    }

    /**
     * Returns whenever given path is file.
     */
    public static function isFile(string $file): bool
    {
        return is_file($file);
    }

    /**
     * Returns whenever given path is directory.
     */
    public static function isDir(string $dir): bool
    {
        return is_dir($dir);
    }

    /**
     * Deletes given file/directory.
     */
    public static function delete(string $file): void
    {
        if (self::isFile($file)) {
            unlink($file);
        }

        if (self::isDir($file)) {
            foreach (scandir($file) as $sub) {
                self::delete(self::join($file, $sub));
            }
            rmdir($file);
        }
    }

    /**
     * Joins given paths with directory separator.
     */
    public static function join(string ...$paths): string
    {
        return Str::join(
            DIRECTORY_SEPARATOR,
            $paths
        )->value;
    }

    /**
     * Converts path into os-compatible.
     *
     * @param string $path
     *                     return string
     */
    public static function normalize(string $path): string
    {
        $path = rtrim($path, '/\\');

        return preg_replace('/(\\/|\\\)/', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Returns parent of given path.
     */
    public static function parent(string $path)
    {
        $path = self::normalize($path);

        return Str::join(
            DIRECTORY_SEPARATOR,
            Str::split($path, DIRECTORY_SEPARATOR)->slice(0, -2)->value
        );
    }
}
