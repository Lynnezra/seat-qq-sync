<?php

namespace Lynnezra\Seat\QQSync\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Lynnezra\Seat\QQSync\Models\QQBinding;
use Lynnezra\Seat\QQSync\Models\QQGroup;
use Lynnezra\Seat\QQSync\Services\NapcatService;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Alliances\Alliance;

class CheckMemberEligibility implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $qqNumber;
    protected $groupId;
    protected $action; // 'join' 或 'periodic_check'

    /**
     * Create a new job instance.
     *
     * @param string $qqNumber
     * @param string $groupId
     * @param string $action
     */
    public function __construct($qqNumber, $groupId, $action = 'periodic_check')
    {
        $this->qqNumber = $qqNumber;
        $this->groupId = $groupId;
        $this->action = $action;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(NapcatService $napcatService)
    {
        try {
            Log::info("开始检查QQ用户资格", [
                'qq' => $this->qqNumber,
                'group' => $this->groupId,
                'action' => $this->action
            ]);

            // 获取QQ群配置
            $qqGroup = QQGroup::where('group_id', $this->groupId)
                ->where('is_active', true)
                ->first();

            if (!$qqGroup) {
                Log::warning("QQ群未配置或未激活", ['group_id' => $this->groupId]);
                return;
            }

            // 检查用户是否在群内
            $isInGroup = $napcatService->checkUserInGroup($this->qqNumber, $this->groupId);
            if (!$isInGroup && $this->action === 'periodic_check') {
                Log::info("用户不在群内，跳过检查", ['qq' => $this->qqNumber]);
                return;
            }

            // 检查QQ绑定
            $binding = QQBinding::where('qq_number', $this->qqNumber)
                ->whereNotNull('verified_at')
                ->first();

            if (!$binding) {
                $this->handleUnboundUser($napcatService, $qqGroup);
                return;
            }

            // 检查角色信息
            $character = CharacterInfo::find($binding->character_id);
            if (!$character) {
                Log::error("角色信息不存在", ['character_id' => $binding->character_id]);
                $this->handleInvalidUser($napcatService, $qqGroup, '角色信息无效');
                return;
            }

            // 检查军团/联盟资格
            if (!$this->checkCorporationAlliance($character, $qqGroup)) {
                $this->handleIneligibleUser($napcatService, $qqGroup, $character);
                return;
            }

            // 用户符合条件
            if ($this->action === 'join') {
                $this->handleEligibleUser($napcatService, $qqGroup);
            }

            Log::info("用户资格检查通过", [
                'qq' => $this->qqNumber,
                'character' => $character->name
            ]);

        } catch (\Exception $e) {
            Log::error("检查用户资格时发生错误", [
                'qq' => $this->qqNumber,
                'group' => $this->groupId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 处理未绑定QQ的用户
     */
    private function handleUnboundUser(NapcatService $napcatService, QQGroup $qqGroup)
    {
        Log::info("用户未绑定QQ", ['qq' => $this->qqNumber]);

        if ($this->action === 'join') {
            // 拒绝入群申请
            $napcatService->handleGroupRequest($this->groupId, $this->qqNumber, false, 
                config('qq-sync.messages.bind_required'));
        } else {
            // 踢出群聊
            $napcatService->kickGroupMember($this->groupId, $this->qqNumber);
            
            // 发送通知消息
            $message = str_replace(
                ['{qq}', '{reason}'],
                [$this->qqNumber, '未绑定QQ'],
                config('qq-sync.messages.kicked_notification')
            );
            $napcatService->sendGroupMessage($this->groupId, $message);
        }
    }

    /**
     * 处理不符合军团/联盟要求的用户
     */
    private function handleIneligibleUser(NapcatService $napcatService, QQGroup $qqGroup, CharacterInfo $character)
    {
        Log::info("用户不符合军团/联盟要求", [
            'qq' => $this->qqNumber,
            'character' => $character->name,
            'corporation_id' => $character->corporation_id
        ]);

        if ($this->action === 'join') {
            // 拒绝入群申请
            $napcatService->handleGroupRequest($this->groupId, $this->qqNumber, false,
                config('qq-sync.messages.corp_alliance_required'));
        } else {
            // 踢出群聊
            $napcatService->kickGroupMember($this->groupId, $this->qqNumber);
            
            // 发送通知消息
            $message = str_replace(
                ['{qq}', '{reason}'],
                [$this->qqNumber, '不符合军团/联盟要求'],
                config('qq-sync.messages.kicked_notification')
            );
            $napcatService->sendGroupMessage($this->groupId, $message);
        }
    }

    /**
     * 处理无效用户（角色信息错误等）
     */
    private function handleInvalidUser(NapcatService $napcatService, QQGroup $qqGroup, $reason)
    {
        Log::warning("用户信息无效", ['qq' => $this->qqNumber, 'reason' => $reason]);

        if ($this->action === 'join') {
            $napcatService->handleGroupRequest($this->groupId, $this->qqNumber, false, $reason);
        } else {
            $napcatService->kickGroupMember($this->groupId, $this->qqNumber);
            
            $message = str_replace(
                ['{qq}', '{reason}'],
                [$this->qqNumber, $reason],
                config('qq-sync.messages.kicked_notification')
            );
            $napcatService->sendGroupMessage($this->groupId, $message);
        }
    }

    /**
     * 处理符合条件的用户（入群申请）
     */
    private function handleEligibleUser(NapcatService $napcatService, QQGroup $qqGroup)
    {
        // 同意入群申请
        $napcatService->handleGroupRequest($this->groupId, $this->qqNumber, true);
        
        // 发送欢迎消息
        $napcatService->sendPrivateMessage($this->qqNumber, config('qq-sync.messages.welcome'));
        
        Log::info("用户入群申请已通过", ['qq' => $this->qqNumber]);
    }

    /**
     * 检查军团/联盟资格
     */
    private function checkCorporationAlliance(CharacterInfo $character, QQGroup $qqGroup)
    {
        // 如果没有设置军团/联盟限制，则通过
        if (empty($qqGroup->allowed_corporations) && empty($qqGroup->allowed_alliances)) {
            return true;
        }

        // 检查军团
        if (!empty($qqGroup->allowed_corporations)) {
            $allowedCorps = json_decode($qqGroup->allowed_corporations, true) ?: [];
            if (in_array($character->corporation_id, $allowedCorps)) {
                return true;
            }
        }

        // 检查联盟
        if (!empty($qqGroup->allowed_alliances) && $character->alliance_id) {
            $allowedAlliances = json_decode($qqGroup->allowed_alliances, true) ?: [];
            if (in_array($character->alliance_id, $allowedAlliances)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error("CheckMemberEligibility Job 执行失败", [
            'qq' => $this->qqNumber,
            'group' => $this->groupId,
            'action' => $this->action,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}