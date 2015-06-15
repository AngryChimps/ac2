<?php


namespace AC\NormBundle\core\generator\types;


use AC\NormBundle\core\Utils;

class Column {
    public $name;
    public $position;
    public $default;
    public $isNullable;
    public $type;
    public $typeWithLength;
    public $length;
    public $comments;
    public $validations;
    public $includeInAll;
    public $indexName;

    public function getPropertyName() {
        return Utils::field2property($this->name);
    }
}