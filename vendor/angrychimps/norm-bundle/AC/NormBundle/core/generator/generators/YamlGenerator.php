<?php


namespace AC\NormBundle\core\generator\generators;

use AC\NormBundle\core\generator\types\AbstractEntityOrSubclass;
use AC\NormBundle\core\generator\types\Datastore;
use AC\NormBundle\core\generator\types\Enum;
use AC\NormBundle\core\generator\types\Schema;
use AC\NormBundle\core\generator\types\Subclass;
use AC\NormBundle\core\generator\types\Entity;
use AC\NormBundle\core\generator\types\Field;
use AC\NormBundle\core\generator\types\EntityDatastore;

class YamlGenerator extends AbstractGenerator {
    protected $namespace;
    protected $datastores;
    protected $defaults;

    public function __construct($namespace, $datastores, $defaults ) {
        $this->namespace = $namespace;
        $this->datastores = $datastores;
        $this->defaults = $defaults;
    }

    /**
     * @returns Schema
     * @throws \Exception
     */
    public function getSchema() {
        $schema = new Schema();
        $schema->namespace = $this->namespace;

        //Create Datastores
        foreach($this->datastores as $datastoreName => $datastore) {
            $ds = new Datastore();
            $ds->name = $datastoreName;
            $ds->driver = $datastore['driver'];
            $ds->prefix = isset($datastore['prefix']) ? $datastore['prefix'] : null;
            $ds->indexName = isset($datastore['indexName']) ? $datastore['indexName'] : null;
            $ds->shards = isset($datastore['shards']) ? $datastore['shards'] : null;
            $ds->replicas = isset($datastore['replicas']) ? $datastore['replicas'] : null;
            $ds->defaultAnalyzer = isset($datastore['defaultAnalyzer']) ? $datastore['defaultAnalyzer'] : null;

            $ds->isRiak2 = false;
            $ds->isElasticsearch = false;
            switch($ds->driver) {
                case 'riak2':
                    $ds->isRiak2 = true;
                    break;
                case 'elasticsearch':
                    $ds->isElasticsearch = true;
                    break;
                default:
                    throw new \Exception('Unsupported driver type');
            }
            $schema->datastores[$ds->name] = $ds;
        }

        foreach($this->getTableNames() as $tableName) {
            $schema->entities[$tableName] = $this->processEntity($tableName, $schema);
        }

        foreach($this->getSubclassNames() as $subclassName) {
            $schema->subclasses[$subclassName] = $this->processSubclass($subclassName);
        }

        return $schema;
    }

    protected function processEntity($entityName, Schema $schema) {
        $entity = new Entity();
        $entity->name = $entityName;
        $entityData = $this->getEntityData($entity->name);
        if(isset($entityData['primary_keys'])) {
            $entity->primaryKeyNames = $entityData['primary_keys'];
        }

        $entity->datastores = [];
        foreach($entityData['datastores'] as $datastore) {
            $ds = new EntityDatastore();
            $ds->name = $datastore['name'];
            $ds->method = $datastore['method'];
            $ds->type = $datastore['type'];
            $ds->datastore = $schema->datastores[$ds->name];

            $entity->datastores[] = $ds;

            if($ds->type === 'primary') {
                $entity->primaryDatastore = $ds;
            }
        }

        foreach($entityData['fields'] as $fieldData) {
            $entity->fields[$fieldData['name']] = $this->processField($entity, $fieldData);
        }

        $entity->apiPublicFields = isset($entityData['api_public_fields']) ? $entityData['api_public_fields'] : [];
        $entity->apiPrivateFields = isset($entityData['api_private_fields']) ? $entityData['api_private_fields'] : [];
        $entity->apiHiddenButSettableFields = isset($entityData['api_hidden_but_settable_fields']) ? $entityData['api_hidden_but_settable_fields'] : [];

        return $entity;
    }

    protected function processSubclass($subclassName) {
        $subclass = new Subclass();
        $subclass->name = $subclassName;
        $subclassData = $this->getSubclassData($subclass->name);

        foreach($subclassData['fields'] as $fieldData) {
            $subclass->fields[$fieldData['name']] = $this->processField($subclass, $fieldData);
        }

        return $subclass;
    }

    protected function processField(AbstractEntityOrSubclass $entityOrSubclass, array $fieldData) {
        $field = new Field();
        $field->name = $fieldData['name'];
        $field->default = isset($fieldData['default']) ? $fieldData['default'] : null;

        if(strtolower($fieldData['type']) === 'enum') {
            $field->type = 'int';
            $enum = new Enum();
            $enum->name = $fieldData['name'];
            $enum->values = $fieldData['values'];
            $entityOrSubclass->enums[] = $enum;
        }
        else {
            $field->type = $fieldData['type'];
        }

        if(isset($fieldData['validations'])) {
            $field->validations = $fieldData['validations'];
        }

        if($entityOrSubclass instanceof Entity) {
            if(isset($fieldData['auto_increment']) && $fieldData['auto_increment'] == 'true') {
                $entityOrSubclass->autoIncrementName = $field->name;
            }
            if(isset($fieldData['auto_generate']) && $fieldData['auto_generate'] == 'true') {
                $entityOrSubclass->autoGenerateName = $field->name;
            }
            if(isset($fieldData['include_in_all'])) {
                $field->includeInAll = $fieldData['include_in_all'];
            }
            else {
                $field->includeInAll = true;
            }
            if(isset($fieldData['index_name'])) {
                $field->indexName = $fieldData['index_name'];
            }
            else {
                $field->indexName = null;
            }

            //For Riak
            if(isset($fieldData['riak2']['indexed'])) {
                $field->riakIndexed = $fieldData['riak2']['indexed'];
            }
            else {
                $field->riakIndexed = $this->defaults['riak2']['indexed'];
            }

            //For Elasticsearch
            if(isset($fieldData['elasticsearch']['indexed'])) {
                $field->elasticsearchIndexed = $fieldData['elasticsearch']['indexed'];
            }
            else {
                $field->elasticsearchIndexed = $this->defaults['elasticsearch']['indexed'];
            }
        }

        return $field;
    }

    protected function getTableNames() {
        $tables = array();

        $handle = opendir(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/yaml/entities");

        while (false !== ($entry = readdir($handle))) {
            //Ignore . and .. entries
            if($entry == '.' || $entry == '..') {
                continue;
            }

            $parts = explode('.', $entry);
            $tables[] = $parts[0];
        }

        return $tables;
    }

    protected function getEntityData($entityName) {
        $contents = file_get_contents(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/yaml/entities/" . $entityName . '.yml');
        return yaml_parse($contents);
    }

    protected function getSubclassNames() {
        $subclasses = array();

        $handle = opendir(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/yaml/subclasses");

        while (false !== ($entry = readdir($handle))) {
            //Ignore . and .. entries
            if($entry == '.' || $entry == '..') {
                continue;
            }

            $parts = explode('.', $entry);
            $subclasses[] = $parts[0];
        }

        return $subclasses;
    }

    protected function getSubclassData($subclassName) {
        $contents = file_get_contents(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/yaml/subclasses/" . $subclassName . '.yml');
        return yaml_parse($contents);
    }


}