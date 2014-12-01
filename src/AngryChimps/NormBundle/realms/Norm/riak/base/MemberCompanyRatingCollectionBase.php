<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class MemberCompanyRatingCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'member_company_rating';
    protected static $singularClassName = 'MemberCompanyRating';
    protected static $primaryKeyFieldNames = array('member_id', 'company_id');
    protected static $primaryKeyPropertyNames = array('memberId', 'companyId');
    protected static $autoIncrementFieldName = '';
}