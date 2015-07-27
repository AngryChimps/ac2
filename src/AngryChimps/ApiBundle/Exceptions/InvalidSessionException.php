<?php


namespace AngryChimps\ApiBundle\Exceptions;


use Exception;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class InvalidSessionException extends \RuntimeException implements HttpExceptionInterface {
    protected $debug;

    public function __construct($debug)
    {
        $this->debug = $debug;

        parent::__construct("Invalid Session", 0, null);
    }

    public function getDebugMessage() {
        return $this->debug;
    }


    public function getStatusCode() {
        return 403;
    }

    /**
     * Returns response headers.
     *
     * @return array Response headers
     */
    public function getHeaders()
    {
        return array();
    }
}