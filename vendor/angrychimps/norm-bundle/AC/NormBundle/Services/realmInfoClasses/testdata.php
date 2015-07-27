<?php

$realms['mysql']['member']['objectClass'] = "\\Norm\\mysql\\Member";
$realms['mysql']['member']['collectionClass'] = "\\Norm\\mysql\\MemberCollection";
$realms['mysql']['member']['primaryDatastore'] = "mysql_ds";
$realms['mysql']['member']['primaryKeyFieldNames'] = ['id'];
$realms['mysql']['member']['primaryKeyPropertyNames'] = ['Id'];
$realms['mysql']['member']['autoIncrementFieldNames'] = null;
$realms['mysql']['member']['autoIncrementPropertyNames'] = null;
$realms['mysql']['member']['autoGenerateFieldNames'] = ['id'];
$realms['mysql']['member']['autoGeneratePropertyNames'] = ['Id'];
$realms['mysql']['member']['fields']['id']['propertyName'] = 'Id';
$realms['mysql']['member']['fields']['id']['type'] = 'string';
$realms['mysql']['member']['fields']['id']['default'] = 'null';
