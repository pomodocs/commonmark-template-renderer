<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TemplateRenderer;

use League\CommonMark\ConverterInterface;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Parser\MarkdownParserInterface;

final class TemplateConverter implements ConverterInterface
{
    private MarkdownParserInterface $markdownParser;
    private TemplateRenderer $templateRenderer;

    public function __construct(Environment $environment)
    {
        $environment->addExtension(new TemplateRendererExtension());
        $this->markdownParser = new MarkdownParser($environment);
        $this->templateRenderer   = new TemplateRenderer($environment);
    }

    /**
     * Converts Markdown to HTML.
     *
     * @param string $input The Markdown to convert
     *
     * @return RenderedContentInterface Rendered HTML
     *
     * @throws CommonMarkException
     */
    public function convert(string $input): RenderedContentInterface
    {
        $documentAST = $this->markdownParser->parse($input);

        return $this->templateRenderer->renderDocument($documentAST);
    }

    /**
     * Converts CommonMark to HTML.
     *
     * @see MarkdownConverter::convert()
     *
     * @throws CommonMarkException
     */
    public function __invoke(string $markdown): RenderedContentInterface
    {
        return $this->convert($markdown);
    }
}
