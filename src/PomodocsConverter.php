<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TemplateRenderer;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use PomoDocs\CommonMark\Alert\AlertExtension;
use PomoDocs\CommonMark\TemplateRenderer\TemplateConverter;

/**
 * An already configured converter for Github Flavored Markdown with the TemplateRenderer
 * and `pomodocs/commonmark-alert` extensions.
 * 
 * @see https://github.com/pomodocs/commonmark-alert
 */
final class PomodocsConverter extends TemplateConverter
{
    /**
     * Create a new Markdown converter pre-configured for GFM
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new TemplateRendererExtension());
        $environment->addExtension(new AlertExtension());

        parent::__construct($environment);
    }
}
