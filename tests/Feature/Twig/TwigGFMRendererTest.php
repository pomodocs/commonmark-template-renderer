<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TwigRenderer\Tests\Functional;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use PomoDocs\CommonMark\TemplateRenderer\TemplateConverter;

beforeEach(function () {
    $env = new Environment([
            'html_input' => 'escape', 
            'templateRenderer' => [
                'engine' => 'twig',
                'templates_dirs' => [
                    $this->root->url()
                ]
            ]
        ]
    );
    $env->addExtension(new GithubFlavoredMarkdownExtension());
        
    $this->twigConverter = new TemplateConverter($env);
    $this->stdConverter = new GithubFlavoredMarkdownConverter(['html_input' => 'escape']);
});

it('renders a Github Flavored markdown file', function () {
    $markdown = file_get_contents(__DIR__ . '/../../Datasets/GFM.md');
    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();
    
    expect($expected)->toBe($actual);  
});
