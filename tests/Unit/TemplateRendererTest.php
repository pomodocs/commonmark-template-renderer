<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TemplateRenderer\Tests\Unit;

use League\CommonMark\Environment\EnvironmentInterface;
use League\Config\ConfigurationInterface;
use PomoDocs\CommonMark\TemplateRenderer\TemplateRenderer;

it('throws an exception for unsupported template engines', function () {
    $environment = $this->createMock(EnvironmentInterface::class);
    $environment->method('getConfiguration')->willReturn(
        new class implements ConfigurationInterface {
            public function get(string $key, mixed $default = null): mixed
            {
                return 'unsupported_engine';
            }

            public function has(string $key): bool
            {
                return true;
            }

            public function exists(string $key): bool
            {
                return true;
            }
        },
    );

    new TemplateRenderer($environment);
})->throws(\InvalidArgumentException::class, 'Unsupported template engine: unsupported_engine');
