<?php

declare(strict_types=1);

namespace PomoDocs\CommonMark\TemplateRenderer\Tests;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public private(set) vfsStreamDirectory $root {
        get => $this->root ??= vfsStream::setup('root');
    }

    public function createFile(string $path, string $content = ''): void
    {
        $dirs = explode('/', $path);
        $fileName = array_pop($dirs);
        $currentDir = $this->root;

        foreach ($dirs as $dir) {
            if (!$currentDir->hasChild($dir)) {
                $currentDir->addChild(vfsStream::newDirectory($dir));
            }
            $currentDir = $currentDir->getChild($dir);
        }

        $currentDir->addChild(vfsStream::newFile($fileName)->withContent($content));
    }
}
