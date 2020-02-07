<?php

namespace App\Controller;

use App\Model\Role;
use App\Model\TeamMember;
use App\Services\ViewJson;

class RoleController extends Controller
{
    /**
     * @param string $action
     * @return bool
     */
    public function checkPermissions(string $action)
    {
        if (in_array($action, ['permissions'])) {
            return true;
        }
        if (in_array('role.' . $action, $this->teamRole->permission)) {
            return true;
        }
        return false;
    }

    /**
     * @return ViewJson
     */
    public function permissions()
    {
        $data = showAllPermissions();
        if (!empty($data)) {
            return new ViewJson($data);
        }
        return new ViewJson(['status' => false, 'messages' => 'permissions list is empty']);
    }

    /**
     * @return ViewJson
     */
    public function available()
    {
        $status = false;
        $messages = [];
        $request = $_REQUEST;
        try {
            $teamMembers = new TeamMember();
            if (
                empty($request)
                || empty($request['user_id'])
                || !$teamMembers->getByConditions(['team_id' => $this->teamRole->team_id, 'user_id' => $request['user_id'], 'status' => TeamMember::STATUS_ACTIVE])
            ) {
                return new ViewJson(['status' => $status, 'messages' => 'user not found']);
            }

            $roles = new Role();
            $leadingWeight = $this->teamRole->weight;
            $slaveRole = $roles->getByConditions(['id' => $teamMembers->getModel()->role]);
            $slaveWeight =  !empty($slaveRole) ? $slaveRole->weight : $roles->getLowest()->weight;

            $availableRoles = $roles->getAvailable($leadingWeight, $slaveWeight );

            if (!empty($availableRoles)) {
                return new ViewJson($availableRoles);
            } else {
                $messages[] = 'no suitable options';
            }
        } catch (\Exception $e) {
            $messages[] = $e->getMessage();
        }
        $messages = !empty($messages) ? $messages : 'successfully';
        return new ViewJson(['status' => $status, 'messages' => $messages]);
    }
}