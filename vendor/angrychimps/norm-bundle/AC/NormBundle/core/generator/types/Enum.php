<?php


namespace AC\NormBundle\core\generator\types;

use \AC\NormBundle\core\generator\types\Column;
use \AC\NormBundle\core\generator\types\ForeignKey;

class Enum {
    /** @var  string */
    public $name;

    /** @var  string[] */
    public $values;
}