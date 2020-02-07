<?php

namespace App\Controller;

use App\Services\Db;

class Seeder
{
    public function createBaseTable()
    {
        $db = Db::getPdo();

        $dbUsers = $db->query('SELECT * FROM `users`');
        if (empty($dbUsers)) {
            $users = 'CREATE TABLE `users` (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(30) UNIQUE NOT NULL,
            session_key VARCHAR(50) NOT NULL
            )';
            $db->query($users);

            $insert = 'INSERT INTO `users` (username, session_key) VALUES (?, ?)';
            for ($i=1; $i <= 10; $i++) {
                $db->prepare($insert)->execute(['user_' . $i, 'key_' . $i]);
            }
        }

        $dbRoles = $db->query('SELECT * FROM `roles`');
        if (empty($dbRoles)) {
            $roles = 'CREATE TABLE `roles` (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(30) UNIQUE NOT NULL,
            weight INT(11),
            permission TEXT NOT NULL
            )';
            $db->query($roles);

            $insert = 'INSERT INTO `roles` (title, weight, permission) VALUES (?, ?, ?)';

            $stmt = $db->prepare($insert);
            $stmt->execute(['TeamLead', 100, '["team.drop","team.index","team.show","team.update","team.update-title","team.update-description","team-member.add","team-member.drop","team-member.changeRole","role.available"]']);
            $stmt->execute(['Vice', 50, '["team.index","team.show","team.update","team.update-description","team-member.add","team-member.changeRole","role.available"]']);
            $stmt->execute(['Soldier', 10, '["team.index","team.show"]']);
            $stmt->execute(['Guest', null, '["team.create","team.index","team-member.bid","team-member.dropBid"]']);
        }

        $dbTeams = $db->query('SELECT * FROM `teams`');
        if (empty($dbTeams)) {
            $team = 'CREATE TABLE `teams` (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(12) UNIQUE NOT NULL,
            description VARCHAR(30) NOT NULL,
            user_id INT(11),
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            )';
            $db->query($team);
        }

        $dbTeamMember = $db->query('SELECT * FROM `team_members`');
        if (empty($dbTeamMember)) {
            $teamMembers = 'CREATE TABLE `team_members` (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11),
            team_id INT(11),
            role INT(11),
            status INT(11),
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
            FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE CASCADE
            )';
            $db->query($teamMembers);
        }

        die('Tables created successfully');
    }
}