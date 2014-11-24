<?php


namespace AC\NormBundle\core\datastore;


abstract class AbstractRedisDatastore extends AbstractDatastore {
    /** @var \Redis  */
    public $connection;


} 