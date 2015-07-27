<?php


namespace AC\NormBundle\core\exceptions;


class ObjectAlreadyDeletedException extends AbstractNormException {
    /**
     * @param string $tableName
     * @param string $primaryKeyData
     */
    public function __construct($tableName, $primaryKeyData) {
        parent::__construct('Cannot modify an already deleted item; table: ' . $tableName . '; '
            . 'primaryKeyData: ' . json_encode($primaryKeyData));
    }

} 