<?php


namespace AC\NormBundle\Services\realmInfoClasses;


class Table {
    public $name;

    private $columns;

    public function getColumn($columnName) {

    }

    public function addColumn(Column $column) {
        $this->columns[$column->name] = $column;
    }
} 