<?php

namespace Lynnezra\Seat\QQSync\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArray;

class QQGroup extends Model
{
    protected $table = 'Lynnezra_qq_groups';
    
    protected $fillable = [
        'group_id',
        'group_name',
        'required_corporations',
        'required_alliances',
        'is_active'
    ];
    
    protected $casts = [
        'required_corporations' => AsArray::class,
        'required_alliances' => AsArray::class,
        'is_active' => 'boolean'
    ];
    
    public function isCharacterEligible($character): bool
    {
        // 检查军团要求
        if (!empty($this->required_corporations)) {
            if (!in_array($character->corporation_id, $this->required_corporations)) {
                return false;
            }
        }
        
        // 检查联盟要求
        if (!empty($this->required_alliances)) {
            if (!in_array($character->alliance_id, $this->required_alliances)) {
                return false;
            }
        }
        
        return true;
    }
}