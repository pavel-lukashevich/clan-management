<?php

function getRoutesList()
{
    return [
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
}