<?php

namespace App\Model;

class TeamMember extends Model
{
    protected static $table = 'team_members';

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * @param array $conditions
     * @return array|null
     */
    public function userList(array $conditions)
    {
        if (empty($conditions)) {
            return null;
        }

        $columns = array_keys($conditions);
        $firstWhere = array_shift($columns);
        $where = $firstWhere . ' = :' . $firstWhere . ' ';
        foreach ($columns as $column) {
            $where .= ' AND ' . static::$table . '.' . $column . ' = :' . $column . ' ';
        }

        $query = 'SELECT ' . static::$table . '.*, users.username  FROM ' . static::$table
            . ' LEFT JOIN users ON ' . static::$table . '.user_id = users.id'
            . ' WHERE ' . $where;
        $stmt = $this->db->prepare($query);
        if ($stmt->execute($conditions)) {
            $this->model = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $this->model;
        }
        return null;
    }

    /**
     * @param array $conditions
     * @return bool|string
     */
    public function wadeInTeam(array $conditions)
    {
        if (empty($conditions)) {
            return false;
        }
        $conditions['status'] = self::STATUS_INACTIVE;
        $conditions['role'] = null;

        return $this->insert($conditions);
    }

    /**
     * @return bool
     */
    public function dropBid()
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
     * @param int $memberId
     * @return mixed|null
     */
    public function getRoleInfo(int $memberId)
    {
        $query = 'SELECT ' . static::$table . '.*, roles.*  FROM ' . static::$table
            . ' LEFT JOIN roles ON ' . static::$table . '.role = roles.id'
            . ' WHERE user_id = :user_id';
        $stmt = $this->db->prepare($query);
        if ($stmt->execute(['user_id' => $memberId])) {
            $this->model = $stmt->fetchObject();
            return $this->model;
        }
        return null;
    }
}