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
use League\CommonMark\Extension\DescriptionList\DescriptionListExtension;
use League\CommonMark\Extension\Embed\EmbedAdapterInterface;
use League\CommonMark\Extension\Embed\EmbedExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Highlight\HighlightExtension;
use League\CommonMark\Extension\Mention\MentionExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use PomoDocs\CommonMark\TemplateRenderer\TemplateConverter;

beforeEach(function () {
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
    $env->addExtension(new CommonMarkCoreExtension());

    $this->twigConverter = new TemplateConverter($env);
    $this->stdConverter = new CommonMarkConverter(['html_input' => 'escape']);
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

it('renders a standard markdown file', function () {
    $markdown = file_get_contents(__DIR__ . '/../../Datasets/StandardMarkdown.md');
    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();

    expect($expected)->toBe($actual);
});

it('renders a commonmark markdown file', function () {
    $markdown = file_get_contents(__DIR__ . '/../../Datasets/CommonMark.md');
    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();

    expect($expected)->toBe($actual);
});

it('renders a description list, via Description List Extension', function (string $markdown) {
    $this->stdConverter->getEnvironment()->addExtension(new DescriptionListExtension());
    $this->twigConverter->getEnvironment()->addExtension(new DescriptionListExtension());

    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();

    expect($expected)->toBe($actual);
})->with('description_list');

it('renders embedded content, via Embed Extension', function () {
    $adapter = $this->getMockBuilder(EmbedAdapterInterface::class)->getMock();
    /*$adapter->expects($this->once())
        ->method('getEmbedCode')
        ->willReturn('<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allowfullscreen></iframe>');
*/
    $env = new Environment(
        [
            'html_input' => 'escape',
            'templateRenderer' => [
                'engine' => 'twig',
                'templates_dirs' => [
                    $this->root->url(),
                ],
            ],
            'embed' => [
                'adapter' => $adapter,
                'allowed_domains' => ['youtube.com', 'twitter.com', 'github.com'],
                'fallback' => 'link',
            ],
        ],
    );
    $env->addExtension(new CommonMarkCoreExtension());
    $env->addExtension(new EmbedExtension());

    $twigConverter = new TemplateConverter($env);
    $stdConverter = new CommonMarkConverter([
        'html_input' => 'escape',
        'embed' => [
            'adapter' => $adapter,
            'allowed_domains' => ['youtube.com', 'twitter.com', 'github.com'],
            'fallback' => 'link',
            ]
        ]
    );
    $stdConverter->getEnvironment()->addExtension(new EmbedExtension());

    $markdown = "
Check out this video!

https://www.youtube.com/watch?v=dQw4w9WgXcQ  
";

    $expected = $stdConverter->convert($markdown)->getContent();
    $actual = $twigConverter->convert($markdown)->getContent();

    expect($expected)->toBe($actual);
});

it('renders a footnotes list, via Footnotes Extension', function (string $markdown) {
    $this->stdConverter->getEnvironment()->addExtension(new FootnoteExtension());
    $this->twigConverter->getEnvironment()->addExtension(new FootnoteExtension());

    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();

    expect($expected)->toBe($actual);
})->with('footnotes');

it('renders markdown with a front matter', function(string $markdown) {
    $this->stdConverter->getEnvironment()->addExtension(new FrontMatterExtension());
    $this->twigConverter->getEnvironment()->addExtension(new FrontMatterExtension());

    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();

    expect($expected)->toBe($actual);
})->with('front_matter');

it('renders markdown with heading permalinks', function(string $markdown) {
    $this->stdConverter->getEnvironment()->addExtension(new HeadingPermalinkExtension());
    $this->twigConverter->getEnvironment()->addExtension(new HeadingPermalinkExtension());

    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();

    expect($expected)->toBe($actual);
})->with(["# Heading 1\n\n## Heading 2\n\n### Heading 3\n"]);

it('renders markdown with Highlight Extension', function(string $markdown) {
    $this->stdConverter->getEnvironment()->addExtension(new HighlightExtension());
    $this->twigConverter->getEnvironment()->addExtension(new HighlightExtension());

    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();

    expect($expected)->toBe($actual);
})->with(["I need to highlight these ==very important words==."]);

it('renders markdown with Mention Extension', function(string $markdown) {
    $this->stdConverter->getEnvironment()->addExtension(new MentionExtension());
    $this->twigConverter->getEnvironment()->addExtension(new MentionExtension());

    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();

    expect($expected)->toBe($actual);
})->with(["Please, ask @cristianoc72 about that"]);

it('renders markdown with Strikethrough Extension', function(string $markdown) {
    $this->stdConverter->getEnvironment()->addExtension(new StrikethroughExtension());
    $this->twigConverter->getEnvironment()->addExtension(new StrikethroughExtension());

    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();

    expect($expected)->toBe($actual);
})->with(["This extension is ~~really good~~ great!"]);

it('renders markdown with Table of Contents Extension', function(string $markdown) {
    $this->twigConverter->getEnvironment()->addExtension(new TableOfContentsExtension());
    $this->twigConverter->getEnvironment()->addExtension(new HeadingPermalinkExtension());
    $this->stdConverter->getEnvironment()->addExtension(new TableOfContentsExtension());
    $this->stdConverter->getEnvironment()->addExtension(new HeadingPermalinkExtension());

    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();

    // don't care about newlines, as they can be different between the two renderers
    expect(str_replace("\n", "", $expected))->toBe(str_replace("\n", "", $actual));
})->with('toc');

it('renders markdown with tables, via Table Extension', function(string $markdown) {
    $this->twigConverter->getEnvironment()->addExtension(new TableExtension());
    $this->stdConverter->getEnvironment()->addExtension(new TableExtension());

    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();

    expect($expected)->toBe($actual);
})->with('tables');

it('renders markdown with task lists, via Task List Extension', function(string $markdown) {
    $this->twigConverter->getEnvironment()->addExtension(new TaskListExtension());
    $this->stdConverter->getEnvironment()->addExtension(new TaskListExtension());

    $expected = $this->stdConverter->convert($markdown)->getContent();
    $actual = $this->twigConverter->convert($markdown)->getContent();

    expect($expected)->toBe($actual);
})->with('tasks');

it('renders a simple markdown string by calling Twig converter (__invoke test)', function () {
    $markdown = "# Hello World\nThis is a **test** of the Twig converter.";
    $twigConverter = $this->twigConverter;

    $expected = $this->stdConverter->convert($markdown)->getContent();
    $rendered = $twigConverter($markdown)->getContent();

    expect($rendered)->toBe($expected);
});
