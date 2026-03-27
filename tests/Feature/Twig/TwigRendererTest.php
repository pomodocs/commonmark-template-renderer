<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TwigRenderer\Tests\Functional;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use PomoDocs\CommonMark\TemplateRenderer\TemplateConverter;
use PomoDocs\CommonMark\TemplateRenderer\TemplateRendererExtension;

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
    $env->addExtension(new CommonMarkCoreExtension());
    $env->addExtension(new GithubFlavoredMarkdownExtension());
    $env->addExtension(new TemplateRendererExtension());
        
    $this->twigConverter = new TemplateConverter($env);
    $this->stdConverter = new GithubFlavoredMarkdownConverter(['html_input' => 'escape']);
});

it('renders a simple markdown string with the Twig converter', function () {
    $markdown = "# Hello World\nThis is a **test** of the Twig converter.";
    $rendered = $this->twigConverter->convert($markdown)->getContent();
    $expected = $this->stdConverter->convert($markdown)->getContent();
    
    expect($rendered)->toBe($expected);
});

it('renders a markdown string with a custom template', function () {
    $markdown = "# Hello World";
    
    $templateContent = <<<TWIG
<h{{ node.level }}{{ node|render_attributes }} class="title">{{ node|render_children }}</h{{ node.level }}>
TWIG;
    $this->createFile('heading.html.twig', $templateContent);

    expect($this->twigConverter->convert($markdown)->getContent())->toBe('<h1 class="title">Hello World</h1>' . "\n");
});

it('renders a markdown string with a separator', function () {
    $markdown = "First line\n\nSecond line";
    
    expect($this->twigConverter->convert($markdown)->getContent())->toBe("<p>First line</p>\n<p>Second line</p>\n");
});

it('renders a markdown string with a separator part for inline nodes', function () {
    $markdown = "This is **bold** text.";
    
    expect($this->twigConverter->convert($markdown)->getContent())->toBe("<p>This is <strong>bold</strong> text.</p>\n");
});

it('renders a standard markdown file', function (string $markdown) {
    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();
    expect($expected)->toBe($actual);
    
})->with([file_get_contents(__DIR__ . '/../../Datasets/StandardMarkdown.md')]);


