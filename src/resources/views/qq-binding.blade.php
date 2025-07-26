@extends('web::layouts.grids.12')

@section('title', 'QQ绑定')
@section('page_header', 'QQ绑定')

@section('full')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">我的QQ绑定</h3>
    </div>
    <div class="card-body">
        @if($bindings->count() > 0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>QQ号</th>
                        <th>绑定角色</th>
                        <th>验证状态</th>
                        <th>绑定时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bindings as $binding)
                    <tr>
                        <td>{{ $binding->qq_number }}</td>
                        <td>{{ $binding->character->name ?? '未知角色' }}</td>
                        <td>
                            @if($binding->isVerified())
                                <span class="badge badge-success">已验证</span>
                            @else
                                <span class="badge badge-warning">未验证</span>
                            @endif
                        </td>
                        <td>{{ $binding->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <form method="POST" action="{{ route('qq-sync.binding.unbind', $binding->id) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('确定要解绑吗？')">解绑</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>您还没有绑定任何QQ号。</p>
        @endif
        
        <hr>
        
        <h4>绑定新的QQ号</h4>
        <form method="POST" action="{{ route('qq-sync.binding.bind') }}">
            @csrf
            <div class="form-group">
                <label for="qq_number">QQ号</label>
                <input type="text" class="form-control" id="qq_number" name="qq_number" required>
            </div>
            <div class="form-group">
                <label for="character_id">选择角色</label>
                <select class="form-control" id="character_id" name="character_id" required>
                    <option value="">请选择角色</option>
                    @foreach(auth()->user()->characters as $character)
                        <option value="{{ $character->character_id }}">{{ $character->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">绑定</button>
        </form>
    </div>
</div>
@endsection