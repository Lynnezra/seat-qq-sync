<?php

return [
    'version' => '1.0.0',
    
    // 默认配置
    'default_check_interval' => 300, // 5分钟检查一次
    'max_retry_attempts' => 3,
    'api_timeout' => 30,
    
    // 消息模板
    'messages' => [
        'welcome' => '欢迎加入群聊！请确保您已在SeAT系统中绑定QQ号。',
        'bind_required' => '您需要先在SeAT系统中绑定QQ号才能留在群内。',
        'corp_alliance_required' => '您的角色不在指定的军团或联盟中，无法留在群内。',
        'kicked_notification' => '用户 {qq} 因未满足条件被移出群聊。原因：{reason}'
    ],
    
    // 权限配置
    'permissions' => [
        'qq-sync.admin' => 'QQ同步管理',
        'qq-sync.bind' => 'QQ绑定',
        'qq-sync.view' => 'QQ同步查看'
    ]
];