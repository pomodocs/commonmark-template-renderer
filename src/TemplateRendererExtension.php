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
            'engine' => Expect::anyOf('twig', 'latte', 'blade')->default('twig'),
            'templates_dirs' => Expect::listOf('string')->default([]),
        ])->transform(function ($value, Context $context) {
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

    public function register(EnvironmentBuilderInterface $environment): void
    {
        
    }
}
