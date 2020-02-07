<?php

namespace App\Model;

class Team extends Model
{
    protected static $table = 'teams';

    /**
     * @return array|null
     */
    public function getTeamInfo()
    {
        $query = 'SELECT ' . static::$table . '.*, users.username as teamlead, COUNT(team_members.id) as count  FROM ' . static::$table
            . ' LEFT JOIN users ON ' . static::$table . '.user_id = users.id '
            . ' LEFT JOIN team_members ON ' . static::$table . '.id = team_members.team_id GROUP BY ' . static::$table . '.id';
        $stmt = $this->db->prepare($query);
        if ($stmt->execute()) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return null;
    }
}