<?php


namespace AC\NormBundle\core\exceptions;


class UnsupportedColumnType extends AbstractNormException {
    public function __construct($tableName, $columnName, $type) {
        parent::__construct('UnsupportedColumnType table: ' . $tableName . '; column: ' . $columnName
            . '; type:' . $type);

    }
} 