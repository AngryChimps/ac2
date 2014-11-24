<?php


namespace AC\NormBundle\core\generator\generators;


use AC\NormBundle\core\datastore\AbstractDatastore;
use AC\NormBundle\core\exceptions\UnsupportedColumnType;
use AC\NormBundle\core\generator\types\PrimaryKey;
use AC\NormBundle\core\generator\types\Schema;
use AC\NormBundle\core\generator\types\Table;
use AC\NormBundle\core\generator\types\Column;
use AC\NormBundle\core\generator\types\ForeignKey;

class MysqlGenerator extends AbstractGenerator {
    /** @var  AbstractDatastore */
    private $datastore;

    public function __construct(AbstractDatastore $datastore) {
        $this->datastore = $datastore;
    }

    /**
     * @returns Schema
     */
    public function getSchema() {
        $schema = new Schema();

        foreach($this->getTableData() as $row) {
            $table = new Table();
            $table->name = $row['TABLE_NAME'];
            $table->comment = $row['TABLE_COMMENT'];
            $table->primaryKeyNames = $this->getPrimaryKeyFieldNames($table->name);

            foreach($this->getColumnData($table->name) as $columnRow) {
                $column = new Column();
                $column->name = $columnRow['COLUMN_NAME'];
                $column->position = $columnRow['ORDINAL_POSITION'];
                $column->default = $columnRow['COLUMN_DEFAULT'];
                $column->typeWithLength = $columnRow['COLUMN_TYPE'];
                $column->type = $this->getPhpFieldType($table->name, $column->name, $columnRow['DATA_TYPE'],
                                                       $column->typeWithLength);
                $column->length = $columnRow['CHARACTER_MAXIMUM_LENGTH'];

                if($columnRow['EXTRA'] === 'auto_increment') {
                    $table->autoIncrementName = $column->name;
                }
                if($columnRow['COLUMN_KEY'] === 'PRI') {
                    $table->primaryKeyNames[] = $column->name;
                }

                $table->columns[$column->name] = $column;
            }

            $schema->tables[$row['TABLE_NAME']] = $table;
        }

        foreach($this->getForeignKeyData() as $row) {
            $key = new ForeignKey();
            $key->name = $row['CONSTRAINT_NAME'];
            $key->tableName = $row['TABLE_NAME'];
            $key->columnName = $row['COLUMN_NAME'];
            $key->referencedTableName = $row['REFERENCED_TABLE_NAME'];
            $key->referencedColumnName = $row['REFERENCED_COLUMN_NAME'];

            $schema->foreignKeys[] = $key;
            $schema->tables[$key->tableName]->foreignKeys[] = $key;
            $schema->tables[$key->referencedTableName]->reverseForeignKeys[] = $key;
        }
print_r($schema);
        return $schema;
    }

    protected  function getTableData() {
        $sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :schema";
        $params = array(':schema' => $this->datastore->getDbName());
        return $this->datastore->query($sql, $params);
    }

    protected function getColumnData($tableName) {
        $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :tableName
                ORDER BY ORDINAL_POSITION";
        $params = array(':schema' => $this->datastore->getDbName(), ':tableName' => $tableName);
        return $this->datastore->query($sql, $params);
    }

    protected function getPrimaryKeyFieldNames($tableName) {
        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = :schema AND
                TABLE_NAME  = :tableName and CONSTRAINT_NAME = 'PRIMARY' ORDER BY TABLE_NAME, ORDINAL_POSITION";
        $params = array(':schema' => $this->datastore->getDbName(), ':tableName' => $tableName);
        return $this->datastore->queryOneColumn($sql, $params);
    }

    protected function getForeignKeyData() {
        $sql = "SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = :schema AND
                REFERENCED_TABLE_NAME IS NOT NULL ORDER BY TABLE_NAME, COLUMN_NAME";
        $params = array(':schema' => $this->datastore->getDbName());
        return $this->datastore->query($sql, $params);
    }

    protected function getPhpFieldType($tableName, $columnName, $dbType, $typeWithLength) {
        switch($dbType) {
            //Char types
            case 'char':
            case 'varchar':
            case 'tinytext':
            case 'text':
            case 'mediumtext':
            case 'longtext':
                return 'string';

            //Boolean is considered a tinyint(1) in mysql; workbench handles that automatically
            case 'tinyint':
                if($typeWithLength === 'tinyint(1)') {
                    return 'bool';
                }
                return 'int';

            //Integer Types
            case 'smallint':
            case 'int':
            case 'mediumint':
            case 'bigint':
                return 'int';

            //Float types
            case 'decimal':
            case 'numeric':
            case 'float':
            case 'double':
                return 'float';

            //Date types
            case 'timestamp':
            case 'datetime':
            case 'date':
                return 'DateTime';

            case 'binary':
            case 'varbinary':
            case 'enum':
            case 'set':
            case 'bit':
            case 'time':
            case 'year':
            default:
                throw new UnsupportedColumnType($tableName, $columnName, $dbType);
        }
    }


}