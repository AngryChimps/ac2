<?php


namespace AC\NormBundle\core\generator\types;


class Entity extends AbstractEntityOrSubclass
{
    /** @var  string[] */
    public $primaryKeyNames = array();

    /** @var  string */
    public $autoIncrementName;

    /** @var  string */
    public $autoGenerateName;

    /** @var EntityDatastore[] */
    public $datastores;

    /** @var Datastore */
    public $primaryDatastore;

    /** @var string[] */
    public $apiPublicFields = [];

    /** @var string[] */
    public $apiPrivateFields = [];

    /** @var string[] */
    public $apiHiddenButSettableFields = [];
}