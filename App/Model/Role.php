<?php

namespace App\Model;

class Role extends Model
{
    protected static $table = 'roles';

    /**
     * @return mixed|null
     */
    public function getLeader()
    {
        $query = 'SELECT * FROM ' . static::$table . ' WHERE weight = (SELECT MAX(weight) FROM ' . static::$table . ')';
        $stmt = $this->db->query($query);
        if ($stmt->execute()) {
            $this->model = $stmt->fetchObject();
            return $this->model;
        }
        return null;
    }

    /**
     * @return mixed|null
     */
    public function getLowest()
    {
        $query = 'SELECT * FROM ' . static::$table . ' WHERE weight = (SELECT MIN(weight) FROM ' . static::$table . ')';
        $stmt = $this->db->query($query);
        if ($stmt->execute()) {
            $this->model = $stmt->fetchObject();
            return $this->model;
        }
        return null;
    }

    /**
     * @return mixed|null
     */
    public function getGuest()
    {
        $query = 'SELECT * FROM ' . static::$table . ' WHERE weight IS NULL';
        $stmt = $this->db->query($query);
        if ($stmt->execute()) {
            $this->model = $stmt->fetchObject();
            return $this->model;
        }
        return null;
    }

    public function getAvailable(int $leadingWeight, int $slaveWeight)
    {
        $query = 'SELECT id, title, weight FROM ' . static::$table . ' WHERE weight <= :leadingWeight AND weight >= :slaveWeight ORDER BY weight ASC';
        $stmt = $this->db->prepare($query);
        if ($stmt->execute(['leadingWeight' => $leadingWeight, 'slaveWeight' => $slaveWeight])) {
            return $stmt->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
        }
        return null;
    }


}