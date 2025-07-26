<?php

namespace Lynnezra\Seat\QQSync\Http\Controllers;

use Illuminate\Http\Request;
use Seat\Web\Http\Controllers\Controller;
use Lynnezra\Seat\QQSync\Models\QQBotConfig;
use Lynnezra\Seat\QQSync\Models\QQBinding;
use Lynnezra\Seat\QQSync\Models\QQGroup;
use Lynnezra\Seat\QQSync\Services\NapcatService;

class QQBotController extends Controller
{
    public function index()
    {
        $configs = QQBotConfig::all();
        return view('qq-sync::bot.index', compact('configs'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'bot_qq' => 'required|string|max:20',
            'napcat_api_url' => 'required|url',
            'api_token' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        // 如果设置为活跃，先将其他配置设为非活跃
        if ($request->is_active) {
            QQBotConfig::where('is_active', true)->update(['is_active' => false]);
        }
        
        QQBotConfig::create($request->all());
        
        return back()->with('success', '机器人配置已保存');
    }
    
    /**
     * 处理新成员入群事件
     */
    public function memberJoin(Request $request)
    {
        $groupId = $request->input('group_id');
        $userId = $request->input('user_id');
        $flag = $request->input('flag');
        
        // 检查是否为监控的群
        $group = QQGroup::where('group_id', $groupId)->where('is_active', true)->first();
        if (!$group) {
            return response()->json(['status' => 'ignored']);
        }
        
        // 检查用户是否绑定QQ
        $binding = QQBinding::where('qq_number', $userId)->first();
        if (!$binding) {
            // 拒绝入群
            app(NapcatService::class)->rejectGroupRequest($flag, '请先在SeAT系统中绑定QQ号');
            return response()->json(['status' => 'rejected', 'reason' => 'no_binding']);
        }
        
        // 检查角色是否符合要求
        if (!$group->isCharacterEligible($binding->character)) {
            app(NapcatService::class)->rejectGroupRequest($flag, '您的角色不在指定的军团或联盟中');
            return response()->json(['status' => 'rejected', 'reason' => 'not_eligible']);
        }
        
        // 同意入群
        app(NapcatService::class)->approveGroupRequest($flag);
        return response()->json(['status' => 'approved']);
    }
    
    /**
     * 验证群成员
     */
    public function verifyMember(Request $request)
    {
        $groupId = $request->input('group_id');
        $userId = $request->input('user_id');
        
        $group = QQGroup::where('group_id', $groupId)->where('is_active', true)->first();
        if (!$group) {
            return response()->json(['status' => 'ignored']);
        }
        
        $binding = QQBinding::where('qq_number', $userId)->first();
        if (!$binding || !$group->isCharacterEligible($binding->character)) {
            return response()->json(['status' => 'kick_required']);
        }
        
        return response()->json(['status' => 'valid']);
    }
}