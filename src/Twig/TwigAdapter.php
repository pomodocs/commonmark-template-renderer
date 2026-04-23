<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TemplateRenderer\Twig;

use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\DescriptionList\Node\Description;
use League\CommonMark\Extension\Footnote\Node\Footnote;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use League\Config\ConfigurationInterface;
use PomoDocs\CommonMark\TemplateRenderer\AdapterInterface;
use PomoDocs\CommonMark\TemplateRenderer\NodeNormalizer;
use PomoDocs\CommonMark\TemplateRenderer\Parts\SeparatorPart;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

/**
 * Twig template engine wrapper.
 *
 * This class instantiates a Twig Environment instance, configures it and adds some useful filters.
 * It also contains some normalize methods.
 */
final class TwigAdapter implements AdapterInterface
{
    use SeparatorPart;

    /**
     * @var Environment The Twig instance.
     */
    private Environment $twig;

    /**
     * @var NodeNormalizer The node normalizer instance.
     */
    private NodeNormalizer $nodeNormalizer;

    /**
     * @todo before release, change twig configuration.
     */
    public function __construct(private ConfigurationInterface $configuration)
    {
        // Initialize Twig
        /** @var array<string> $templatesDirs */
        $templatesDirs = $this->configuration->get('templateRenderer/templates_dirs');
        $loader = new FilesystemLoader($templatesDirs);
        $twig = new Environment($loader, [
            'autoescape' => false,
            //'cache' => sys_get_temp_dir() . '/commonmark_twig_renderer_cache',
            'debug' => true,
        ]);
        $twig->addExtension(new DebugExtension());
        $this->setEngine($twig);
        $this->nodeNormalizer = new NodeNormalizer($this->configuration);
    }

    /**
     * Set the Twig instance to be used by the renderer.
     * This method is used to set a custom, already configured Twig instance to be used by the renderer.
     * It's useful when we need a custom Twig configuration or an instance taken from a di container.
     * It adds the necessary filters to the Twig instance.
     *
     * @param Environment $engine The Twig instance.
     * @return void
     */
    public function setEngine(Environment $engine): void
    {
        $this->twig = $engine;

        $this->twig->addFilter(new TwigFilter('render_children', [$this, 'renderChildren']));
        $this->twig->addFilter(new TwigFilter('render_attributes', [$this, 'renderAttributes']));
        $this->twig->addFilter(new TwigFilter('unescape_single_quotes', [$this, 'unescapeSingleQuotes']));
    }

    /**
     * Twig filter method.
     * Render the children of a node.
     *
     * @param Node $node The node to render the children of.
     * @return string
     */
    public function renderChildren(Node $node): string
    {
        if (!$node->hasChildren()) {
            return '';
        }

        $output = '';

        foreach ($node->children() as $child) {
            // Force tight lists.
            if (($node instanceof ListItem || $node instanceof Description) && $child instanceof Paragraph) {
                $output .= $this->renderChildren($child);
            } else {
                $output .= $this->renderNode($child);
            }

            if ($child instanceof ListItem || $child instanceof Description) {
                $output .= $this->getSeparator($child);
            }

            if ($node instanceof Footnote && $child instanceof Paragraph) {
                $output .= $this->getSeparator($child);
            }
        }

        return rtrim($output);
    }

    /**
     * Twig filter method.
     * Render the attributes of a node.
     *
     * @return string
     */
    public function renderAttributes(Node $node): string
    {
        $output = '';

        /** @var array<string, string|array<string>> $attributes */
        $attributes = $node->data['attributes'];
        foreach ($attributes as $key => $value) {
            $output .= ' ' . $key . '="' . (is_array($value) ? (string) implode(' ', $value) : $value) . '"';
        }

        return $output;
    }

    /**
     * Twig filter method.
     * Unescape single quotes in a string.
     * This method is useful to unescape single quotes since Twig escapes them by default and
     * CommonMark expects them to be unescaped.
     *
     * @return string
     */
    public function unescapeSingleQuotes(string $value): string
    {
        return str_replace('&#039;', "'", $value);
    }

    /**
     * Render a node via Twig template engine.
     *
     * @return string The resulted html.
     * @throws LoaderError It the template does not exist.
     */
    public function renderNode(Node $node): string
    {
        // Document node is not rendered.
        if ($node instanceof Document) {
            return '';
        }

        $node = $this->nodeNormalizer->normalize($node);

        return $this->twig->render(
            $this->getTemplateName($node),
            ['node' => $node, 'configuration' => $this->configuration]
        );
    }

    public function getConfiguration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    private function getTemplateName(Node $node): string
    {
        $array = explode('\\', get_class($node));
        $nodeClass = trim(array_pop($array), '-_' );
        $nodeClass = (string) preg_replace('/\s+/', ' ', $nodeClass);
		$nodeClass = str_replace([' ', '-'], '_', $nodeClass);
        $templateName = mb_strtolower((string) preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $nodeClass));

        return "$templateName.html.twig";
    }
}
