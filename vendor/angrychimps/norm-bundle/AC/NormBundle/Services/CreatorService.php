<?php


namespace AC\NormBundle\services;


use AC\NormBundle\core\generator\types\AbstractEntityOrSubclass;
use AC\NormBundle\core\generator\types\Field;
use AC\NormBundle\core\generator\types\Entity;
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

    public function getData() {
        return $this->data;
    }

    public function createIfNecessary($force = false)
    {
        if ($force || !file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm/classes.php')) {
            if (!is_dir(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm')) {
                mkdir(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/norm');
            }

            $gen = new YamlGenerator($this->namespace, $this->datastores);
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

        foreach($this->schema->datastores as $datastoreName => $datastore) {
            $dsData = (array) $datastore;
            $data['datastores'][] = $dsData;
        }

        foreach ($this->schema->entities as $entity) {
            $data['entities'][] = $this->generateEntityOrSubclassData($entity, $this->schema->namespace,
                $data['traitFullNames'], $data['traitShortNames']);
        }

        foreach ($this->schema->subclasses as $subclass) {
            $data['subclasses'][] = $this->generateEntityOrSubclassData($subclass, $this->schema->namespace,
                $data['traitFullNames'], $data['traitShortNames']);
        }

        $this->data = $data;
    }

    protected function generateEntityOrSubclassData(AbstractEntityOrSubclass $entity, $namespace,
                                                    array &$traitFullNames, array &$traitShortNames) {
        $entityData = [];
        $entityData['name'] = $entity->name;
        $entityData['objectNameWithoutNamespace'] = Utils::table2class($entity->name);
        $entityData['objectName'] = $namespace . "\\" . Utils::table2class($entity->name);

        $entityData['traitFullNames'] = [];
        $entityData['traitShortNames'] = [];

        //Set to false and change if necessary
        $entityData['usesRiak2'] = false;
        $entityData['usesElasticsearch'] = false;
        $entityData['primaryIsRiak2'] = false;
        $entityData['primaryIsElasticsearch'] = false;

        if($entity instanceof Entity) {
            $entityData['driver'] = $this->datastores[$entity->primaryDatastore->name]['driver'];
            $entityData['riakSolrIndexName'] = $entity->name;
            $entityData['isElasticsearch'] = $entityData['driver'] === 'elasticsearch';
            $entityData['usesRiak2'] = $entityData['driver'] === 'riak2';
            $entityData['primaryKeyFieldNamesString'] = '["' . implode('", "', $entity->primaryKeyNames) . '"]';
            $entityData['primaryKeyPropertyNamesString'] = '["';
            foreach($entity->primaryKeyNames as $pkName) {
                $entityData['primaryKeyPropertyNamesString'] .= $pkName . ", ";
            }
            $entityData['primaryKeyPropertyNamesString'] = rtrim($entityData['primaryKeyPropertyNamesString'], ", ");
            $entityData['primaryKeyPropertyNamesString'] .= '"]';

            $entityData['autoIncrementField'] = $entity->autoIncrementName;
            $entityData['autoIncrementProperty'] = Utils::field2property($entity->autoIncrementName);
            $entityData['autoGenerateField'] = $entity->autoGenerateName;
            $entityData['autoGenerateProperty'] = Utils::field2property($entity->autoGenerateName);
            if($entity->apiPublicFields === []) {
                $entityData['apiPublicFieldsString'] = '[]';
            }
            else {
                $entityData['apiPublicFieldsString'] = '["' . implode('", "', $entity->apiPublicFields) . '"]';
            }
            if($entity->apiPrivateFields === []) {
                $entityData['apiPrivateFieldsString'] = '[]';
            }
            else {
                $entityData['apiPrivateFieldsString'] = '["' . implode('", "', $entity->apiPrivateFields) . '"]';
            }
            if($entity->apiHiddenButSettableFields === []) {
                $entityData['apiHiddenButSettableFields'] = '[]';
            }
            else {
                $entityData['apiHiddenButSettableFields'] = '["' . implode('", "', $entity->apiHiddenButSettableFields) . '"]';
            }

            $entityData['datastores'] = [];
            foreach($entity->datastores as $datastore) {
                $ds = [];
                $ds['name'] = $datastore->name;
                $ds['type'] = $datastore->type;
                $ds['method'] = $datastore->method;
                $entityData['datastores'][] = $ds;

                if($datastore->type === 'primary') {
                    $entityData['primaryDatastore'] = $ds;
                    $entityData['primaryDatastoreName'] = $ds['name'];
                }

                switch($datastore->method) {
                    case 'riak2_map':
                        $entityData['usesRiak2'] = true;
                        break;
                    case 'elasticsearch':
                        $entityData['usesElasticsearch'] = true;
                        break;
                    default:
                        throw new \Exception("Unknown datastore method");
                }
            }

            switch($entityData['primaryDatastore']['method']) {
                case 'riak2_map':
                    $entityData['primaryIsRiak2'] = true;
                    break;
                case 'elasticsearch':
                    $entityData['primaryIsElasticsearch'] = true;
                    break;
                default:
                    throw new \Exception("Unknown datastore method");
            }

            //Setup traitNames if any datastore is of that type
            if($entityData['usesRiak2']) {
                $entityData['traitFullNames'][] = 'AC\NormBundle\services\traits\Riak2MapTrait';
                $entityData['traitShortNames'][] = 'Riak2MapTrait';
                $entityData['traitFullNames'][] = 'AC\NormBundle\services\traits\Riak2Trait';
                $entityData['traitShortNames'][] = 'Riak2Trait';
            }
            if($entityData['usesElasticsearch']) {
                $entityData['traitFullNames'][] = 'AC\NormBundle\services\traits\ElasticsearchTrait';
                $entityData['traitShortNames'][] = 'ElasticsearchTrait';
            }

            //Set at top level data as well
            $traitFullNames = array_merge($traitFullNames, $entityData['traitFullNames']);
            $traitShortNames = array_merge($traitShortNames, $entityData['traitShortNames']);
        }


        $fieldNames = [];
        $propertyNames = [];
        $fieldTypes = [];
        foreach ($entity->fields as $field) {
            $fieldNames[] = $field->name;
            $fieldData = $this->generateFieldData($field, $entityData);

            $entityData['fields'][] = $fieldData;
            if($fieldData['type'] === 'bool') {
                $entityData['flags'][] = $fieldData;
            }
            elseif($field->type === 'set') {
                $entityData['sets'][] = $fieldData;
            }
            elseif(strpos($field->type, 'Collection') === strlen($field->type) - 10) {
                $entityData['sets'][] = $fieldData;
            }
            elseif(strpos($field->type, '[]') === strlen($field->type) - 2) {
                $entityData['sets'][] = $fieldData;
            }
            elseif($field->type === 'Currency') {
                $entityData['currencies'][] = $fieldData;
            }
            elseif($field->type === 'Counter') {
                $entityData['counters'][] = $fieldData;
            }
            elseif($field->type === 'Counter') {
                $entityData['counters'][] = $fieldData;
            }
            elseif(strpos($field->type, $namespace) === 1) { //skips the leading / in type
                $entityData['subclasses'][] = $fieldData;
            }
            else {
                $entityData['registers'][] = $fieldData;
            }
        }

        foreach($fieldTypes as &$type) {
            $type = str_replace("\\", "\\\\", $type);
        }
        $entityData['fieldNamesString'] = '["' . implode('", "', $fieldNames) . '"]';
        $entityData['propertyNamesString'] = '["' . implode('", "', $propertyNames) . '"]';
        $entityData['fieldTypesString'] = '["' . implode('", "', $fieldTypes) . '"]';

        /** @var Enum $enum */
        foreach($entity->enums as $enum) {
            $enumArray = array();
            $cnt = 1;

            foreach($enum->values as $value) {
                $valueArray = array();
                $valueArray['name'] = Utils::camel2TrainCase($value) . '_' .  Utils::camel2TrainCase($enum->name);
                $valueArray['value'] = $cnt;
                $enumArray['values'][] = $valueArray;
                $cnt++;
            }

            $entityData['enums'][] = $enumArray;
        }

        return $entityData;
    }

    protected function generateFieldData(Field $field, array &$entityOrSubclassData) {
        $fieldData = [];
        $fieldData['name'] = $field->name;
        $fieldData['propertyName'] = Utils::field2property($field->name);
        $fieldData['propertyNameCapitalized'] = ucfirst(Utils::field2property($field->name));
        $fieldData['type'] = $field->type;
        $fieldData['typeIsSubclass'] = (strpos($field->type, $this->namespace) === 1); //skips the leading / in type
        $fieldData['includeInAll'] = $field->includeInAll ? 'true' : 'false';
        $fieldData['indexName'] = $field->indexName;
        $fieldData['usesRiak2'] = isset($entityOrSubclassData['driver']) && ($entityOrSubclassData['driver'] === 'riak2');
        $fieldData['isDateTime'] = false;
        switch($field->type) {
            case 'Currency':
                $fieldData['phpType'] = 'float';
                $fieldData['elasticsearchType'] = 'float';
                $fieldData['mysqlType'] = 'double';
                break;
            case 'Location':
                $fieldData['phpType'] = 'string';
                $fieldData['elasticsearchType'] = 'geo_point';
                $fieldData['mysqlType'] = 'point';
                break;
            case 'Date':
                $fieldData['phpType'] = '\\DateTime';
                $fieldData['elasticsearchType'] = 'date';
                $fieldData['mysqlType'] = 'Date';
                $fieldData['isDateTime'] = true;
                break;
            case 'DateTime':
                $fieldData['phpType'] = '\\DateTime';
                $fieldData['elasticsearchType'] = 'date';
                $fieldData['mysqlType'] = 'DateTime';
                $fieldData['isDateTime'] = true;
                break;
            case 'Time':
                $fieldData['phpType'] = '\\DateTime';
                $fieldData['elasticsearchType'] = 'date';
                $fieldData['mysqlType'] = 'DateTime';
                $fieldData['isDateTime'] = true;
                break;
            case 'Uuid':
                $fieldData['phpType'] = 'string';
                $fieldData['elasticsearchType'] = 'string';
                $fieldData['mysqlType'] = 'varchar';
                break;
            case 'Email':
                $fieldData['phpType'] = 'string';
                $fieldData['elasticsearchType'] = 'string';
                $fieldData['mysqlType'] = 'varchar';
                break;
            case 'Counter':
                $fieldData['phpType'] = 'int';
                $fieldData['elasticsearchType'] = 'int';
                $fieldData['mysqlType'] = 'int';
                break;
            case 'bool':
                $fieldData['phpType'] = 'bool';
                $fieldData['elasticsearchType'] = 'boolean';
                $fieldData['mysqlType'] = 'bool';
                break;
            case 'string':
                $fieldData['phpType'] = 'string';
                $fieldData['elasticsearchType'] = 'string';
                $fieldData['mysqlType'] = 'varchar';
                break;
            case 'text':
                $fieldData['phpType'] = 'string';
                $fieldData['elasticsearchType'] = 'string';
                $fieldData['mysqlType'] = 'text';
                break;
            case 'int':
                $fieldData['phpType'] = 'int';
                $fieldData['elasticsearchType'] = 'integer';
                $fieldData['mysqlType'] = 'int';
                break;
            case 'float':
                $fieldData['phpType'] = 'float';
                $fieldData['elasticsearchType'] = 'float';
                $fieldData['mysqlType'] = 'float';
                break;
            case 'decimal':
                $fieldData['phpType'] = 'float';
                $fieldData['elasticsearchType'] = 'float';
                $fieldData['mysqlType'] = 'decimal';
                break;
            case 'enum':
                $fieldData['phpType'] = 'int';
                $fieldData['elasticsearchType'] = 'int';
                $fieldData['mysqlType'] = 'int';
                break;
            case 'set':
                $fieldData['phpType'] = 'int[]';
                $fieldData['elasticsearchType'] = 'int';
                $fieldData['mysqlType'] = 'set';
                break;
            default:
                $fieldData['phpType'] = $field->type;
                $fieldData['elasticsearchType'] = $field->type;
                $fieldData['mysqlType'] = $field->type;
        }


        $fieldNames[] = $field->name;
        $propertyNames[] =  Utils::field2property($field->name);
        $fieldTypes[] = $field->type;

        //Set defaults
        if($field->default !== null) {
            $entityOrSubclassData['defaults'][] = array('statement' => '$this->set' . $fieldData['propertyNameCapitalized'] .
                '( ' . $field->default . ');');
        }
        elseif(strpos($field->type, 'Collection') === strlen($field->type) - 10) {
            $entityOrSubclassData['defaults'][] = array('statement' => '$this->set' . $fieldData['propertyNameCapitalized'] .
                '(new ' . $field->type . ');');
        }
        elseif(isset($entityOrSubclassData['autoGenerateField']) && $entityOrSubclassData['autoGenerateField'] === $field->name) {
            $entityOrSubclassData['defaults'][] = array('statement' => '$this->set' . $fieldData['propertyNameCapitalized'] .
                '(sha1(microtime(true) . bin2hex(openssl_random_pseudo_bytes(16))));');
        }

        return $fieldData;
    }

    public function createValidations(Schema $schema) {
        $data = array();

        foreach($schema->entities as $table) {
            $tableStarted = false;
            foreach($table->fields as $field) {
                if(isset($field->validations)) {
                    $fullClassName = $schema->namespace . "\\" . Utils::table2class($table->name);
                    if(!$tableStarted) {
                        $data[$fullClassName] = array();
                        $data[$fullClassName]['properties'] = array();
                        $tableStarted = true;
                    }
                    $data[$fullClassName]['properties'][Utils::field2property($field->name)] = $field->validations;
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