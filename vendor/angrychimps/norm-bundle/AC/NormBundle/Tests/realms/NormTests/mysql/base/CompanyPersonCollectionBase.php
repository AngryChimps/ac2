<?php

namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseCollection;

class CompanyPersonCollectionBase extends NormBaseCollection {
    protected static $realm = 'mysql';
    protected static $tableName = 'company_person';
    protected static $singularClassName = 'CompanyPerson';
    protected static $primaryKeyFieldNames = array('company', 'person');
    protected static $primaryKeyPropertyNames = array('company', 'person');
    protected static $autoIncrementFieldName = '';
}