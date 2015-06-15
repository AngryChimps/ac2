<?php


namespace AC\NormBundle\core\generator\generators;


use AC\NormBundle\core\datastore\AbstractDatastore;
use AC\NormBundle\core\exceptions\UnsupportedColumnType;
use AC\NormBundle\core\generator\types\Datastore;
use AC\NormBundle\core\generator\types\Enum;
use AC\NormBundle\core\generator\types\PrimaryKey;
use AC\NormBundle\core\generator\types\Schema;
use AC\NormBundle\core\generator\types\Table;
use AC\NormBundle\core\generator\types\Column;
use AC\NormBundle\core\generator\types\ForeignKey;
use AC\NormBundle\core\NormBaseCollection;
use AC\NormBundle\core\Utils;

class YamlGenerator extends AbstractGenerator {
    protected $isTest;
    protected $realm;
    protected $namespace;

    public function __construct($namespace, $isTest = false ) {
        $this->namespace = $namespace;
        $this->isTest = $isTest;
    }

    /**
     * @returns Schema
     */
    public function getSchema() {
        $schema = new Schema();
        $schema->namespace = $this->namespace;

        foreach($this->getTableNames() as $tableName) {
            $table = new Table();
            $table->name = $tableName;
            $tableData = $this->getTableData($table->name);
            if(isset($tableData['primary_keys'])) {
                $table->primaryKeyNames = $tableData['primary_keys'];
                $table->primaryKeyPropertyNames = [];
                foreach($table->primaryKeyNames as $pkName) {
                    $table->primaryKeyPropertyNames[] = Utils::field2property($pkName);
                }
            }

            $table->datastores = [];
            foreach($tableData['datastores'] as $datastore) {
                $ds = new Datastore();
                $ds->name = $datastore['name'];
                $ds->method = $datastore['method'];
                $ds->type = $datastore['type'];
                $table->datastores[] = $ds;

                if($ds->type === 'primary') {
                    $table->primaryDatastore = $ds;
                }
            }

            $ordinalPosition = 0;
            foreach($tableData['fields'] as $fieldData) {
                $column = new Column();
                $column->name = $fieldData['name'];
                $column->position = $ordinalPosition;
                $column->default = isset($fieldData['default']) ? $fieldData['default'] : null;

      //            $column->typeWithLength = $columnRow['COLUMN_TYPE'];

                if(strtolower($fieldData['type']) === 'enum') {
                    $column->type = 'int';
                    $enum = new Enum();
                    $enum->name = $fieldData['name'];
                    $enum->values = $fieldData['values'];
                    $table->enums[] = $enum;
                }
                else {
                    $column->type = $fieldData['type'];
                }

                if(isset($fieldData['length'])) {
                    $column->length = $fieldData['length'];
                }
                if(isset($fieldData['auto_increment']) && $fieldData['auto_increment'] == 'true') {
                    $table->autoIncrementName = $column->name;
                }
                if(isset($fieldData['auto_generate']) && $fieldData['auto_generate'] == 'true') {
                    $table->autoGenerateName = $column->name;
                }
                if(isset($fieldData['validations'])) {
                    $column->validations = $fieldData['validations'];
                }
                if(isset($fieldData['include_in_all'])) {
                    $column->includeInAll = $fieldData['include_in_all'];
                }
                else {
                    $column->includeInAll = true;
                }
                if(isset($fieldData['index_name'])) {
                    $column->indexName = $fieldData['index_name'];
                }
                else {
                    $column->indexName = null;
                }

                $table->columns[$column->name] = $column;

                $ordinalPosition++;
            }

            $schema->tables[$tableName] = $table;
        }

        return $schema;
    }

    protected function getTableNames() {
        $tables = array();

        if($this->isTest) {
            $handle = opendir(__DIR__ . "/../../../Tests/realms/NormTests/" . $this->realm . "/yaml/classes");
        }
        else {
            $handle = opendir(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/yaml/classes");
        }

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

    protected function getTableData($tableName) {
        if($this->isTest) {
            $contents = file_get_contents(__DIR__ . "/../../../Tests/realms/NormTests/" . $this->realm . "/yaml/classes/" . $tableName . '.yml');
        }
        else {
            $contents = file_get_contents(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/yaml/classes/" . $tableName . '.yml');
        }
        return yaml_parse($contents);
    }
}