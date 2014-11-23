<?php


namespace AngryChimps\ApiBundle\Exceptions;


use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class InvalidSessionException extends \RuntimeException implements HttpExceptionInterface {
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