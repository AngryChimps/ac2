<?php


namespace AC\NormBundle\services\traits;

use AC\NormBundle\core\datastore\DatastoreManager;
use AC\NormBundle\core\exceptions\UnsupportedObjectTypeException;
use AC\NormBundle\core\datastore\RiakBlobDatastore;
use Riak\Client\Command\Search\Search;

trait Riak2BlobTrait {
    function populateCollectionByQuery(\ArrayObject $coll, $query, $limit = null, $offset = 0) {
        $rowsPerPage = 2;
        $page        = 2;
        $start       = $rowsPerPage * ($page - 1);

//        $search = Search::builder()
//            ->withNumRows($rowsPerPage)
//            ->withIndex("famous")
//            ->withStart($start)
//            ->withQuery('*:*')
//            ->build();
//
        $builder = Search::builder();
        $builder->withIndex('__norm_classmaps_' . $this->realm);
        if($limit !== null) {
           $builder->withNumRows($limit);
        }
        if($offset !== 0) {
            $builder->withStart($offset);
        }
        $builder->withQuery($query);

        $search = $builder->build();

        $searchResult = $this->client->execute($search);


    }
}