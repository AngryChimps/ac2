<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class MemberCompanyRatingCollectionBase extends NormBaseCollection {
    public static $realm = 'riak';
    public static $tableName = 'member_company_rating';
    public static $singularClassName = '\NormTests\riak\MemberCompanyRating';
    public static $primaryKeyFieldNames = array('member_id', 'company_id');
    public static $primaryKeyPropertyNames = array('memberId', 'companyId');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_riak_ds';
}