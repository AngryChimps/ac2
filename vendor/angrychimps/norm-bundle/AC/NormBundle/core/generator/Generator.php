<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/25/14
 * Time: 3:58 PM
 */

namespace AC\NormBundle\core\generator;

use AC\NormBundle\core\datastore\DatastoreManager;
use AC\NormBundle\core\exceptions\InvalidForeignKeyMultipleColumnsException;
use AC\NormBundle\core\generator\generators\MysqlGenerator;
use AC\NormBundle\core\generator\generators\YamlGenerator;
use AC\NormBundle\core\Utils;
use AC\NormBundle\core\generator\types\Schema;
use AC\NormBundle\core\generator\types\Table;
use AC\NormBundle\core\generator\types\Column;
use AC\NormBundle\core\generator\types\ForeignKey;
use Handlebars\Handlebars;
use Symfony\Component\Yaml\Dumper;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;

class Generator {
    protected $_realm;
    protected $_namespace;
    protected $_isTest;
    protected $_schemaManager;
    protected $_environment;
    protected $_primaryDatastore;
    protected $_tableNames = [];

    /**
     * @var ForeignKeyConstraint[]
     */
    protected $_fks = array();

    /**
     * @var Table[]
     */
    protected $_tables = array();

    protected $_reverseForeignKeysByTable = array();

    protected $_classConfigs;

    protected static $_realms = array();
    protected static $_datastores;

    public function generate($realm, $environment, $isTest = false) {
        $this->populateRealms();

        $this->_realm = $realm;
        $this->_namespace = self::$_realms[$realm]['namespace'];
        $this->_isTest = $isTest;
        $this->_environment = $environment;


        $gen = new YamlGenerator($realm, $isTest);
        $schema = $gen->getSchema();
        $this->_primaryDatastore = $schema->

        $this->createRealmFolders();

        $this->createValidations($schema);

        foreach($schema->tables as $table) {
            $this->processTable($table, $isTest);
        }

        $this->renderRealmProperties();
    }

    public function generateAll($environment) {
        $this->populateRealms();

        foreach(self::$_realms as $realmName) {
            $this->generate($realmName, $environment);
        }
    }

//    public function renderDatastoreProperties() {
//        $dsInfo = self::$_realms[$this->_realm]['dsInfo'];
//
//        $data = array();
//        $data['dsName'] = self::$_realms[$this->_realm]['primary_datastore'];
//        $data['driver'] = $dsInfo['driver'];
//        $data['connetionInfo'] = $dsInfo;
//    }

    protected function populateRealms() {
        $contents = file_get_contents(__DIR__ . "/../../../../../../app/config/ac_norm.yml");
        $ac_norm = yaml_parse($contents);

        $contents = file_get_contents(__DIR__ . "/../../../../../../app/config/ac_norm_"
            . $this->_environment . ".yml");
        $ac_norm_env = yaml_parse($contents);

        foreach($ac_norm['realms'] as $realmName => $realmInfo) {
            self::$_realms[$realmName] = $realmInfo;
            self::$_realms[$realmName]['dsInfo']
                = $ac_norm_env['datastores'][self::$_realms[$realmName]['primary_datastore']];
        }
    }

    public function createValidations(Schema $schema) {
        $data = array();

        foreach($schema->tables as $table) {
            $tableStarted = false;
            foreach($table->columns as $column) {
                if(isset($column->validations)) {
                    if(!$tableStarted) {
                        $data[$table->getFullClassName($this->_realm)] = array();
                        $data[$table->getFullClassName($this->_realm)]['properties'] = array();
                        $tableStarted = true;
                    }
                    $data[$table->getFullClassName($this->_realm)]['properties'][$column->getPropertyName()] = $column->validations;
                }
            }
        }

        $dumper = new Dumper();

        $yaml = $dumper->dump($data, 5);

        file_put_contents(__DIR__ . "/../../../../../../../src/AngryChimps/NormBundle/realms/Norm/"
            . $this->_realm . '/validations/validations.yml', $yaml);
    }


    public static function setRealms($realms) {
        self::$_realms = $realms;
    }

