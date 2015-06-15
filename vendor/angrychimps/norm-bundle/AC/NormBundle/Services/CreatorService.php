<?php


namespace AC\NormBundle\services;


use AC\NormBundle\core\generator\types\Table;
use AC\NormBundle\core\Utils;
use AC\NormBundle\core\generator\generators\YamlGenerator;
use AC\NormBundle\core\generator\types\Schema;
use Handlebars\Handlebars;
use AC\NormBundle\core\generator\types\Enum;
use Symfony\Component\Yaml\Dumper;

class CreatorService
{
    /** @var  string The environment */
    protected $environment;

    /** @var  Schema */
    protected $schema;

    /** @var array The data associated with all of the realms */
    protected $data = [];

    protected $namespace;

    private $datastores;

    public function __construct($environment, $namespace, $datastores)
    {
        $this->environment = $environment;
        $this->namespace = $namespace;
        $this->datastores = $datastores;
    }

    public function setEnvironment($env)
    {
        $this->environment = $env;
    }

    public function createIfNecessary($force = false)
    {
        if ($force || !file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm/classes.php')) {
            if (!is_dir(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm')) {
                mkdir(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm');
            }

            $gen = new YamlGenerator($this->namespace);
            $this->schema = $gen->getSchema();

            $this->createValidations($this->schema);

            $this->generateData();

            $this->createClassesFile();

            $this->createPropertiesFile();
            $this->generateServiceFiles();
        }
    }

    protected function generateServiceFiles()
    {
        //Create the Service file if necessary
        $filename = __DIR__ . '/../../../../../../src/AngryChimps/NormBundle/services/NormService.php';
        if (!file_exists($filename)) {
            $engine = new Handlebars(array(
                'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__ . '/../core/generator/templates/', array('extension' => 'handlebars')),
            ));
            $rendered = $engine->render('Service', $this->data);

            touch($filename);
            file_put_contents($filename, $rendered);
        }

        //Create the BaseService file
        $engine = new Handlebars(array(
            'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__ . '/../core/generator/templates/', array('extension' => 'handlebars')),
        ));
        $rendered = $engine->render('BaseService', $this->data);

        $filename = __DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm/NormBaseService.php';
        if (!file_exists($filename)) {
            touch($filename);
        }
        file_put_contents($filename, $rendered);

    }

    protected function createPropertiesFile()
    {
        $engine = new Handlebars(array(
            'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__.'/../core/generator/templates/', array('extension' => 'handlebars')),
        ));
        $rendered = $engine->render('Structure', $this->data);

        if (!file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm/structure.php')) {
            touch(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm/structure.php');
        }
        file_put_contents(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm/structure.php', $rendered);
    }

    protected function createClassesFile()
    {
        $engine = new Handlebars(array(
            'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__.'/../core/generator/templates/', array('extension' => 'handlebars')),
        ));
        $rendered = $engine->render('Classes', $this->data);

        if (!file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm/classes.php')) {
            touch(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm/classes.php');
        }
        file_put_contents(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm/classes.php', $rendered);
    }

    protected function generateData()
    {
        $data = [];
        $data['namespace'] = $this->schema->namespace;
        $data['serviceNamespace'] = "AngryChimps\\NormBundle\\services";
        $data['baseServiceNamespace'] = "AC\\NormBundle\\cached";
        $data['traitFullNames'] = [];
        $data['traitShortNames'] = [];

        foreach ($this->schema->tables as $table) {
            $tableData = [];
            $tableData['driver'] = $this->datastores[$table->primaryDatastore->name]['driver'];
            $tableData['isElasticsearch'] = $tableData['driver'] === 'elasticsearch';
//            $tableData['isMysql'] = $tableData['driver'] === 'mysql';
//            $tableData['isRiak2'] = $tableData['driver'] === 'riak2';
            $tableData['usesRiak2'] = $tableData['driver'] === 'riak2';
            $tableData['tableName'] = $table->name;
            $tableData['objectNameWithoutNamespace'] = Utils::table2class($table->name);
            $tableData['objectName'] = $data['namespace'] . "\\" . Utils::table2class($table->name);
//            $tableData['primaryDatastoreName'] = $table->primaryDatastore;
//            $tableData['primaryKeyFieldNames'] = $table->primaryKeyNames;
            $tableData['primaryKeyFieldNamesString'] = '["' . implode('", "', $table->primaryKeyNames) . '"]';
//            $tableData['primaryKeyPropertyNames'] = $table->primaryKeyPropertyNames;
            $tableData['primaryKeyPropertyNamesString'] = '["' . implode('", "', $table->primaryKeyPropertyNames) . '"]';
            $tableData['autoIncrementField'] = $table->autoIncrementName;
            $tableData['autoIncrementProperty'] = Utils::field2property($table->autoIncrementName);
            $tableData['autoGenerateField'] = $table->autoGenerateName;
            $tableData['autoGenerateProperty'] = Utils::field2property($table->autoGenerateName);
            $tableData['traitFullNames'] = [];
            $tableData['traitShortNames'] = [];

            switch($tableData['driver']) {
                case 'riak1_blob':
                    $tableData['traitFullNames'] = ['AC\NormBundle\services\traits\Riak1BlobTrait'];
                    $tableData['traitShortNames'] = ['Riak1BlobTrait'];
                    break;
                case 'mysql':
                    $tableData['traitFullNames'] = ['AC\NormBundle\services\traits\MysqlTrait', 'AC\NormBundle\services\traits\PdoTrait'];
                    $tableData['traitShortNames'] = ['MysqlTrait', 'PdoTrait'];
                    break;
                case 'elasticsearch':
                    $tableData['traitFullNames'] = ['AC\NormBundle\services\traits\ElasticsearchTrait'];
                    $tableData['traitShortNames'] = ['ElasticsearchTrait'];
                    break;
                case 'riak2':
                    $tableData['traitFullNames'] = ['AC\NormBundle\services\traits\Riak2MapTrait', 'AC\NormBundle\services\traits\Riak2Trait'];
                    $tableData['traitShortNames'] = ['Riak2MapTrait', 'Riak2Trait'];
                    break;
                default:
                    throw new \Exception('Unsupported driver type');
            }

            //Set at top level data as well
            $data['traitFullNames'] = array_merge_recursive($data['traitFullNames'], $tableData['traitFullNames']);
            $data['traitShortNames'] = array_merge_recursive($data['traitShortNames'], $tableData['traitShortNames']);

            $fieldNames = [];
            $propertyNames = [];
            $fieldTypes = [];
            foreach ($table->columns as $column) {
                $columnData = [];
                $columnData['columnName'] = $column->name;
//                $columnData['tableName'] = $table->name;
                $columnData['propertyName'] = $column->getPropertyName();
                $columnData['propertyNameCapitalized'] = ucfirst($column->getPropertyName());
                $columnData['type'] = $column->type;
                $columnData['includeInAll'] = $column->includeInAll ? 'true' : 'false';
                $columnData['indexName'] = $column->indexName;
                $columnData['usesRiak2'] = $tableData['usesRiak2'];
                $columnData['isDateTime'] = false;
                switch($column->type) {
                    case 'Currency':
                        $columnData['phpType'] = 'float';
                        $columnData['elasticsearchType'] = 'float';
                        $columnData['mysqlType'] = 'double';
                        break;
                    case 'Location':
                        $columnData['phpType'] = 'string';
                        $columnData['elasticsearchType'] = 'geo_point';
                        $columnData['mysqlType'] = 'point';
                        break;
                    case 'Date':
                        $columnData['phpType'] = '\\DateTime';
                        $columnData['elasticsearchType'] = 'date';
                        $columnData['mysqlType'] = 'Date';
                        $columnData['isDateTime'] = true;
                        break;
                    case 'DateTime':
                        $columnData['phpType'] = '\\DateTime';
                        $columnData['elasticsearchType'] = 'date';
                        $columnData['mysqlType'] = 'DateTime';
                        $columnData['isDateTime'] = true;
                        break;
                    case 'Uuid':
                        $columnData['phpType'] = 'string';
                        $columnData['elasticsearchType'] = 'string';
                        $columnData['mysqlType'] = 'varchar';
                        break;
                    case 'Email':
                        $columnData['phpType'] = 'string';
                        $columnData['elasticsearchType'] = 'string';
                        $columnData['mysqlType'] = 'varchar';
                        break;
                    case 'Counter':
                        $columnData['phpType'] = 'int';
                        $columnData['elasticsearchType'] = 'int';
                        $columnData['mysqlType'] = 'int';
                        break;
                    case 'bool':
                        $columnData['phpType'] = 'bool';
                        $columnData['elasticsearchType'] = 'boolean';
                        $columnData['mysqlType'] = 'bool';
                        break;
                    case 'string':
                        $columnData['phpType'] = 'string';
                        $columnData['elasticsearchType'] = 'string';
                        $columnData['mysqlType'] = 'varchar';
                        break;
                    case 'text':
                        $columnData['phpType'] = 'string';
                        $columnData['elasticsearchType'] = 'string';
                        $columnData['mysqlType'] = 'text';
                        break;
                    case 'int':
                        $columnData['phpType'] = 'int';
                        $columnData['elasticsearchType'] = 'integer';
                        $columnData['mysqlType'] = 'int';
                        break;
                    case 'float':
                        $columnData['phpType'] = 'float';
                        $columnData['elasticsearchType'] = 'float';
                        $columnData['mysqlType'] = 'float';
                        break;
                    case 'decimal':
                        $columnData['phpType'] = 'float';
                        $columnData['elasticsearchType'] = 'float';
                        $columnData['mysqlType'] = 'decimal';
                        break;
                    case 'enum':
                        $columnData['phpType'] = 'int';
                        $columnData['elasticsearchType'] = 'int';
                        $columnData['mysqlType'] = 'int';
                        break;

                }


                $fieldNames[] = $column->name;
                $propertyNames[] = $column->getPropertyName();
                $fieldTypes[] = $column->type;

                //Set defaults
                if($column->default !== null) {
                    $tableData['defaults'][] = array('statement' => '$this->set' . $columnData['propertyNameCapitalized'] .
                        '( ' . $column->default . ');');
                }
                elseif(strpos($column->type, 'Collection') === strlen($column->type) - 10) {
                    $tableData['defaults'][] = array('statement' => '$this->set' . $columnData['propertyNameCapitalized'] .
                        '(new ' . $column->type . ');');
                }
                elseif($table->autoGenerateName == $column->name) {
                    $tableData['defaults'][] = array('statement' => '$this->set' . $columnData['propertyNameCapitalized'] .
                        '(bin2hex(openssl_random_pseudo_bytes(16)));');
                }

                $tableData['columns'][] = $columnData;
                if($columnData['type'] === 'bool') {
                    $tableData['flags'][] = $columnData;
                }
                elseif(strpos($column->type, 'Collection') === strlen($column->type) - 10) {
                    $tableData['sets'][] = $columnData;
                }
                elseif(strpos($column->type, '[]') === strlen($column->type) - 2) {
                    $tableData['sets'][] = $columnData;
                }
                elseif($column->type === 'Currency') {
                    $tableData['currencies'][] = $columnData;
                }
                elseif($column->type === 'Counter') {
                    $tableData['counters'][] = $columnData;
                }
                else {
                    $tableData['registers'][] = $columnData;
                }
            }

            foreach($fieldTypes as &$type) {
                $type = str_replace("\\", "\\\\", $type);
            }
            $tableData['fieldNamesString'] = '["' . implode('", "', $fieldNames) . '"]';
            $tableData['propertyNamesString'] = '["' . implode('", "', $propertyNames) . '"]';
            $tableData['fieldTypesString'] = '["' . implode('", "', $fieldTypes) . '"]';

            /** @var Enum $enum */
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

                $tableData['enums'][] = $enumArray;
            }

            $data['tables'][] = $tableData;
        }

        $this->data = $data;
    }

    public function createValidations(Schema $schema) {
        $data = array();

        foreach($schema->tables as $table) {
            $tableStarted = false;
            foreach($table->columns as $column) {
                if(isset($column->validations)) {
                    $fullClassName = $schema->namespace . "\\" . Utils::table2class($table->name);
                    if(!$tableStarted) {
                        $data[$fullClassName] = array();
                        $data[$fullClassName]['properties'] = array();
                        $tableStarted = true;
                    }
                    $data[$fullClassName]['properties'][$column->getPropertyName()] = $column->validations;
                }
            }
        }

        $dumper = new Dumper();

        $yaml = $dumper->dump($data, 5);

        $filename = __DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm/validations.yml';

        if(!file_exists($filename)) {
            touch($filename);
        }
        file_put_contents($filename, $yaml);
    }
}