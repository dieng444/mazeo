<?php

namespace Mazeo\Response;

/**
 * Interface ResponseInterface
 * @package Mazeo\Templater
 * @author Macky Dieng
 */
interface ResponseInterface {

    /**
     * Send new response response
     * @param mixed response to send
     * @return mixed
     */
    public function send();
    /**
     * Allows to set the returning content type
     * @param String $content
     */
    public function setHeader($content);
}
