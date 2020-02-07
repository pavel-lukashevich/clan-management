<?php

namespace App\Controller;

use App\Model\Role;
use App\Model\TeamMember;
use App\Model\User;

class Controller
{
    public $user = null;
    public $teamRole = null;

    public function __construct()
    {
        if (!empty($_REQUEST['session_key'])) {
            $this->user = (new User())->getByConditions(['session_key' => $_REQUEST['session_key']]);
        }
        if (empty($this->user)) {
            header('Location: 404');
            die;
        }

        $this->teamRole = (new TeamMember())->getRoleInfo($this->user->id);
        if (empty($this->teamRole) || empty($this->teamRole->role)) {
            $this->teamRole = (new Role())->getGuest();
        }
        $this->teamRole->permission = json_decode($this->teamRole->permission);

        return $this;
    }
}