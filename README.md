# Create and management clan

1. Creating a clan (clan name (max 12 characters without special characters and spaces), description (max 30 characters), list of participants (id and name fields are enough to describe a member) / delete the clan.
2. Roles for clan members:
Clan leader: can edit the description of the clan, delete the clan, remove other members, raise or lower the rank of other members of the clan. The member who created the clan becomes the default clan leader.
Deputy: can edit the description of the clan, upgrade to deputy
A soldier has no privileges. Any new player who comes to the clan becomes a soldier.
3. Adding new members to the clan / removing members from the clan (only a member with the role of soldier can be removed from the clan).
4. Change the description of the clan.
5. Increase / decrease the roles of clan members.
6. Getting a list of clans and their members.

**Implementation Requirements:**

Data comes from outside via POST requests.
We use pure PHP without frameworks and third-party services and libraries.

* Use an object oriented approach
* The url of the site from which you want to upload images must be passed to the script as
 an argument to the call
* Implement the ability to connect plugins that change or expand the behavior of the script
* Allowed to use libraries for http-requests and work with HTML

### Commands to run

-  Clone this repository

```
https://github.com/pavel-lukashevich/clan-management.git

```
- cd to the dir with clan-management, follow

```
composer update
```
- create a MySQL database and fill in the data in "./config/database.php"
- to create tables and fill in the source data, execute the GET request at "http://\<your domain\>/run"


#Notification

* it is assumed that post method calls will be from authorized users
```
[
    'post' => [
        'team/create' => 'App\Controller\TeamController@create', // session_key, title, description
        'team/drop' => 'App\Controller\TeamController@drop', // session_key
        'team/index' => 'App\Controller\TeamController@index', // session_key
        'team/show' => 'App\Controller\TeamController@show', // session_key
        'team/show-bids' => 'App\Controller\TeamController@showBids', // session_key
        'team/update' => 'App\Controller\TeamController@update', // session_key, ?title, description

        'team-member/bid' => 'App\Controller\TeamMemberController@bid', // session_key, team_id
        'team-member/drop-bid' => 'App\Controller\TeamMemberController@dropBid', // session_key

        'team-member/add' => 'App\Controller\TeamMemberController@add', // session_key, user_id
        'team-member/drop' => 'App\Controller\TeamMemberController@drop', // session_key, user_id
        'team-member/change-role' => 'App\Controller\TeamMemberController@changeRole', // session_key, user_id, role_id

        'role/permission' => 'App\Controller\RoleController@permissions', // session_key
        'role/available' => 'App\Controller\RoleController@available', // session_key, user_id
    ],
    'get' => [
        'run' => 'App\Controller\Seeder@createBaseTable',
    ],
    '404' => [
        '404' => 'App\Controller\RunController@notFound',
    ]
];
```


# ( ͡° ͜ʖ ͡°)

