<?php


namespace AC\NormBundle\core\exceptions;


class MethodNotImplemented extends AbstractNormException {
    public function __construct($methodName, $objectString) {
        parent::__construct('MethodNotImplemented methodName: ' . $methodName . ' objStr: ' . $objectString);
    }
} 