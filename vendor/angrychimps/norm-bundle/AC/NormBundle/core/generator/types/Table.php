<?php


namespace AC\NormBundle\core\generator\types;

use \AC\NormBundle\core\generator\types\Column;
use \AC\NormBundle\core\generator\types\ForeignKey;
use \AC\NormBundle\core\generator\types\Enum;
use AC\NormBundle\core\Utils;

class Table {
    /** @var  string */
    public $name;

    /** @var  string */
    public $comment;

    /** @var  string[] */
    public $primaryKeyNames = array();

    /** @var  string[] */
    public $primaryKeyPropertyNames = array();

    /** @var  string */
    public $autoIncrementName;

    /** @var  string */
    public $autoGenerateName;

    /** @var Column[] */
    public $columns = array();

    /** @var ForeignKey[] */
    public $foreignKeys = array();

    /** @var ForeignKey[] */
    public $reverseForeignKeys = array();

    /** @var Enum[] */
    public $enums = array();

    const StatusEnumActive = 1;
}