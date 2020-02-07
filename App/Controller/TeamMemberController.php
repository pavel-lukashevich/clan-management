<?php

namespace App\Controller;

use App\Model\Role;
use App\Model\Team;
use App\Model\TeamMember;
use App\Services\ViewJson;

class TeamMemberController extends Controller
{
    private $messages = [];

    /**
     * @param string $action
     * @return bool
     */
    public function checkPermissions(string $action)
    {
        if (in_array('team-member.' . $action, $this->teamRole->permission)) {
            return true;
        }
        return false;
    }

    /**
     * @return ViewJson
     */
    public function bid()
    {
        $status = false;
        $request = $_REQUEST;
        try {
            $team = new Team();
            if (empty($request)
                || empty($request['team_id'])
                || empty($team->getByConditions(['id' => $request['team_id']]))
            ) {
                return new ViewJson(['status' => $status, 'messages' => 'team not found']);
            }

            $teamMembers = new TeamMember();
            if (!empty($teamMembers->getByConditions(['user_id' => $this->user->id]))
            ) {
                return new ViewJson(['status' => $status, 'messages' => 'only one team can be selected']);
            }

            $conditions = [
                'team_id' => $team->getModel()->id,
                'user_id' => $this->user->id
            ];

            $status = (bool) $teamMembers->wadeInTeam($conditions);
        } catch (\Exception $e) {
            $this->messages[] = $e->getMessage();
        }
        $messages = !empty($this->messages) ? $this->messages : 'successfully';
        return new ViewJson(['status' => $status, 'messages' => $messages]);
    }

    /**
     * @return ViewJson
     */
    public function dropBid()
    {
        $status = false;
        try {
            $teamMembers = new TeamMember();
            $conditions = ['user_id' => $this->user->id, 'status' => TeamMember::STATUS_INACTIVE];
            if (empty($teamMembers->getByConditions($conditions))) {
                return new ViewJson(['status' => $status, 'messages' => 'bid not found']);
            }

            $status = $teamMembers->dropBid();
        } catch (\Exception $e) {
            $this->messages[] = $e->getMessage();
        }
        $messages = !empty($this->messages) ? $this->messages : 'successfully';
        return new ViewJson(['status' => $status, 'messages' => $messages]);
    }

    /**
     * @return ViewJson
     */
    public function add()
    {
        $status = false;
        $request = $_REQUEST;
        try {
            $teamMembers = new TeamMember();
            if (
                empty($request)
                || empty($request['user_id'])
                || !$teamMembers->getByConditions(['team_id' => $this->teamRole->team_id, 'user_id' => $request['user_id'], 'status' => TeamMember::STATUS_INACTIVE])
            ) {
                return new ViewJson(['status' => $status, 'messages' => 'recruit not found']);
            }

            $lowestRole = (new Role())->getLowest();
            $conditions = ['role' => $lowestRole->id, 'status' => TeamMember::STATUS_ACTIVE];
            $whereConditions = ['id' => $teamMembers->getModel()->id];

            $status = $teamMembers->update($conditions, $whereConditions);
        } catch (\Exception $e) {
            $this->messages[] = $e->getMessage();
        }
        $messages = !empty($this->messages) ? $this->messages : 'successfully';
        return new ViewJson(['status' => $status, 'messages' => $messages]);
    }

    /**
     * @return ViewJson
     */
    public function drop()
    {
        $status = false;
        $request = $_REQUEST;
        try {
            $teamMembers = new TeamMember();
            if (
                empty($request)
                || empty($request['user_id'])
                || !$teamMembers->getByConditions(['team_id' => $this->teamRole->team_id, 'user_id' => $request['user_id']])
            ) {
                return new ViewJson(['status' => $status, 'messages' => 'recruit not found']);
            }

            $lowestRole = (new Role())->getLowest();
            $teamMembersModel = $teamMembers->getModel();
            if ($lowestRole->id == $teamMembersModel->role) {
                $status = $teamMembers->drop();
            } else {
                $this->messages[] = 'this cannot be deleted, first you need to lower role';
            }
        } catch (\Exception $e) {
            $this->messages[] = $e->getMessage();
        }
        $messages = !empty($this->messages) ? $this->messages : 'successfully';
        return new ViewJson(['status' => $status, 'messages' => $messages]);
    }

    /**
     * @return ViewJson
     */
    public function changeRole()
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
            $slaveUser = $teamMembers->getModel();
            $slaveRole = $roles->getByConditions(['id' => $slaveUser->role]);
            $slaveWeight =  $slaveRole->weight;
            if ($leadingWeight > $slaveWeight) {
                $slaveWeight = $roles->getLowest()->weight;
            }

            $availableRoles = $roles->getAvailable($leadingWeight, $slaveWeight );
            if (
                !empty($availableRoles)
                && !empty($request)
                && !empty($request['role_id'])
                && array_key_exists($request['role_id'], $availableRoles)
            ) {
                $status = $teamMembers->update(['role' => $request['role_id']], ['id' => $slaveUser->id]);
            } else {
                $messages[] = 'you cannot apply this role';
            }
        } catch (\Exception $e) {
            $messages[] = $e->getMessage();
        }
        $messages = !empty($messages) ? $messages : 'successfully';
        return new ViewJson(['status' => $status, 'messages' => $messages]);
    }
}