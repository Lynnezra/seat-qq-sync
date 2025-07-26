<?php

namespace Lynnezra\Seat\QQSync\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Seat\Web\Models\User;
use Seat\Eveapi\Models\Character\CharacterInfo;

class QQBinding extends Model
{
    protected $table = 'Lynnezra_qq_bindings';
    
    protected $fillable = [
        'user_id',
        'character_id', 
        'qq_number',
        'verified_at'
    ];
    
    protected $casts = [
        'verified_at' => 'datetime'
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function character(): BelongsTo
    {
        return $this->belongsTo(CharacterInfo::class, 'character_id', 'character_id');
    }
    
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }
    
    public function markAsVerified(): void
    {
        $this->update(['verified_at' => now()]);
    }
}