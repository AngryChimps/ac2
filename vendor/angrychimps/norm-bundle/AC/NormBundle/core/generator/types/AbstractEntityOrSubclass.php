<?php


namespace AC\NormBundle\core\generator\types;


class AbstractEntityOrSubclass
{
    public $name;

    /** @var  Field[] */
    public $fields;

    /** @var Enum[] */
    public $enums = array();

}