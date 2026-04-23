<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TemplateRenderer;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\Footnote\Node\Footnote;
use League\CommonMark\Extension\Footnote\Node\FootnoteBackref;
use League\CommonMark\Extension\Footnote\Node\FootnoteContainer;
use League\CommonMark\Extension\Footnote\Node\FootnoteRef;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalink;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Node\Node;
use League\Config\ConfigurationInterface;
use PomoDocs\CommonMark\Alert\Node\Block\Alert;

/**
 * Normalizes nodes for template rendering.
 */
final class NodeNormalizer
{
    public function __construct(private ConfigurationInterface $configuration)
    {
    }

    /**
     * Normalizes a node for template rendering.
     *
     * This method checks the type of the node and applies specific normalization logic based on its type.
     * If the node is of a recognized type, it will be transformed accordingly; otherwise, it will be returned as is.
     *
     * @param Node $node The node to normalize.
     * @return Node The normalized node, ready for Twig rendering.
     */
    public function normalize(Node $node): Node
    {
        return match (get_class($node)) {
            Link::class => $this->normalizeLink($node),
            FencedCode::class => $this->normalizeFencedCode($node),
            TableCell::class => $this->normalizeTableCell($node),
            Alert::class => $this->normalizeAlert($node),
            FootnoteContainer::class => $this->normalizeFootnoteContainer($node),
            FootnoteRef::class => $this->normalizeFootnoteRef($node),
            Footnote::class => $this->normalizeFootnote($node),
            FootnoteBackref::class => $this->normalizeFootnoteBackref($node),
            HeadingPermalink::class => $this->normalizeHeadingPermalink($node),
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

        /** @var string $class */
        $class = $this->configuration->get('alert/class_name');

        /** @var string $colorClass */
        $colorClass = $this->configuration->get("alert/colors/$type");

        $node->data->append('attributes/class', "$class");
        $node->data->append('attributes/class', "$class-$colorClass");

        return $node;
    }

    /**
     * Normalize FootnoteContainer node.
     *
     * @param FootnoteContainer $node
     * @return FootnoteContainer
     */
    private function normalizeFootnoteContainer(FootnoteContainer $node): FootnoteContainer
    {
        $node->data->append('attributes/class', $this->configuration->get('footnote/container_class'));
        $node->data->set('attributes/role', 'doc-endnotes');

        return $node;
    }

    /**
     * Normalize FootnoteRef node.
     *
     * @param FootnoteRef $node
     * @return FootnoteRef
     */
    private function normalizeFootnoteRef(FootnoteRef $node): FootnoteRef
    {
        $node->data->append('attributes/class', $this->configuration->get('footnote/ref_class'));
        $node->data->set('attributes/href', \mb_strtolower($node->getReference()->getDestination(), 'UTF-8'));
        $node->data->set('attributes/role', 'doc-noteref');

        return $node;
    }

    /**
     * Normalize Footnote node.
     *
     * @param Footnote $node
     * @return Footnote
     */
    private function normalizeFootnote(Footnote $node): Footnote
    {
        /** @var string $class */
        $class = $this->configuration->get('footnote/footnote_class');
        /** @var string $idPrefix */
        $idPrefix = $this->configuration->get('footnote/footnote_id_prefix');
        
        $node->data->append('attributes/class', $class);
        $node->data->set('attributes/id', $idPrefix . \mb_strtolower($node->getReference()->getLabel(), 'UTF-8'));
        $node->data->set('attributes/role', 'doc-endnote');
        
        return $node;
    }

    /**
     * Normalize FootnoteBackref node.
     *
     * @param FootnoteBackref $node
     * @return FootnoteBackref
     */
    private function normalizeFootnoteBackref(FootnoteBackref $node): FootnoteBackref
    {
        $node->data->append('attributes/class', $this->configuration->get('footnote/backref_class'));
        $node->data->set('attributes/rev', 'footnote');
        $node->data->set('attributes/href', \mb_strtolower($node->getReference()->getDestination(), 'UTF-8'));
        $node->data->set('attributes/role', 'doc-backlink');

        return $node;
    }

    /**
     * Normalize HeadingPermalink node.
     *
     * @param HeadingPermalink $node
     * @return HeadingPermalink
     */
    private function normalizeHeadingPermalink(HeadingPermalink $node): HeadingPermalink
    {
        $slug = $node->getSlug();

        /** @var string $fragmentPrefix */
        $fragmentPrefix = $this->configuration->get('heading_permalink/fragment_prefix');
        $fragmentPrefix = $fragmentPrefix !== '' ? $fragmentPrefix . '-' : '';
        
        if (! $this->configuration->get('heading_permalink/apply_id_to_heading')) {
            /** @var string $idPrefix */
            $idPrefix = $this->configuration->get('heading_permalink/id_prefix');
            $idPrefix = $idPrefix !== '' ? $idPrefix . '-' : '';
            
            $node->data->set('attributes/id', $idPrefix . $slug);
        }

        $node->data->set('attributes/href', '#' . $fragmentPrefix . $slug);
        $node->data->append('attributes/class', $this->configuration->get('heading_permalink/html_class'));

        if ($this->configuration->get('heading_permalink/aria_hidden')) {
            $node->data->set('attributes/aria-hidden', 'true');
        }

        $node->data->set('attributes/title', $this->configuration->get('heading_permalink/title'));

        return $node;
    }
}
