<?php

namespace Lynnezra\Seat\QQSync\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Lynnezra\Seat\QQSync\Models\QQGroup;
use Lynnezra\Seat\QQSync\Models\QQBinding;
use Lynnezra\Seat\QQSync\Services\NapcatService;

class SyncQQMembers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle()
    {
        $napcatService = app(NapcatService::class);
        $activeGroups = QQGroup::where('is_active', true)->get();
        
        foreach ($activeGroups as $group) {
            $this->syncGroupMembers($group, $napcatService);
        }
    }
    
    private function syncGroupMembers(QQGroup $group, NapcatService $napcatService)
    {
        // 获取群成员列表
        $groupMembers = $napcatService->getGroupMembers($group->group_id);
        
        foreach ($groupMembers as $member) {
            $this->checkMemberEligibility($group, $member['user_id'], $napcatService);
        }
    }
    
    private function checkMemberEligibility(QQGroup $group, string $qqNumber, NapcatService $napcatService)
    {
        // 检查QQ是否绑定
        $binding = QQBinding::where('qq_number', $qqNumber)->first();
        
        if (!$binding) {
            // 未绑定，踢出群聊
            $napcatService->kickGroupMember($group->group_id, $qqNumber);
            return;
        }
        
        // 检查角色是否在指定军团/联盟
        $character = $binding->character;
        if (!$this->isCharacterEligible($character, $group)) {
            // 不符合条件，踢出群聊
            $napcatService->kickGroupMember($group->group_id, $qqNumber);
        }
    }
    
    private function isCharacterEligible($character, QQGroup $group): bool
    {
        $requiredCorps = json_decode($group->required_corporations, true) ?? [];
        $requiredAlliances = json_decode($group->required_alliances, true) ?? [];
        
        // 检查军团
        if (!empty($requiredCorps) && !in_array($character->corporation_id, $requiredCorps)) {
            return false;
        }
        
        // 检查联盟
        if (!empty($requiredAlliances) && !in_array($character->alliance_id, $requiredAlliances)) {
            return false;
        }
        
        return true;
    }
}