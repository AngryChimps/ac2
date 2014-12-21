<?php


namespace AC\NormBundle\Services;


use AC\NormBundle\core\generator\types\Table;
use AC\NormBundle\core\Utils;
use AC\NormBundle\core\generator\generators\YamlGenerator;
use AC\NormBundle\core\generator\types\Schema;
use AC\NormBundle\core\generator\types\Norm;
use Handlebars\Handlebars;
use AC\NormBundle\core\generator\types\Enum;
use Symfony\Component\Yaml\Dumper;

class RealmInfoCreatorService {
    /** @var string[] All of the realm names */
    private $realmNames = [];

    private $realms = [];

    /** @var  string The environment */
    protected $environment;

    /** @var Norm An array of schemas */
    protected $norm;

    /** @var array The data associated with all of the realms */
    protected $data = [];

    public function __construct($environment)
    {
        $this->environment = $environment;
    }

    public function createIfNecessary() {
        if(!file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realmProperties.php')) {
        $this->populateRealms();
        $this->createRealmFolders();

        $this->norm = new Norm();
        $this->generateRealmInfoFolders();
        foreach($this->realmNames as $realmName) {
            $gen = new YamlGenerator($realmName, $this->realms[$realmName]['namespace']);
            $schema = $gen->getSchema();
            $this->norm->schemas[$realmName] = $schema;
            $this->createValidations($schema);
        }

        $this->generateRealmData();

        foreach($this->realmNames as $realmName) {
            $this->createRealmClassesFile($realmName);
        }

        $this->createRealmPropertiesFile();
        $this->generateServiceFiles();
        }
    }

    protected function generateServiceFiles()
    {
        foreach ($this->realmNames as $realmName) {
            //Create the Service file if necessary
            $filename = __DIR__ . '/../../../../../../src/AngryChimps/NormBundle/realms/Norm/' . $realmName . '/services/Norm' . ucfirst($realmName) . 'Service.php';
            if (!file_exists($filename)) {
                $engine = new Handlebars(array(
                    'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__ . '/../core/generator/templates/', array('extension' => 'txt')),
                ));
                $rendered = $engine->render('Service', $this->getDataForRealm($realmName));

                touch($filename);
                file_put_contents($filename, $rendered);
            }

            //Create the BaseService file
            $engine = new Handlebars(array(
                'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__ . '/../core/generator/templates/', array('extension' => 'txt')),
            ));
            $rendered = $engine->render('BaseService', $this->getDataForRealm($realmName));

            $filename = __DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms/' . $realmName . '/services/Norm' . ucfirst($realmName) . 'BaseService.php';
            if (!file_exists($filename)) {
                touch($filename);
            }
            file_put_contents($filename, $rendered);

        }
    }

