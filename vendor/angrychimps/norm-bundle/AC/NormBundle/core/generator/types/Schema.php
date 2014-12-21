<?php


namespace AC\NormBundle\core\generator\types;

use \AC\NormBundle\core\generator\types\Table;
use \AC\NormBundle\core\generator\types\Column;

class Schema {
    /** @var string */
    public $realm;
    public $namespace;

    /** @var  string */
    public $dbname;

    /** @var Table[]  */
    public $tables = array();

    /** @var ForeignKey[] */
    public $foreignKeys = array();
} 