<?php

return [
    'qq-sync' => [
        'name' => 'QQ同步',
        'icon' => 'fas fa-comments',
        'route_segment' => 'qq-sync',
        'permission' => 'qq-sync.view',
        'entries' => [
            [
                'name' => 'QQ绑定',
                'icon' => 'fas fa-link',
                'route' => 'qq-sync.binding',
                'permission' => 'qq-sync.bind'
            ],
            [
                'name' => 'QQ群管理',
                'icon' => 'fas fa-users',
                'route' => 'qq-sync.groups.index',
                'permission' => 'qq-sync.admin'
            ],
            [
                'name' => '机器人配置',
                'icon' => 'fas fa-robot',
                'route' => 'qq-sync.bot.config',
                'permission' => 'qq-sync.admin'
            ],
            [
                'name' => '手动绑定',
                'icon' => 'fas fa-user-plus',
                'route' => 'qq-sync.admin.bind',
                'permission' => 'qq-sync.admin'
            ],
            [
                'name' => '同步日志',
                'icon' => 'fas fa-history',
                'route' => 'qq-sync.logs',
                'permission' => 'qq-sync.view'
            ]
        ]
    ]
];