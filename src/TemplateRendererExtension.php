<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TemplateRenderer;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Context;
use Nette\Schema\Expect;

final class TemplateRendererExtension implements ConfigurableExtensionInterface
{
    /**
     * Configure the Template Renderer extension.
     */
    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->addSchema('templateRenderer', Expect::structure([
            'engine' => Expect::anyOf('twig', 'latte', 'plates', 'blade')->default('twig'),
            'templates_dirs' => Expect::listOf('string')->default([]),
        ])->transform(function ($value, Context $context) {
            if (!class_exists($this->getEngineClass($value->engine))) {
                $context->addError("The template engine '{$value->engine}' is not available. Please install it by running `composer require {$this->getEnginePackage($value->engine)}`.", 'templateRenderer.engine');
            }

            // Add the default templates directory for the selected engine
            $value->templates_dirs[] = __DIR__ . '/../resources/templates/default/' . $value->engine;
            foreach ($value->templates_dirs as $dir) {
                if (!is_dir($dir)) {
                    $context->addError("The template directory '$dir' does not exist.", 'templateRenderer.templates_dirs');
                }
            }

            return $value;
        }));
    }

    public function register(EnvironmentBuilderInterface $environment): void {}

    /**
     * Get the class name for the specified template engine.
     *
     * @param string $engine The name of the template engine (e.g., 'twig', 'latte', 'plates', 'blade').
     * @return string The fully qualified class name of the template engine.
     */
    private function getEngineClass(string $engine): string
    {
        return match ($engine) {
            'twig' => \Twig\Environment::class,
            'latte' => \Latte\Engine::class,
            'plates' => \League\Plates\Engine::class,
            'blade' => \bladeone\BladeOne::class,
        };
    }

    /**
     * Get the Composer package name for the specified template engine.
     *
     * @param string $engine The name of the template engine (e.g., 'twig', 'latte', 'plates', 'blade').
     * @return string The Composer package name required for the template engine.
     */
    private function getEnginePackage(string $engine): string
    {
        return match ($engine) {
            'twig' => 'twig/twig',
            'latte' => 'latte/latte',
            'plates' => 'league/plates',
            'blade' => 'eftec/bladeone',
        };
    }
}
