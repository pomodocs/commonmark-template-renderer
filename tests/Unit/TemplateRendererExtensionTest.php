<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TwigRenderer\Tests\Functional;

use InvalidArgumentException;
use League\CommonMark\Environment\Environment;
use League\Config\Exception\ValidationException;
use PomoDocs\CommonMark\TemplateRenderer\TemplateConverter;

it("configures the template renderer extension", function () {
    $env = new Environment(
        [
            'html_input' => 'escape',
            'templateRenderer' => [
                'engine' => 'twig',
                'templates_dirs' => [
                    $this->root->url(),
                ],
            ],
        ],
    );

    $converter = new TemplateConverter($env);
    expect(true)->toBeTrue(); // Just ensure no exceptions are thrown during configuration
});

it("configures the template renderer extension with default values", function () {
    $converter = new TemplateConverter(new Environment());
    expect(true)->toBeTrue(); // Just ensure no exceptions are thrown during configuration
});

it("throws an exception if the specified template engine is not supported", function () {
    $env = new Environment(
        [
            'html_input' => 'escape',
            'templateRenderer' => [
                'engine' => 'nonexistent_engine',
            ],
        ],
    );

    $conv = new TemplateConverter($env);
})->throws(
    ValidationException::class,
    "The item 'templateRenderer › engine' expects to be 'twig'|'latte'|'plates'|'blade', 'nonexistent...' given.",
);

it("throws an error if the specified template engine is not available", function () {
    $env = new Environment(
        [
            'html_input' => 'escape',
            'templateRenderer' => [
                'engine' => 'latte',
            ],
        ],
    );

    $conv = new TemplateConverter($env);
})->throws(
    ValidationException::class,
    "The template engine 'latte' is not available. Please install it by running `composer require latte/latte`.",
);
