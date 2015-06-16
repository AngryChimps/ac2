<?php


namespace AC\NormBundle\core\generator\types;

class Schema {
    /** @var string */
    public $namespace;

    /** @var string */
    public $dbname;

    /** @var Datastore[] */
    public $datastores = [];

    /** @var Table[]  */
    public $tables = array();
}