    public static function setDatastores($datastores) {
        self::$_datastores = $datastores;
    }
    protected function createRealmFolders() {
        if($this->_isTest) {
            if (!file_exists(__DIR__ . '/../../Tests/realms/NormTests/' . $this->_realm)) {
                mkdir(__DIR__ . '/../../Tests/realms/NormTests/' . $this->_realm);
                mkdir(__DIR__ . '/../../Tests/realms/NormTests/' . $this->_realm . '/validations');
                mkdir(__DIR__ . '/../../Tests/realms/NormTests/' . $this->_realm . '/services');
                mkdir(__DIR__ . '/../../Tests/realms/NormTests/' . $this->_realm . '/services/base');
                mkdir(__DIR__ . '/../../Tests/realms/NormTests/' . $this->_realm . '/yaml');
                mkdir(__DIR__ . '/../../Tests/realms/NormTests/' . $this->_realm . '/yaml/classes');
            }
        }
        else {
            if (!file_exists(__DIR__ . "/../../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $this->_realm)) {
                mkdir(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $this->_realm);
                mkdir(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $this->_realm . '/validations');
                mkdir(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $this->_realm . '/services');
                mkdir(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $this->_realm . '/services/base');
                mkdir(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $this->_realm . '/yaml');
                mkdir(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $this->_realm . '/yaml/classes');
            }
        }
    }

    protected function populateClassConfigs() {
        if($this->_classConfigs === null) {
            $ds = DatastoreManager::getReferenceDb($this->_realm);
            $this->_classConfigs = $this->getClassConfigs($this->_realm);
        }
    }

//    protected function getClassConfigs() {
//        $configs = array();
//
//        $driver = DatastoreManager::getReferenceDbType($this->_realm);
//        switch($driver) {
//            case 'pdo_mysql':
//                $referenceDatastore = self::$_realms[$this->_realm]['referenceDatastore'];
//                $schemaName = $referenceDatastore['dbname'];
//
//                $ds = DatastoreManager::getReferenceDb($this->_realm);
//                $sql = 'SELECT TABLE_NAME, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?';
//                $params = array($schemaName);
//
//                $stmt = $ds->executeQuery($sql, $params);
//
//                while($row = $stmt->fetch()) {
//                    $commentArray = array();
//                    $tableName = $row['TABLE_NAME'];
//                    $comments = isset($$row['TABLE_COMMENT']) ? $row['TABLE_COMMENT'] : null;
//
//                    if($comments !== null) {
//                        $lines = explode("\n", $comments);
//                        foreach($lines as $line) {
//                            $parts = explode('=', $line, 1);
//                            $lpart = trim($parts[0]);
//                            $rpart = trim($parts[1]);
//                            $commentArray[] = array($lpart => $rpart);
//                        }
//                        $configs[$tableName] = $commentArray;
//                    }
//                }
//                break;
//        }
//
//        return $configs;
//    }

    protected function processTable(Table $table, $isTest) {
        $data = $this->getHandlebarsData($table);
        $this->renderTemplate('NormObject', $data['className'], $data, $isTest);
        $this->renderTemplate('NormCollection', $data['className'] . 'Collection', $data, $isTest);
        $this->renderRealmInfoTable($data, $table->name);
    }

    protected function renderRealmProperties() {
        $data = [];
        $data['namespace'] = '';
        $data['primaryDatastore'] = self::$_realms[$this->_realm]['primary_datastore'];
        $data['realm'] = $this->_realm;
        $data['tables'] = $this->_tableNames;
        $data['namespace'] = $this->_namespace;

        $engine = new Handlebars(array(
            'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__.'/templates/', array('extension' => 'txt')),
        ));
        $rendered = $engine->render('RealmProperties', $data);

        if(!file_exists(__DIR__ . '/../../../../../../../app/cache/angrychimps/norm/realmInfo/' . $this->_realm . '/_realmProperties.php')) {
            touch(__DIR__ . '/../../../../../../../app/cache/angrychimps/norm/realmInfo/' . $this->_realm . '/_realmProperties.php');
        }
        file_put_contents(__DIR__ . '/../../../../../../../app/cache/angrychimps/norm/realmInfo/' . $this->_realm . '/_realmProperties.php', $rendered);

    }

    protected function renderRealmInfoTable($data, $tableName) {
        $engine = new Handlebars(array(
            'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__.'/templates/', array('extension' => 'txt')),
        ));
        $rendered = $engine->render('TableInfo', $data);

        if(!file_exists(__DIR__ . '/../../../../../../../app/cache/angrychimps')) {
            mkdir(__DIR__ . '/../../../../../../../app/cache/angrychimps');
        }
        if(!file_exists(__DIR__ . '/../../../../../../../app/cache/angrychimps/norm')) {
            mkdir(__DIR__ . '/../../../../../../../app/cache/angrychimps/norm');
        }
        if(!file_exists(__DIR__ . '/../../../../../../../app/cache/angrychimps/norm/realmInfo')) {
            mkdir(__DIR__ . '/../../../../../../../app/cache/angrychimps/norm/realmInfo');
        }
        if(!file_exists(__DIR__ . '/../../../../../../../app/cache/angrychimps/norm/realmInfo/' . $this->_realm)) {
            mkdir(__DIR__ . '/../../../../../../../app/cache/angrychimps/norm/realmInfo/' . $this->_realm);
        }

        if(!file_exists(__DIR__ . '/../../../../../../../app/cache/angrychimps/norm/realmInfo/' . $this->_realm . '/' . $tableName . '.php')) {
            touch(__DIR__ . '/../../../../../../../app/cache/angrychimps/norm/realmInfo/' . $this->_realm . '/' . $tableName . '.php');
        }
        file_put_contents(__DIR__ . '/../../../../../../../app/cache/angrychimps/norm/realmInfo/' . $this->_realm . '/' . $tableName . '.php', $rendered);
    }

    protected function renderTemplate($templateName, $className, $data, $isTest) {

        $engine = new Handlebars(array(
            'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__.'/templates/', array('extension' => 'txt')),
        ));
        $rendered = $engine->render($templateName, $data);

        if($isTest) {
            $filename = __DIR__ . '/../../Tests/realms/NormTests/' . $this->_realm . '/' . $className . '.php';
        }
        else {
            $filename = __DIR__ . '/../../../../../../../src/AngryChimps/NormBundle/realms/Norm/' . $this->_realm . '/' . $className . '.php';
        }

        if(!file_exists($filename)) {
            touch($filename);
            file_put_contents($filename, $rendered);
        }
    }

    /**
     * @param Table $table
     * @return array
     */
    protected function getHandlebarsData(Table $table) {
        $data = array();

        $data['driver'] = self::$_realms[$this->_realm]['dsInfo']['driver'];
        $data['tableName'] = $table->name;
        $data['className'] = Utils::table2class($table->name);
        $data['realm'] = $this->_realm;
        $data['namespace'] = $this->_namespace;
        $data['primaryDatastoreName'] = /*!empty($this->_classConfigs[$table->name]['primaryDatastoreName'])
            ? $this->_classConfigs[$table->name]['primaryDatastoreName']
            :*/ self::$_realms[$this->_realm]['primary_datastore'];

        $data['fullyQualifiedClass'] = $data['namespace'] . "\\" . $data['className'];
        $data['fullyQualifiedClassWithPrecedingBackslash'] = "\\" . $data['namespace'] . "\\" . $data['className'];
        $data['classNameDoubleEscaped'] = "\\\\" . $data['namespace'] . "\\\\" . $data['className'];
        $data['collectionNameDoubleEscaped'] = "\\\\" . $data['namespace'] . "\\\\" . $data['className'] . 'Collection';

        $data['fieldNames'] = array();
        $data['propertyNames'] = array();
        $data['propertyTypes'] = array();
        $data['properties'] = array();
        $data['defaults'] = array();
        $data['autoIncrementFieldName'] = $table->autoIncrementName;
        $data['autoIncrementPropertyName'] = Utils::field2property($table->autoIncrementName);
        $data['autoGenerateFieldName'] = $table->autoGenerateName;
        $data['autoGeneratePropertyName'] = Utils::field2property($table->autoGenerateName);
        $data['fields'] = [];
        foreach($table->columns as $column) {
            /** @var $column Column */
            $data['fieldNames'][] = $column->name;
            switch($column->type) {
                case 'Date':
                case 'DateTime':
                    $data['fieldTypes'][] = "\\DateTime";
                    break;
                default:
                    $data['fieldTypes'][] = (string) $column->type;
            }

            if($column->default !== null) {
                $data['defaults'][] = array('statement' => '$this->' .
                    Utils::field2property($column->name) . ' = ' . $column->default . ';');
            }
            elseif(class_exists($column->type) && in_array("AC\\NormBundle\\core\\NormBaseCollection", class_parents($column->type))) {
                $data['defaults'][] = array('statement' => '$this->' .
                    Utils::field2property($column->name) . ' = new ' . $column->type . '();');
            }
            elseif(in_array($column->type, array('string[]', 'int[]', 'float[]', 'double[]', 'bool[]'))) {
                $data['defaults'][] = array('statement' => '$this->' .
                    Utils::field2property($column->name) . ' = array();');
            }

            $data['propertyNames'][] = Utils::field2property($column->name);
            $data['properties'][] = array(
                'name' => Utils::field2property($column->name),
                'type' => $data['fieldTypes'][count($data['fieldTypes']) - 1],
            );
            $data['validations'] = $column->validations;

            //For the Realm Info Template
            $fieldData = [];
            $fieldData['fieldName'] = $column->name;
            $fieldData['type'] = $column->type;
            $fieldData['propertyName'] = $column->getPropertyName();
            $data['fields'][] = $fieldData;
        }

        $data['fieldNamesQuotedString'] = Utils::array2quotedString($data['fieldNames']);
        $data['fieldTypesQuotedString'] = Utils::array2quotedString($data['fieldTypes']);
        $data['propertyNamesQuotedString'] = Utils::array2quotedString($data['propertyNames']);
        $data['hasAutoIncrement'] = ($data['autoIncrementFieldName'] === null) ? 'false' : 'true';

        $data['primaryKeyFieldNames'] = array();
        $data['primaryKeyPropertyNames'] = array();
        if($table->primaryKeyNames !== null) {
            $data['hasPrimaryKey'] = 'true';

            foreach($table->primaryKeyNames as $pk) {
                $data['primaryKeyFieldNames'][] = $pk;
                $data['primaryKeyPropertyNames'][] = Utils::field2property($pk);
            }
        }
        else {
            $data['hasPrimaryKey'] = 'false';
        }

        $data['primaryKeyFieldNamesQuotedString'] = Utils::array2quotedString($data['primaryKeyFieldNames']);
        $data['primaryKeyPropertyNamesQuotedString'] = Utils::array2quotedString($data['primaryKeyPropertyNames']);

        $data['foreignKeys'] = array();

        foreach($table->foreignKeys as $fk) {
            /** @var $fk ForeignKey */
            $newFk = array();
            $newFk['localColumnName'] = $fk->columnName;
            $newFk['remoteTableName'] = $fk->referencedTableName;
            $newFk['remoteColumn'] = $fk->referencedColumnName;
            $newFk['propertyName'] = self::getPropertyFromFkFieldName($fk->columnName);
            $newFk['propertyClass'] = Utils::table2class($fk->referencedTableName);
            $newFk['localPropertyIdFieldName'] = Utils::field2property($fk->referencedColumnName);
            $newFk['remotePropertyClass'] = Utils::table2class($fk->tableName);

            $newFk['remotePropertyIdFieldName'] = Utils::field2property($fk->referencedColumnName);
            $newFk['propertyClassWithNamespace'] = "\\" . $data['namespace'] . "\\"
                . Utils::table2class($fk->referencedTableName);
            $newFk['remotePropertyClassWithNamespace'] = $data['namespace'] . "\\"
                . Utils::table2class($fk->tableName);

            $data['foreignKeys'][] = $newFk;
        }

        foreach($table->reverseForeignKeys as $fk) {
            //Don't allow reverse foreign keys into the same table
            if($fk->tableName !== $fk->referencedTableName) {
                /** @var $fk ForeignKey */
                $newFk = array();
                $newFk['localColumnName'] = $fk->columnName;
                $newFk['localTableName'] = $fk->tableName;
                $newFk['remoteTableName'] = $fk->referencedTableName;
                $newFk['remoteColumn'] = $fk->referencedColumnName;
                $newFk['propertyName'] = self::getPropertyFromFkFieldName($fk->columnName);
                $newFk['localPropertyIdFieldName'] = Utils::field2property($fk->columnName);

                $newFk['remotePropertyClass'] = Utils::table2class($fk->tableName);
                $newFk['remotePropertyIdFieldName'] = Utils::field2property($fk->referencedColumnName);
                $newFk['propertyClass'] = Utils::table2class($fk->referencedTableName);

                $newFk['propertyClassWithNamespace'] = $data['namespace'] . "\\"
                    . Utils::table2class($fk->referencedTableName);
                $newFk['remotePropertyClassWithNamespace'] = $data['namespace'] . "\\"
                    . Utils::table2class($fk->tableName);

                $data['reverseForeignKeys'][] = $newFk;
            }
        }

        //Process Enums
        $data['enums'] = array();
        foreach($table->enums as $enum) {
            $enumArray = array();
            $cnt = 1;

            foreach($enum->values as $value) {
                $valueArray = array();
                $valueArray['name'] = Utils::camel2TrainCase($value) . '_' .  Utils::camel2TrainCase($enum->name);
                $valueArray['value'] = $cnt;
                $enumArray['values'][] = $valueArray;
                $cnt++;
            }

            $data['enums'][] = $enumArray;
        }

//        if(isset($this->_reverseForeignKeysByTable[$table->getName()])) {
//            foreach($this->_reverseForeignKeysByTable[$table->getName()] as $fk) {
//                /** @var $fk \Doctrine\DBAL\Schema\ForeignKeyConstraint */
//                $localColumns = $fk->getLocalColumns();
//                $remoteColumns = $fk->getForeignColumns();
//                $localTableName = $fk->getLocalTableName();
//                $remoteTableName = $fk->getForeignTableName();
//
//                if($this->_tables[$remoteTableName])
//
//                if(count($localColumns) != 1 || count($remoteColumns) != 1) {
//                    throw new \norm\core\exceptions\InvalidForeignKeyMultipleColumns($this->_realm, $fk->getName());
//                }
//                $newFk = array();
//                $newFk['localColumnName'] = $localColumns[0];
//                $newFk['localTableName'] = localTableName;
//                $newFk['remoteTableName'] = $remoteTableName;
//                $newFk['remoteColumn'] = $remoteColumns[0];
//                $newFk['propertyName'] = self::getPropertyFromFkFieldName($localColumns[0]);
//                $newFk['propertyClass'] = Utils::table2class($fk->getForeignTableName());
//                $newFk['propertyClassWithNamespace'] = "\\norm\\realms\\" . $this->_realm . "\\"
//                    . Utils::table2class($fk->getForeignTableName());
//                $newFk['localPropertyIdFieldName'] = Utils::field2property($localColumns[0]);
//
//                $data['reverseForeignKeys'][] = $newFk;
//            }
//        }

        $this->_tableNames[] = array(
            'tableName' => $table->name,
            'realmInfo' => '$realms["' . $this->_realm . '"]',
            'tableInfo' => '$realms["' . $this->_realm . '"]["' . $table->name . '"]',
        );
        return $data;
    }


    public function dump_schema($realm) {
        $ds = DatastoreManager::getReferenceDatastore($realm);
        $gen = new MysqlGenerator($ds);
        $schema = $gen->getSchema();
        print_r($schema);
    }

    protected static function getPropertyFromFkFieldName($fieldName) {
        if(strstr('_id', $fieldName) != strlen($fieldName) - 3) {
            return ucfirst(substr($fieldName, 0, strlen($fieldName) - 3));
        }
        else {
            return ucfirst($fieldName);
        }
    }
}