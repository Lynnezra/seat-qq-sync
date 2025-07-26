<?php

namespace Lynnezra\Seat\QQSync\Models;

use Illuminate\Database\Eloquent\Model;

class QQBotConfig extends Model
{
    protected $table = 'Lynnezra_qq_bot_configs';
    
    protected $fillable = [
        'bot_qq',
        'napcat_api_url',
        'api_token',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    protected $hidden = [
        'api_token'
    ];
}