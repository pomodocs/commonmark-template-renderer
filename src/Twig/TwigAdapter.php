<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TemplateRenderer\Twig;

use Attribute;
use League\CommonMark\Extension\Attributes\Util\AttributesHelper;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\DescriptionList\Node\Description;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use League\Config\ConfigurationInterface;
use phootwork\lang\Text;
use PomoDocs\CommonMark\Alert\Node\Block\Alert;
use PomoDocs\CommonMark\TemplateRenderer\AdapterInterface;
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
     * @todo before release, change twig configuration.
     */
    public function __construct(private ConfigurationInterface $configuration)
    {
        // Initialize Twig
        $templatesDirs = $this->configuration->get('templateRenderer/templates_dirs');
        $loader = new FilesystemLoader($templatesDirs);
        $twig = new Environment($loader, [
            'autoescape' => false,
            //'cache' => sys_get_temp_dir() . '/commonmark_twig_renderer_cache',
            'debug' => true,
        ]);
        $twig->addExtension(new DebugExtension());
        $this->setEngine($twig);
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

        foreach ($node->data['attributes'] as $key => $value) {
            $output .= ' ' . $key . '="' . (is_array($value) ? implode(' ', $value) : $value) . '"';
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

        $node = $this->normalizeNode($node);

        $nodeClass = Text::create(get_class($node))->split('\\')->pop();
        $templateName = Text::create($nodeClass)->toSnakeCase()->append('.html.twig')->toString();

        return $this->twig->render($templateName, ['node' => $node, 'configuration' => $this->configuration]);
    }

    public function getConfiguration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    /**
     * Normalize node.
     *
     * @param Node $node
     * @return Node
     */
    private function normalizeNode(Node $node): Node
    {
        return match (true) {
            $node instanceof Link => $this->normalizeLink($node),
            $node instanceof FencedCode => $this->normalizeFencedCode($node),
            $node instanceof TableCell => $this->normalizeTableCell($node),
            $node instanceof Alert => $this->normalizeAlert($node),
            default => $node,
        };
    }

    /**
     * Normalize Link node.
     *
     * @param Link $node
     * @return Link
     */
    private function normalizeLink(Link $node): Link
    {
        if ($node->data->has('attributes/target') && $node->data->get('attributes/target') === '_blank' && !$node->data->has('attributes/rel')) {
            $node->data->set('attributes/rel', 'noopener noreferrer');
        }

        return $node;
    }

    /**
     * Normalize FencedCode node.
     *
     * @param FencedCode $node
     * @return FencedCode
     */
    private function normalizeFencedCode(FencedCode $node): FencedCode
    {
        if ($node->getInfo() !== "") {
            $node->data->append('attributes/class', "language-{$node->getInfo()}");
        }

        return $node;
    }

    /**
     * Normalize TableCell node.
     *
     * @param TableCell $node
     * @return TableCell
     */
    private function normalizeTableCell(TableCell $node): TableCell
    {
        if ($node->getAlign() !== null) {
            $node->data->append('attributes/align', $node->getAlign());
        }

        return $node;
    }

    /**
     * Normalize Alert node.
     *
     * @param Alert $node
     * @return Alert
     */
    private function normalizeAlert(Alert $node): Alert
    {
        $type = $node->getType();

        $class = $this->configuration->get('alert/class_name');
        $colorClass = $this->configuration->get("alert/colors/$type");

        $node->data->append('attributes/class', "$class");
        $node->data->append('attributes/class', "$class-$colorClass");

        return $node;
    }
}
