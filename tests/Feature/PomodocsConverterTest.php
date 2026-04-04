<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TwigRenderer\Tests\Functional;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use PomoDocs\CommonMark\Alert\AlertExtension;
use PomoDocs\CommonMark\TemplateRenderer\PomodocsConverter;

beforeEach(function () {
    $config = [
            'html_input' => 'escape', 
            'templateRenderer' => [
                'engine' => 'twig',
                'templates_dirs' => [
                    $this->root->url()
                ]
            ]
        ];
        
    $this->pomodocs = new PomodocsConverter($config);
    $this->stdConverter = new GithubFlavoredMarkdownConverter(['html_input' => 'escape']);
    $this->stdConverter->getEnvironment()->addExtension(new AlertExtension());
});

it('renders a Github Flavored markdown file', function () {
    $markdown = file_get_contents(__DIR__ . '/../Datasets/GFM.md');
    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->pomodocs->convert($markdown)->getContent();
    
    expect($expected)->toBe($actual);  
});
