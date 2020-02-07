<?php

namespace App\Controller;

use App\Model\Role;
use App\Model\Team;
use App\Model\TeamMember;
use App\Services\Db;
use App\Services\ViewJson;

class TeamController extends Controller
{
    private $messages = [];

    /**
     * @param string $action
     * @return bool
     */
    public function checkPermissions(string $action)
    {
        if (in_array('team.' . $action, $this->teamRole->permission)) {
            return true;
        }
        return false;
    }

    /**
     * @return ViewJson
     */
    public function create()
    {
        $request = $_REQUEST;
        $status = false;
        if ($this->validate($request)) {
            try {
                $db = Db::getPdo();
                $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $db->beginTransaction();

                $teamId = (new Team())->insert([
                    'title' => $request['title'],
                    'description' => $request['description'],
                    'user_id' => $this->user->id
                ]);
                $role = (new Role())->getLeader();
                $teamMembersId = (new TeamMember())->insert([
                    'user_id' => $this->user->id,
                    'team_id' => $teamId,
                    'role' => $role->id,
                    'status' => TeamMember::STATUS_ACTIVE
                ]);
                $status = $teamId && $teamMembersId;

                $db->commit();
            } catch (\Exception $e) {
                $db->rollBack();
                $this->messages[] = $e->getMessage();
            }
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
        try {
            $team = new Team();
            if (empty($team->getByConditions(['user_id' => $this->user->id]))) {
                return new ViewJson(['status' => $status, 'messages' => 'team not found']);
            }
            $status = $team->drop();
        } catch (\Exception $e) {
            $this->messages[] = $e->getMessage();
        }
        $messages = !empty($this->messages) ? $this->messages : 'successfully';
        return new ViewJson(['status' => $status, 'messages' => $messages]);
    }

    /**
     * @return ViewJson
     */
    public function index()
    {
        $data = (new Team())->getTeamInfo();
        if (!empty($data)) {
            return new ViewJson($data);
        }
        return new ViewJson(['status' => false, 'messages' => 'teams list is empty']);
    }

    /**
     * @return ViewJson
     */
    public function show()
    {
        $request = $_REQUEST;
        if (!empty($request)) {
            $data = (new TeamMember())->userList(['team_id' => $this->teamRole->team_id, 'status' => TeamMember::STATUS_ACTIVE]);
        }
        if (!empty($data)) {
            return new ViewJson($data);
        }
        return new ViewJson(['status' => false, 'messages' => 'teams list is empty']);
    }

    /**
     * @return ViewJson
     */
    public function showBids()
    {
        $request = $_REQUEST;
        if (!empty($request)) {
            $data = (new TeamMember())->userList(['team_id' => $this->teamRole->team_id, 'status' => TeamMember::STATUS_INACTIVE]);
        }
        if (!empty($data)) {
            return new ViewJson($data);
        }
        return new ViewJson(['status' => false, 'messages' => 'teams list is empty']);
    }

    /**
     * @return ViewJson
     */
    public function update()
    {
        $request = $_REQUEST;
        $status = false;
        $conditions = [];

        if ($this->checkPermissions('update-title')) {
            if (!empty($request['title'])) {
                $this->validateTitle($request['title']);
                $conditions['title'] = $request['title'];
            } else {
                $this->messages[] = 'title required';
            }
        }

        if ($this->checkPermissions('update-description')) {
            if (!empty($request['description'])) {
                $this->validateDescription($request['description']);
                $conditions['description'] = $request['description'];
            } else {
                $this->messages[] = 'description required';
            }
        }

        if (!empty($this->messages)) {
            return new ViewJson(['status' => $status, 'messages' => $this->messages]);
        }

        try {
            $team = new Team();
            $status = $team->update($conditions, ['id' => $this->teamRole->team_id]);
        } catch (\Exception $e) {
            $this->messages[] = $e->getMessage();
        }

        $messages = !empty($this->messages) ? $this->messages : 'successfully';
        return new ViewJson(['status' => $status, 'messages' => $messages]);
    }

    /**
     * @param array $request
     * @return bool
     */
    private function validate(array &$request)
    {
        $member = (new TeamMember())->getByConditions(['user_id' => $this->user->id]);
        if (!empty($member)) {
            $this->messages[] = 'you are in a group';
            return false;
        }
        if (!empty($request['title'])) {
            $this->validateTitle($request['title']);
            $this->validateUniqueTitle($request['title']);
        } else {
            $this->messages[] = 'title required';
        }
        if (!empty($request['description'])) {
            $this->validateDescription($request['description']);
        } else {
            $this->messages[] = 'description required';
        }
        if (empty($this->messages)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $title
     */
    private function validateTitle(string &$title)
    {
        $title = trim($title);
        if (empty($title)) {
            $this->messages[] = 'title required';
        }
        if (mb_strlen($title) > 12) {
            $this->messages[] = 'title - max length 12 letters';
        }
        if (!preg_match('~^\w+$~', $title)) {
            $this->messages[] = 'title should only consist of letters and numbers';
        }
    }

    /**
     * @param string $title
     */
    private function validateUniqueTitle(string &$title, int $teamId = null)
    {
        if (empty($this->messages)) {
            $team = (new Team())->getByConditions(['title' => $title]);
            if (!empty($team) && empty($teamId) && $team->id != $teamId) {
                $this->messages[] = 'title must be unique';
            }
        }
    }

    /**
     * @param string $description
     */
    private function validateDescription(string &$description)
    {
        $description = trim($description);
        if (empty($description)) {
            $this->messages[] = 'description required';
        }
        if (mb_strlen($description) > 30) {
            $this->messages[] = 'description - max length 30 letters';
        }
    }
}