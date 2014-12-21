<?php


namespace AC\NormBundle\core\generator\generators;


use AC\NormBundle\core\datastore\AbstractDatastore;
use AC\NormBundle\core\exceptions\UnsupportedColumnType;
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
    protected $namepsace;

    public function __construct($realm, $namespace, $isTest = false ) {
        $this->realm = $realm;
        $this->isTest = $isTest;
        $this->namepsace = $namespace;
    }

    /**
     * @returns Schema
     */
    public function getSchema() {
        $schema = new Schema();
        $schema->realm = $this->realm;
        $schema->namespace = $this->namepsace;

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

                $table->columns[$column->name] = $column;

                $ordinalPosition++;
            }

            $schema->tables[$tableName] = $table;
        }

        //Get foreign key info
        foreach($schema->tables as $table) {
            if(isset($table->data['foreign_keys'])) {
                foreach($table->data['foreign_keys'] as $fkName => $fkData) {
                    $key = new ForeignKey();
                    $key->name = $fkName;
                    $key->tableName = $table->name;
                    $key->columnName = $fkData['column_name'];
                    $key->referencedTableName = $fkData['referenced_table_name'];
                    $key->referencedColumnName = $fkData['referenced_column_name'];

                    $schema->foreignKeys[] = $key;
                    $schema->tables[$key->tableName]->foreignKeys[] = $key;
                    $schema->tables[$key->referencedTableName]->reverseForeignKeys[] = $key;
                }
            }
        }

        return $schema;
    }

    protected function getTableNames() {
        $tables = array();

        if($this->isTest) {
            $handle = opendir(__DIR__ . "/../../../Tests/realms/NormTests/" . $this->realm . "/yaml/classes");
        }
        else {
            $handle = opendir(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $this->realm . "/yaml/classes");
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
            $contents = file_get_contents(__DIR__ . "/../../../../../../../../src/AngryChimps/NormBundle/realms/Norm/" . $this->realm . "/yaml/classes/" . $tableName . '.yml');
        }
        return yaml_parse($contents);
    }
}