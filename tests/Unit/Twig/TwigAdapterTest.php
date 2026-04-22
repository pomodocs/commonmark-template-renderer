<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Text;
use League\Config\ConfigurationInterface;
use PomoDocs\CommonMark\TemplateRenderer\Twig\TwigAdapter;

beforeEach(function () {
    $configMock = $this->getMockBuilder(ConfigurationInterface::class)->getMock();
    $configMock->method('get')->willReturnMap([
        ['templateRenderer/templates_dirs', [realpath(__DIR__ . '/../../../resources/templates/default/twig')]],
        ['renderer/block_separator', "\n"],
        ['renderer/inner_separator', ''],
    ]);
    $this->twig = new TwigAdapter($configMock);
});

it('should render a simple text node', function () {
    $node = new Text('Test node content');
    $rendered = $this->twig->renderNode($node);

    expect($rendered)->toBe('Test node content');
});

it('should render a link node', function () {
    $node = new Link('https://example.com');
    $rendered = $this->twig->renderNode($node);
    expect($rendered)->toBe('<a href="https://example.com"></a>');
});

it('should render a fenced code block', function () {
    $node = new FencedCode(3, '`', 0);
    $node->setLiteral("<?php declare(strict_types=1);\n");
    $node->setInfo('php');
    $rendered = $this->twig->renderNode($node);
    $expected = <<<HTML
<pre><code class="language-php">&lt;?php declare(strict_types=1);
</code></pre>
HTML;
    expect($rendered)->toBe($expected);
});

it('should render children nodes', function () {
    $node = new Paragraph();
    $strong = new Strong();
    $text = new Text('Test render children.');
    $strong->appendChild($text);
    $node->appendChild($strong);
    $rendered = $this->twig->renderChildren($node);
    expect($rendered)->toBe('<strong>Test render children.</strong>');
});

it('should render a document node', function () {
    $node = new Document();
    $rendered = $this->twig->renderNode($node);
    expect($rendered)->toBe('');
});

it('should render a link node with target="_blank" and add rel="noopener noreferrer"', function () {
    $node = new Link('https://example.com');
    $node->data->set('attributes/target', '_blank');
    $rendered = $this->twig->renderNode($node);
    expect($rendered)->toBe('<a href="https://example.com" target="_blank" rel="noopener noreferrer"></a>');
});

it('should return the separator for inline nodes', function () {
    $node = new Text('Test inline separator');
    $separator = $this->twig->getSeparator($node);
    expect($separator)->toBe('');
});
