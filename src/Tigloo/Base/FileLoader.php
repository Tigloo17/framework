<?php
declare(strict_types=1);

namespace Tigloo\Base;

use DirectoryIterator;
use M1\Env\Parser;
use Tigloo\Collection\Collection;

final class FileLoader
{
    private $pathname;
    private $factory = [];

    public function __construct(string $path = null)
    {
        $this->setPathName($path);
    }
    
    public function setPathName(string $path = null): FileLoader
    {
        $this->pathname = $path;
        return $this;
    }

    public function load(): FileLoader
    {
        if (null === $this->pathname || ! file_exists($this->pathname)) {
            throw new \RuntimeException('nexiste pas');
        }

        if (is_file($this->pathname)) {
            $this->factory = $this->openFile($this->pathname);
        } elseif (is_dir($this->pathname)) {
            $this->factory = $this->openDirectory($this->pathname);
        }

        return $this;
    }

    public function output(): array
    {
        return $this->factory;
    }

    public function outputCollection(?array $content = null): Collection
    {
        $content = (is_null($content)) ? $this->factory : $content;
        
        foreach ($content as $key => $item) {
            if (is_array($item)) {
                if (is_numeric($key)) {
                    $key = key($item);
                    $item = $item[$key];
                }

                $collection[$key] = $this->outputCollection($item);
                continue;
            }
            
            $collection[$key] = $item;
        }
        
        return new Collection($collection ?? []);
    }

    private function openDirectory(string $path)
    {
        foreach (new DirectoryIterator($path) as $item) {
            if (! $item->isDot()) {
                if ($item->isDir()) {
                    $factory[] = $this->openDirectory($item->getPathname());
                } elseif ($item->isFile()) {
                    $factory[] = $this->openFile($item->getPathname());
                }
            }
        }

        return $factory ?? [];
    }

    private function openFile(string $filename)
    {
        $info = pathinfo($filename);
        // Ajouter d'autre extension au besoin.
        switch ($info['extension']) {
            case 'php':
                return @include $filename;
                break;
            case 'env':
                return (new Parser(file_get_contents($filename), $_ENV))->getContent();
                break;
        }
    }
}