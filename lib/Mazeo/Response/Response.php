<?php

namespace Mazeo\Response;

/**
 * Class Response
 * @package Mazeo\Response;
 * @author Macky Dieng
 */
class Response extends MainResponse {

    /**
     * Response constructor.
     * @param mixed $response - the response to send
     */
    public function __construct($response)
    {
        parent::__construct($response);
    }
}
