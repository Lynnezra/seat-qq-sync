<?php

namespace Lynnezra\Seat\QQSync\Http\Controllers;

use Illuminate\Http\Request;
use Seat\Web\Http\Controllers\Controller;
use Lynnezra\Seat\QQSync\Models\QQBinding;
use Seat\Web\Models\User;

class QQBindingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $bindings = QQBinding::where('user_id', $user->id)->get();
        
        return view('qq-sync::qq-binding', compact('bindings'));
    }
    
    public function bind(Request $request)
    {
        $request->validate([
            'qq_number' => 'required|string|max:20',
            'character_id' => 'required|exists:character_infos,character_id'
        ]);
        
        $user = auth()->user();
        
        // 检查QQ号是否已被绑定
        $existingBinding = QQBinding::where('qq_number', $request->qq_number)->first();
        if ($existingBinding) {
            return back()->withErrors(['qq_number' => 'This QQ number is already bound to another account.']);
        }
        
        // 检查用户是否有权限绑定该角色
        if (!$user->characters->contains('character_id', $request->character_id)) {
            return back()->withErrors(['character_id' => 'You do not have access to this character.']);
        }
        
        QQBinding::create([
            'user_id' => $user->id,
            'character_id' => $request->character_id,
            'qq_number' => $request->qq_number,
        ]);
        
        return back()->with('success', 'QQ number bound successfully!');
    }
    
    public function manualBind(Request $request)
    {
        // 管理员手动绑定功能
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'qq_number' => 'required|string|max:20',
            'character_id' => 'required|exists:character_infos,character_id'
        ]);
        
        QQBinding::updateOrCreate(
            ['user_id' => $request->user_id, 'character_id' => $request->character_id],
            ['qq_number' => $request->qq_number]
        );
        
        return back()->with('success', 'Manual binding completed!');
    }
}