<?php


namespace AC\NormBundle\core\generator\types;


class Field
{
    public $name;

    public $default;

    public $type;

    public $validations;


    public $includeInAll;
    public $indexName;


    //For riak
    public $riakIndexed;

    //For elasticsearch
    public $elasticsearchIndexed;

}