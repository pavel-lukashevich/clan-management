<?php

function showAllPermissions()
{
    // <prefix from controller>.<action>-<permission inside action>
    return [
        'team.create',
        'team.drop',
        'team.index' ,
        'team.show',
        'team.showBids',
        'team.update',
        'team.update-title',
        'team.update-description',

        'team-member.bid',
        'team-member.dropBid',
        'team-member.add',
        'team-member.drop',
        'team-member.changeRole',

        'role.available'
    ];
}
