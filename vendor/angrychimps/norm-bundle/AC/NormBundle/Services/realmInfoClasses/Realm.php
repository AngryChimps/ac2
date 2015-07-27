<?php


namespace AC\NormBundle\Services\realmInfoClasses;


class Realm {
    public $name;
    public $namespace;

    private $tables;

    /**
     * @param $tableName
     * @return Table
     */
    public function getTable($tableName) {
        return $this->tables[$tableName];
    }

    public function addTable(Table $table) {
        $this->tables[$table->name] = $table;
    }
}