<?php


namespace AC\NormBundle\core\exceptions;


class CannotChangePrimaryKeyException extends AbstractNormException {
    /**
     * @param string $db
     * @param string $table
     */
    public function __construct($db, $table) {
        parent::__construct('Cannot Change Primary Key Value; $db=' . $db
            . ' table=' . $table);
    }

} 