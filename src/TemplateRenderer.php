<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TemplateRenderer;

use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Event\DocumentPreRenderEvent;
use League\CommonMark\Event\DocumentRenderedEvent;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Output\RenderedContent;
use League\CommonMark\Output\RenderedContentInterface;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\DocumentRendererInterface;
use League\Config\ConfigurationInterface;
use PomoDocs\CommonMark\TemplateRenderer\AdapterInterface;
use PomoDocs\CommonMark\TemplateRenderer\Parts\SeparatorPart;
use PomoDocs\CommonMark\TemplateRenderer\Twig\TwigAdapter;

final class TemplateRenderer implements DocumentRendererInterface, ChildNodeRendererInterface
{
    use SeparatorPart;

    private AdapterInterface $engine;

    public function __construct(private EnvironmentInterface $environment)
    {
        /** @var string $engine */
        $engine = $this->getConfiguration()->get('templateRenderer/engine');
        $this->engine = match ($engine) {
            'twig' => new TwigAdapter($this->getConfiguration()),
            /*  'latte' => new LatteAdapter($this->getConfiguration()),
                'plates' => new PlatesAdapter($this->getConfiguration()),
                'blade' => new BladeAdapter($this->getConfiguration()),
            */
            default => throw new \InvalidArgumentException("Unsupported template engine: $engine"),
        };
    }

    public function renderDocument(Document $document): RenderedContentInterface
    {
        $this->environment->dispatch(new DocumentPreRenderEvent($document, 'html'));

        $event = new DocumentRenderedEvent(
            new RenderedContent(
                $document,
                $this->renderNodes($document->iterator()),
            ),
        );

        $this->environment->dispatch($event);

        return $event->getOutput();
    }

    /**
     * {@inheritDoc}
     */
    public function renderNodes(iterable $nodes): string
    {
        $output = '';

        foreach ($nodes as $node) {
            //Children nodes are rendered via `render_children` filter.
            if ($node->parent() instanceof Document || $node->parent() === null) {
                $output .= $this->getSeparator($node) . $this->engine->renderNode($node);
            }
        }

        return ltrim($output) . "\n";
    }

    public function getConfiguration(): ConfigurationInterface
    {
        return $this->environment->getConfiguration();
    }
}
