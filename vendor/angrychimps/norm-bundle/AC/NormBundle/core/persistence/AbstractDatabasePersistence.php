<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/18/14
 * Time: 1:23 PM
 */

namespace AC\NormBundle\core\persistence;


abstract class AbstractDatabasePersistence extends AbstractPersistence {
    /**
     * @param $db
     * @param $sql
     * @param array $params
     * @return PDOStatement
     */
    public static function query($db, $sql, $params = array()) {
        $dbh =  self::$db_handles[$db];

        if(count($params) == 0) {
            $stmt = $dbh->query($sql);
        }
        else {
            $stmt =$dbh->prepare($sql);
            $stmt->execute($params);
        }

        return $stmt;
    }

    public static function queryOneValue($db, $sql, $params = array()) {
        $stmt = self::query($db, $sql, $params);
        $result = $stmt->fetchAll(\PDO::FETCH_NUM);

        if(count($result) != 1) {
            throw new OneValueQueryException($db, $sql, $params, $result->rowCount());
        }

        return $result[0][0];
    }


    public static function queryOneColumn($db, $sql, $params = array()) {
        $return = array();

        $stmt = self::query($db, $sql, $params);

        while($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $return[] = $row[0];
        }

        return $return;
    }
} 