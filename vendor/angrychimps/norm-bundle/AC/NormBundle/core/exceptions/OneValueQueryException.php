<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/18/14
 * Time: 3:28 AM
 */

namespace AC\NormBundle\core\exceptions;


class OneValueQueryException  extends AbstractNormException {

    /**
     * @param string $db
     * @param string $sql
     * @param array $params
     * @param int $rowCount
     */
    public function __construct($db, $sql, $params, $rowCount) {
        parent::__construct('One value query exception; $db=' . $db
            . ' $sql=' . $sql . ' rowCount=' . $rowCount . ' params=' . print_r($params, true));
    }
} 