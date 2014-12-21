<?php


namespace AC\NormBundle\core\exceptions;


class UnsupportedObjectTypeException extends AbstractNormException {
    public function __construct($obj) {
        parent::__construct('UnsupportedObjectType: ' . get_class($obj));

    }
} 