<?php

namespace SALESmanago\Model;

use Exception;
use PDO;

/**
 * @abstract
 */
abstract class AbstractModel
{
    /**
     * @var object
     */
    protected $pdo;

    /**
     * @param array $config
     * @return void
     */
    public function __construct($config)
    {
        try {
            $this->pdo = new PDO(
                $config['connection'],
                $config['user'],
                $config['pass']
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            echo 'The connect can not create: ' . $e->getMessage();
        }
    }

    /**
     * @param string $from Table
     * @param string $select
     * @param string $where Condition to query
     * @param <type> $order Order ($record ASC/DESC)
     * @param integer $limit LIMIT
     * @return array $data
     */
    public function select($from, $select = '*', $where = NULL, $order = NULL, $limit = NULL)
    {
        $query = 'SELECT ' . $select . ' FROM ' . $from;
        if ($where != NULL)
            $query = $query . ' WHERE ' . $where;
        if ($order != NULL)
            $query = $query . ' ORDER BY ' . $order;
        if ($limit != NULL)
            $query = $query . ' LIMIT ' . $limit;

        $select = $this->pdo->query($query, PDO::FETCH_ASSOC);

        $data = [];
        foreach ($select as $row) {
            $data[] = $row;
        }
        $select->closeCursor();

        return $data;
    }
}
