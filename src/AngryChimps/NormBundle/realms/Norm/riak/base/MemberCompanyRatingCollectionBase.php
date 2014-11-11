<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class MemberCompanyRatingCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'member_company_rating';
    protected static $singularClassName = 'MemberCompanyRating';
    protected static $primaryKeyFieldNames = array();
    protected static $primaryKeyPropertyNames = array();
    protected static $autoIncrementFieldName = '';
}