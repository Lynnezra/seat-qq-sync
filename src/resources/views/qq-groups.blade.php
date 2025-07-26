@extends('web::layouts.grids.12')

@section('title', 'QQ群管理')
@section('page_header', 'QQ群管理')

@section('full')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">QQ群配置</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addGroupModal">
                        <i class="fas fa-plus"></i> 添加QQ群
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>群号</th>
                                <th>群名称</th>
                                <th>状态</th>
                                <th>允许的军团</th>
                                <th>允许的联盟</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($groups as $group)
                                <tr>
                                    <td>{{ $group->group_id }}</td>
                                    <td>{{ $group->group_name ?: '未设置' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $group->is_active ? 'success' : 'danger' }}">
                                            {{ $group->is_active ? '启用' : '禁用' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($group->allowed_corporations)
                                            @php
                                                $corps = json_decode($group->allowed_corporations, true);
                                            @endphp
                                            <span class="badge badge-info">{{ count($corps) }} 个军团</span>
                                        @else
                                            <span class="text-muted">无限制</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($group->allowed_alliances)
                                            @php
                                                $alliances = json_decode($group->allowed_alliances, true);
                                            @endphp
                                            <span class="badge badge-info">{{ count($alliances) }} 个联盟</span>
                                        @else
                                            <span class="text-muted">无限制</span>
                                        @endif
                                    </td>
                                    <td>{{ $group->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                onclick="editGroup({{ $group->id }})">
                                            <i class="fas fa-edit"></i> 编辑
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteGroup({{ $group->id }})">
                                            <i class="fas fa-trash"></i> 删除
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        暂无QQ群配置
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 添加QQ群模态框 -->
<div class="modal fade" id="addGroupModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('qq-sync.groups.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">添加QQ群</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="group_id">QQ群号 *</label>
                        <input type="text" class="form-control" id="group_id" name="group_id" required>
                    </div>
                    <div class="form-group">
                        <label for="group_name">群名称</label>
                        <input type="text" class="form-control" id="group_name" name="group_name">
                    </div>
                    <div class="form-group">
                        <label for="allowed_corporations">允许的军团ID (JSON格式)</label>
                        <textarea class="form-control" id="allowed_corporations" name="allowed_corporations" 
                                  placeholder='例: [98000001, 98000002]'></textarea>
                        <small class="form-text text-muted">留空表示不限制军团</small>
                    </div>
                    <div class="form-group">
                        <label for="allowed_alliances">允许的联盟ID (JSON格式)</label>
                        <textarea class="form-control" id="allowed_alliances" name="allowed_alliances" 
                                  placeholder='例: [99000001, 99000002]'></textarea>
                        <small class="form-text text-muted">留空表示不限制联盟</small>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="is_active">启用此QQ群</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">添加</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 编辑QQ群模态框 -->
<div class="modal fade" id="editGroupModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editGroupForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h4 class="modal-title">编辑QQ群</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_group_id">QQ群号 *</label>
                        <input type="text" class="form-control" id="edit_group_id" name="group_id" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_group_name">群名称</label>
                        <input type="text" class="form-control" id="edit_group_name" name="group_name">
                    </div>
                    <div class="form-group">
                        <label for="edit_allowed_corporations">允许的军团ID (JSON格式)</label>
                        <textarea class="form-control" id="edit_allowed_corporations" name="allowed_corporations"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_allowed_alliances">允许的联盟ID (JSON格式)</label>
                        <textarea class="form-control" id="edit_allowed_alliances" name="allowed_alliances"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active" value="1">
                            <label class="custom-control-label" for="edit_is_active">启用此QQ群</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('javascript')
<script>
function editGroup(groupId) {
    fetch(`/qq-sync/groups/${groupId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_group_id').value = data.group_id;
            document.getElementById('edit_group_name').value = data.group_name || '';
            document.getElementById('edit_allowed_corporations').value = data.allowed_corporations || '';
            document.getElementById('edit_allowed_alliances').value = data.allowed_alliances || '';
            document.getElementById('edit_is_active').checked = data.is_active;
            document.getElementById('editGroupForm').action = `/qq-sync/groups/${groupId}`;
            $('#editGroupModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('获取群信息失败');
        });
}

function deleteGroup(groupId) {
    if (confirm('确定要删除这个QQ群配置吗？')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/qq-sync/groups/${groupId}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush