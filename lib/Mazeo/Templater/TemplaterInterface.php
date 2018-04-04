<?php

namespace Mazeo\Templater;

/**
 * Interface TemplaterInterface
 * @package Mazeo\Templater
 * @author Macky Dieng
 */
interface TemplaterInterface
{
    /**
     * Display a given template
     * @param $view - the html view to display
     * @param array $data - data to send with the view
     */
    public static function render($view, array $data);
}
