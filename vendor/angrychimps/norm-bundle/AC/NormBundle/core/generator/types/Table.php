<?php


namespace AC\NormBundle\core\generator\types;

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

    /** @var Datastore[] */
    public $datastores;

    /** @var Datastore */
    public $primaryDatastore;

    const StatusEnumActive = 1;
}