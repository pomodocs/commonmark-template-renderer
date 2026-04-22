<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TemplateRenderer\Tests\Unit;

use League\CommonMark\Environment\Environment;
use League\Config\Exception\ValidationException;
use PomoDocs\CommonMark\TemplateRenderer\TemplateConverter;
use PomoDocs\CommonMark\TemplateRenderer\TemplateRendererExtension;
use ReflectionMethod;

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

it("throws an error if the specified template engine is latte and it's not available", function () {
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

it("throws an error if the specified template engine is blade and it's not available", function () {
    $env = new Environment(
        [
            'html_input' => 'escape',
            'templateRenderer' => [
                'engine' => 'blade',
            ],
        ],
    );

    $conv = new TemplateConverter($env);
})->throws(
    ValidationException::class,
    "The template engine 'blade' is not available. Please install it by running `composer require eftec/bladeone`.",
);

it("throws an error if the specified template engine is plates and it's not available", function () {
    $env = new Environment(
        [
            'html_input' => 'escape',
            'templateRenderer' => [
                'engine' => 'plates',
            ],
        ],
    );

    $conv = new TemplateConverter($env);
})->throws(
    ValidationException::class,
    "The template engine 'plates' is not available. Please install it by running `composer require league/plates`.",
);

it("throws an error if a specified template directory does not exist", function () {
    $env = new Environment(
        [
            'html_input' => 'escape',
            'templateRenderer' => [
                'engine' => 'twig',
                'templates_dirs' => [
                    $this->root->url(),
                    $this->root->url() . '/nonexistent_dir',
                ],
            ],
        ],
    );

    $conv = new TemplateConverter($env);
})->throws(
    ValidationException::class,
    "The template directory 'vfs://root/nonexistent_dir' does not exist.",
);

it("throws an error if the default template directory for the selected engine does not exist", function () {
    $env = new Environment(
        [
            'html_input' => 'escape',
            'templateRenderer' => [
                'engine' => 'twig',
                'templates_dirs' => [
                    $this->root->url(),
                    __DIR__ . '/../resources/templates/default/twig_nonexistent', // Non-existent default directory
                ],
            ],
        ],
    );

    $conv = new TemplateConverter($env);
})->throws(
    ValidationException::class,
    "The template directory '" . __DIR__ . "/../resources/templates/default/twig_nonexistent' does not exist.",
);

it("gets the template library repository", function (string $engine, string $expectedPackage) {
    $extension = new TemplateRendererExtension();
    $method = new ReflectionMethod(TemplateRendererExtension::class, 'getEnginePackage');
    $enginePackage = $method->invoke($extension, $engine);
    
    expect($enginePackage)->toBe($expectedPackage);
})->with([['twig', 'twig/twig'], ['latte', 'latte/latte'], ['plates', 'league/plates'], ['blade', 'eftec/bladeone']]);

it("gets the template library class name", function (string $engine, string $expectedClass) {
    $extension = new TemplateRendererExtension();
    $method = new ReflectionMethod(TemplateRendererExtension::class, 'getEngineClass');
    $engineClass = $method->invoke($extension, $engine);
    
    expect($engineClass)->toBe($expectedClass);
})->with([['twig', \Twig\Environment::class], ['latte', \Latte\Engine::class], ['plates', \League\Plates\Engine::class], ['blade', \bladeone\BladeOne::class]]);

it("throws an error if the template library class is requested for an unsupported engine", function () {
    $extension = new TemplateRendererExtension();
    $method = new ReflectionMethod(TemplateRendererExtension::class, 'getEngineClass');
    $method->invoke($extension, 'nonexistent_engine');
})->throws(\InvalidArgumentException::class, "Unsupported template engine: nonexistent_engine");

it("throws an error if the template library repository is requested for an unsupported engine", function () {
    $extension = new TemplateRendererExtension();
    $method = new ReflectionMethod(TemplateRendererExtension::class, 'getEnginePackage');
    $method->invoke($extension, 'nonexistent_engine');
})->throws(\InvalidArgumentException::class, "Unsupported template engine: nonexistent_engine");
