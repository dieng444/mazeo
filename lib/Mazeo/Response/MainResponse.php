<?php

namespace Mazeo\Response;

/**
 * Class MainResponse
 * @package Mazeo\Response;
 * @author Macky Dieng
 */
abstract  class MainResponse implements ResponseInterface
{

    /**
     * @var mixed $response - the response to send
     */
    protected $response;

    /**
     * MainResponse constructor
     * @param mixed $response - the response to send
     */
    public function __construct($response)
    {
        $this->response = $response;
    }
    /**
     * Allows to send a current response
     * @return mixed
     */
    public function send()
    {
        echo $this->response;die;
    }
    /**
     * Set the returning content type
     * @param string $content - the new content to assign
     */
    public function setHeader($content)
    {
        return header($content);
    }
}
