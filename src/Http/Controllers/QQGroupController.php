<?php

namespace Lynnezra\Seat\QQSync\Http\Controllers;

use Illuminate\Http\Request;
use Seat\Web\Http\Controllers\Controller;
use Lynnezra\Seat\QQSync\Models\QQGroup;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Alliances\Alliance;

class QQGroupController extends Controller
{
    public function index()
    {
        $groups = QQGroup::paginate(15);
        return view('qq-sync::groups.index', compact('groups'));
    }
    
    public function create()
    {
        $corporations = CorporationInfo::all();
        $alliances = Alliance::all();
        return view('qq-sync::groups.create', compact('corporations', 'alliances'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'required|string|max:20|unique:Lynnezra_qq_groups',
            'group_name' => 'nullable|string|max:100',
            'required_corporations' => 'nullable|array',
            'required_alliances' => 'nullable|array',
            'is_active' => 'boolean'
        ]);
        
        QQGroup::create($request->all());
        
        return redirect()->route('qq-sync.groups.index')
            ->with('success', 'QQ群配置已创建');
    }
    
    public function show(QQGroup $group)
    {
        return view('qq-sync::groups.show', compact('group'));
    }
    
    public function edit(QQGroup $group)
    {
        $corporations = CorporationInfo::all();
        $alliances = Alliance::all();
        return view('qq-sync::groups.edit', compact('group', 'corporations', 'alliances'));
    }
    
    public function update(Request $request, QQGroup $group)
    {
        $request->validate([
            'group_id' => 'required|string|max:20|unique:Lynnezra_qq_groups,group_id,' . $group->id,
            'group_name' => 'nullable|string|max:100',
            'required_corporations' => 'nullable|array',
            'required_alliances' => 'nullable|array',
            'is_active' => 'boolean'
        ]);
        
        $group->update($request->all());
        
        return redirect()->route('qq-sync.groups.index')
            ->with('success', 'QQ群配置已更新');
    }
    
    public function destroy(QQGroup $group)
    {
        $group->delete();
        
        return redirect()->route('qq-sync.groups.index')
            ->with('success', 'QQ群配置已删除');
    }
}