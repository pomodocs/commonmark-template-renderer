<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TemplateRenderer\Parts;

use League\CommonMark\Node\Inline\AbstractInline;
use League\CommonMark\Node\Node;
use League\Config\ConfigurationInterface;

/**
 * Trait that provides the block and inline separators configuration values.
 */
trait SeparatorPart
{
    abstract public function getConfiguration(): ConfigurationInterface;

    /**
     * Get the correct separator based on node type (block or inline).
     *
     * @param Node $node
     * @return string
     */
    public function getSeparator(Node $node): string
    {
        return $node instanceof AbstractInline ? $this->getInnerSeparator() : $this->getBlockSeparator();
    }

    /**
     * Get the block separator.
     *
     * @return string
     */
    public function getBlockSeparator(): string
    {
        return $this->getConfiguration()->get('renderer/block_separator');
    }

    /**
     * Get the inline separator.
     *
     * @return string
     */
    public function getInnerSeparator(): string
    {
        return $this->getConfiguration()->get('renderer/inner_separator');
    }
}
