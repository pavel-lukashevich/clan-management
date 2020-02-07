<?php

namespace App\Model;

use App\Services\Db;

class Model
{
    protected static $table = '';
    protected $model = null;
    protected $db = null;

    protected static function getTableName()
    {
        return static::$table;
    }

    public function __construct()
    {
        $this->db = Db::getPdo();
    }

    /**
     * @return array|null
     */
    public function getAll()
    {
        $stmt = $this->db->query('SELECT * FROM ' . static::$table);
        if ($stmt->execute()) {
            return $stmt->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
        }
        return null;
    }

    /**
     * @param array $conditions
     * @return mixed|null
     */
    public function getByConditions(array $conditions)
    {
        if (empty($conditions)) {
            return null;
        }

        $columns = array_keys($conditions);
        $firstWhere = array_shift($columns);
        $where = $firstWhere . ' = :' . $firstWhere;
        foreach ($columns as $column) {
            $where .= ' AND ' . $column . ' = :' . $column . ' ';
        }

        $stmt = $this->db->prepare('SELECT * FROM ' . static::$table . ' WHERE ' . $where);
        if ($stmt->execute($conditions)) {
            $this->model = $stmt->fetchObject();
            return $this->model;
        }
        return null;
    }

    /**
     * @param array $conditions
     * @return bool|string
     */
    public function insert(array $conditions)
    {
        if (empty($conditions)) {
            return false;
        }

        $columns = array_keys($conditions);

        $query = 'INSERT INTO  ' . static::$table . ' (' . implode(', ', $columns) . ') VALUE (:' . implode(', :', $columns) . ')';
        $stmt = $this->db->prepare($query);
        if ($stmt->execute($conditions)) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * @param array $conditions
     * @param array $whereConditions
     * @return bool
     */
    public function update(array $conditions, array $whereConditions)
    {
        if (empty($conditions)) {
            return false;
        }

        $columns = array_keys($conditions);
        $firstWhere = array_shift($columns);
        $condition = $firstWhere . ' = :new_' . $firstWhere;
        foreach ($columns as $column) {
            $condition .= ', ' . $column . ' = :new_' . $column . ' ';
        }

        $whereColumns = array_keys($whereConditions);
        $firstWhere = array_shift($whereColumns);
        $where = $firstWhere . ' = :where_' . $firstWhere;
        foreach ($whereColumns as $whereColumn) {
            $where .= ' AND ' . $whereColumn . ' = :where_' . $whereColumn . ' ';
        }

        $stmt = $this->db->prepare('UPDATE ' . static::$table . ' SET ' . $condition . ' WHERE ' . $where);
        $conditions = array_combine(preg_replace('/^/', 'new_', array_keys($conditions), 1), $conditions);
        $whereConditions = array_combine(preg_replace('/^/', 'where_', array_keys($whereConditions), 1), $whereConditions);

        return $stmt->execute(array_merge($conditions, $whereConditions));
    }

    /**
     * @return bool
     */
    public function drop()
    {
        if (empty($this->model)) {
            return false;
        }

        $query = 'DELETE FROM  ' . static::$table . ' WHERE id = :id';
        $stmt = $this->db->prepare($query);
        if ($stmt->execute(['id' => $this->model->id])) {
            return true;
        }
        return false;
    }

    /**
     * @return object|null
     */
    public function getModel()
    {
        return $this->model;
    }


}