    protected function createRealmPropertiesFile()
    {
        $engine = new Handlebars(array(
            'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__.'/../core/generator/templates/', array('extension' => 'txt')),
        ));
        $rendered = $engine->render('RealmProperties', $this->data);

        if (!file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realmProperties.php')) {
            touch(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realmProperties.php');
        }
        file_put_contents(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realmProperties.php', $rendered);
    }

    protected function createRealmClassesFile($realmName)
    {
        $engine = new Handlebars(array(
            'loader' => new \Handlebars\Loader\FilesystemLoader(__DIR__.'/../core/generator/templates/', array('extension' => 'txt')),
        ));
        $rendered = $engine->render('Classes', $this->getDataForRealm($realmName));

        if (!file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms/' . $realmName . '/classes/classes.php')) {
            touch(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms/' . $realmName . '/classes/classes.php');
        }
        file_put_contents(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms/' . $realmName . '/classes/classes.php', $rendered);
    }

    private function getDataForRealm($realmName) {
        foreach($this->data['realms'] as $realmData) {
            if($realmData['realmName'] == $realmName) {
                return $realmData;
            }
        }
    }

    protected function generateRealmData()
    {
        foreach ($this->norm->schemas as $schemaName => $schemaInfo) {
            $data = [];
            $data['realmName'] = $schemaName;
            $data['realmNameCapitalized'] = ucfirst($schemaName);
            $data['namespace'] = $this->realms[$schemaName]['namespace'];
            $data['realmServiceNamespace'] = "AngryChimps\\NormBundle\\realms\\Norm\\$schemaName\\services";
            $data['realmBaseServiceNamespace'] = "AC\\NormBundle\\cached\\realms\\$schemaName\\services";
            $data['primaryDatastoreName'] = $this->realms[$schemaName]['primary_datastore'];
            $data['driver'] = $this->realms[$schemaName]['dsInfo']['driver'];
            $data['driverIsMysql'] = $this->realms[$schemaName]['dsInfo']['driver'] === 'mysql';

            switch($data['driver']) {
                case 'riak_blob':
                    $data['traitNames'] = [
                        ['traitName' => 'AC\NormBundle\Services\traits\RiakBlobTrait', 'traitShortName' => 'RiakBlobTrait'],
                        ['traitName' => 'AC\NormBundle\Services\traits\RiakTrait', 'traitShortName' => 'RiakTrait']
                    ];
                    break;
                case 'mysql':
                    $data['traitNames'] = [
                        ['traitName' => 'AC\NormBundle\Services\traits\MysqlTrait', 'traitShortName' => 'MysqlTrait'],
                        ['traitName' => 'AC\NormBundle\Services\traits\PdoTrait', 'traitShortName' => 'PdoTrait']
                    ];
                    break;
                case 'elasticsearch':
                    $data['traitNames'] = [
                        ['traitName' => 'AC\NormBundle\Services\traits\ElasticsearchTrait', 'traitShortName' => 'ElasticsearchTrait']
                    ];
                    break;
            }

            foreach ($schemaInfo->tables as $table) {
                $tableData = [];
                $tableData['realmName'] = $schemaName;
                $tableData['driverIsMysql'] = $this->realms[$schemaName]['dsInfo']['driver'] === 'mysql';
                $tableData['realmName'] = $schemaInfo->realm;
                $tableData['tableName'] = $table->name;
                $tableData['objectNameWithoutNamespace'] = Utils::table2class($table->name);
                $tableData['collectionNameWithoutNamespace'] = Utils::table2class($table->name) . 'Collection';
                $tableData['objectName'] = $data['namespace'] . "\\" . Utils::table2class($table->name);
                $tableData['collectionName'] = $data['namespace'] . "\\" . Utils::table2class($table->name) . 'Collection';
                $tableData['primaryDatastoreName'] = $this->realms[$schemaName]['primary_datastore'];
                $tableData['primaryKeyFieldNames'] = $table->primaryKeyNames;
                $tableData['primaryKeyFieldNamesString'] = '["' . implode('", "', $table->primaryKeyNames) . '"]';
                $tableData['primaryKeyPropertyNames'] = $table->primaryKeyPropertyNames;
                $tableData['primaryKeyPropertyNamesString'] = '["' . implode('", "', $table->primaryKeyPropertyNames) . '"]';
                $tableData['autoIncrementField'] = $table->autoIncrementName;
                $tableData['autoIncrementProperty'] = Utils::field2property($table->autoIncrementName);
                $tableData['autoGenerateField'] = $table->autoGenerateName;
                $tableData['autoGenerateProperty'] = Utils::field2property($table->autoGenerateName);

                $fieldNames = [];
                $propertyNames = [];
                $fieldTypes = [];
                foreach ($table->columns as $column) {
                    $columnData = [];
                    $columnData['columnName'] = $column->name;
                    $columnData['tableName'] = $table->name;
                    $columnData['realmName'] = $schemaInfo->realm;
                    $columnData['propertyName'] = $column->getPropertyName();
                    $columnData['type'] = $column->type;

                    $fieldNames[] = $column->name;
                    $propertyNames[] = $column->getPropertyName();
                    $fieldTypes[] = $column->type;

                    if($column->default !== null) {
                        $tableData['defaults'][] = array('statement' => '$this->' .
                            Utils::field2property($column->name) . ' = ' . $column->default . ';');
                    }
                    elseif(strpos($column->type, 'Collection') == strlen($column->type) - 10) {
                        $tableData['defaults'][] = array('statement' => '$this->' .
                            Utils::field2property($column->name) . ' = new ' . $column->type . '();');
                    }
                    elseif(in_array($column->type, array('string[]', 'int[]', 'float[]', 'double[]', 'bool[]'))) {
                        $tableData['defaults'][] = array('statement' => '$this->' .
                            Utils::field2property($column->name) . ' = array();');
                    }
                    elseif($table->autoGenerateName == $column->name) {
                        $tableData['defaults'][] = array('statement' => '$this->' .
                            Utils::field2property($column->name) . ' = bin2hex(openssl_random_pseudo_bytes(16));');
                    }

                    $tableData['columns'][] = $columnData;
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

            $this->data['realms'][] = $data;
        }
    }

    protected function generateRealmInfoFolders() {
        if(!file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps')) {
            mkdir(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps');
        }
        if(!file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm')) {
            mkdir(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm');
        }
        if(!file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms')) {
            mkdir(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms');
        }

        foreach($this->realms as $realmName => $realmInfo) {
            if(!file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms/' . $realmName)) {
                mkdir(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms/' . $realmName);
            }
            if(!file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms/' . $realmName . '/classes')) {
                mkdir(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms/' . $realmName . '/classes');
            }
            if(!file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms/' . $realmName . '/validations')) {
                mkdir(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms/' . $realmName . '/validations');
            }
            if(!file_exists(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms/' . $realmName . '/services')) {
                mkdir(__DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms/' . $realmName . '/services');
            }
        }
    }

    protected function populateRealms() {
        $contents = file_get_contents(__DIR__ . "/../../../../../../app/config/ac_norm.yml");
        $ac_norm = yaml_parse($contents);

        $contents = file_get_contents(__DIR__ . "/../../../../../../app/config/ac_norm_"
            . $this->environment . ".yml");
        $ac_norm_env = yaml_parse($contents);

        foreach($ac_norm['realms'] as $realmName => $realmInfo) {
            $this->realms[$realmName] = $realmInfo;
            $this->realms[$realmName]['name'] = $realmName;
            $this->realms[$realmName]['namespace'] = $realmInfo['namespace'];
            $this->realms[$realmName]['dsInfo']
                = $ac_norm_env['datastores'][$this->realms[$realmName]['primary_datastore']];

            $this->realmNames[] = $realmName;
        }
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

        $filename = __DIR__ . '/../../../../../../app/cache/' . $this->environment . '/angrychimps/norm/realms/'
            . $schema->realm . '/validations/validations.yml';

        if(!file_exists($filename)) {
            touch($filename);
        }
        file_put_contents($filename, $yaml);
    }

    protected function createRealmFolders() {
        foreach($this->realmNames as $realmName) {
            if (!file_exists(__DIR__ . "/../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $realmName)) {
                mkdir(__DIR__ . "/../../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $realmName);
                mkdir(__DIR__ . "/../../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $realmName . '/services');
                mkdir(__DIR__ . "/../../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $realmName . '/yaml');
                mkdir(__DIR__ . "/../../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $realmName . '/yaml/classes');
            }
        }
    }

}