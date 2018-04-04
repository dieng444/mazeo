<?php

namespace Mazeo\Response;

/**
 * Class JsonResponse
 * @package Mazeo\Response;
 * @author Macky Dieng
 */
class JsonResponse extends MainResponse {

    /**
     * JsonResponse constructor
     * @param mixed $response - the response to send
     */
    public function __construct($response)
    {
        parent::__construct(json_encode($response));
    }
    /**
     * Send a json response
     */
    public function send()
    {
        $this->setHeader('Content-Type: application/json');
        parent::send();
    }
}
