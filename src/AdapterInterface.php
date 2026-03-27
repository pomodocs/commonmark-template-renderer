<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace PomoDocs\CommonMark\TemplateRenderer;

use League\CommonMark\Node\Node;
use Twig\Environment;

interface AdapterInterface
{
    /**
     * Render the children of a node.
     * 
     * This method is used as a template engine filter, so it can be used in the templates 
     * to render the children of a node.
     * 
     * @param Node $node The node to render the children of.
     * @return string
     */
    public function renderChildren(Node $node): string;
    
    /**
     * Render the attributes of a node.
     * This method is used as a template engine filter, so it can be used in the templates.
     * 
     * @param Node $node The node to render the attributes of.
     * @return string
     */
    public function renderAttributes(Node $node): string;
    
    /**
     * Render a node via the template engine.
     * 
     * @return string The resulted html.
     * @throws \Exception If the template does not exist. The exception class could vary depending on the template engine used.
     */
    public function renderNode(Node $node): string;

    /**
     * Set the template engine.
     * This method is used to set a custom, already configured template engine to be used by the renderer.
     * It's useful when the user wants to use a custom template engine configuration or an instance taken from a di container.
     * 
     * @param Environment $engine The template engine.
     * @return void
     */
    public function setEngine(Environment $engine): void;
}
