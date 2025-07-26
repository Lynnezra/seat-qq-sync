<?php

Route::group([
    'namespace' => 'Lynnezra\Seat\QQSync\Http\Controllers',
    'prefix' => 'qq-sync',
    'middleware' => ['web', 'auth']
], function () {
    
    // 用户QQ绑定相关路由
    Route::group(['prefix' => 'binding'], function () {
        Route::get('/', 'QQBindingController@index')->name('qq-sync.binding.index');
        Route::post('/bind', 'QQBindingController@bind')->name('qq-sync.binding.bind');
        Route::delete('/unbind/{id}', 'QQBindingController@unbind')->name('qq-sync.binding.unbind');
    });
    
    // 管理员功能路由
    Route::group(['middleware' => 'can:qq-sync.admin'], function () {
        Route::group(['prefix' => 'admin'], function () {
            // QQ群管理
            Route::resource('groups', 'QQGroupController');
            
            // 机器人配置
            Route::get('bot-config', 'QQBotController@index')->name('qq-sync.bot.index');
            Route::post('bot-config', 'QQBotController@store')->name('qq-sync.bot.store');
            
            // 手动绑定用户QQ
            Route::post('manual-bind', 'QQBindingController@manualBind')->name('qq-sync.admin.manual-bind');
        });
    });
    
    // API路由（供napcat调用）
    Route::group(['prefix' => 'api', 'middleware' => 'qq-sync.auth'], function () {
        Route::post('member-join', 'QQBotController@memberJoin');
        Route::post('verify-member', 'QQBotController@verifyMember');
    });
});