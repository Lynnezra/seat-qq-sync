@extends('web::layouts.grids.12')

@section('title', 'QQ机器人设置')
@section('page_header', 'QQ机器人设置')

@section('full')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">机器人配置</h3>
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

                <form action="{{ route('qq-sync.bot.config.save') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="napcat_url">Napcat API地址 *</label>
                        <input type="url" class="form-control" id="napcat_url" name="napcat_url" 
                               value="{{ $config->napcat_url ?? '' }}" 
                               placeholder="http://localhost:3000" required>
                        <small class="form-text text-muted">Napcat HTTP API的完整地址</small>
                    </div>

                    <div class="form-group">
                        <label for="access_token">访问令牌</label>
                        <input type="password" class="form-control" id="access_token" name="access_token" 
                               value="{{ $config->access_token ?? '' }}" 
                               placeholder="留空表示无需认证">
                        <small class="form-text text-muted">Napcat的访问令牌，如果设置了的话</small>
                    </div>

                    <div class="form-group">
                        <label for="bot_qq">机器人QQ号 *</label>
                        <input type="text" class="form-control" id="bot_qq" name="bot_qq" 
                               value="{{ $config->bot_qq ?? '' }}" 
                               placeholder="123456789" required>
                        <small class="form-text text-muted">登录Napcat的QQ号</small>
                    </div>

                    <div class="form-group">
                        <label for="webhook_token">Webhook令牌</label>
                        <input type="text" class="form-control" id="webhook_token" name="webhook_token" 
                               value="{{ $config->webhook_token ?? '' }}" 
                               placeholder="自动生成或手动设置">
                        <small class="form-text text-muted">用于验证来自Napcat的Webhook请求</small>
                        <button type="button" class="btn btn-sm btn-secondary mt-1" onclick="generateToken()">
                            <i class="fas fa-refresh"></i> 生成新令牌
                        </button>
                    </div>

                    <div class="form-group">
                        <label for="check_interval">检查间隔 (秒)</label>
                        <input type="number" class="form-control" id="check_interval" name="check_interval" 
                               value="{{ $config->check_interval ?? 300 }}" 
                               min="60" max="3600">
                        <small class="form-text text-muted">定期检查群成员资格的间隔时间</small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                   value="1" {{ ($config->is_active ?? false) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">启用机器人</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="auto_approve" name="auto_approve" 
                                   value="1" {{ ($config->auto_approve ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="auto_approve">自动处理入群申请</label>
                        </div>
                        <small class="form-text text-muted">启用后将自动同意/拒绝入群申请</small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="send_notifications" name="send_notifications" 
                                   value="1" {{ ($config->send_notifications ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="send_notifications">发送通知消息</label>
                        </div>
                        <small class="form-text text-muted">踢出用户时发送通知消息到群内</small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 保存配置
                    </button>
                    <button type="button" class="btn btn-info" onclick="testConnection()">
                        <i class="fas fa-plug"></i> 测试连接
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">连接状态</h3>
            </div>
            <div class="card-body">
                <div id="connection-status">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> 检查中...
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Webhook配置</h3>
            </div>
            <div class="card-body">
                <p><strong>Webhook URL:</strong></p>
                <div class="input-group">
                    <input type="text" class="form-control" 
                           value="{{ route('qq-sync.webhook') }}" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="copyWebhookUrl()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <small class="form-text text-muted mt-2">
                    将此URL配置到Napcat的Webhook设置中
                </small>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">操作</h3>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-warning btn-block" onclick="syncAllGroups()">
                    <i class="fas fa-sync"></i> 立即同步所有群
                </button>
                <button type="button" class="btn btn-info btn-block mt-2" onclick="viewLogs()">
                    <i class="fas fa-list"></i> 查看同步日志
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('javascript')
<script>
// 页面加载时检查连接状态
$(document).ready(function() {
    checkConnectionStatus();
});

function checkConnectionStatus() {
    fetch('/qq-sync/bot/status')
        .then(response => response.json())
        .then(data => {
            const statusDiv = document.getElementById('connection-status');
            if (data.online) {
                statusDiv.innerHTML = `
                    <div class="text-success">
                        <i class="fas fa-check-circle"></i> 机器人在线
                        <br><small>QQ: ${data.qq || '未知'}</small>
                    </div>
                `;
            } else {
                statusDiv.innerHTML = `
                    <div class="text-danger">
                        <i class="fas fa-times-circle"></i> 机器人离线
                        <br><small>${data.error || '连接失败'}</small>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('connection-status').innerHTML = `
                <div class="text-warning">
                    <i class="fas fa-exclamation-triangle"></i> 状态未知
                    <br><small>检查失败</small>
                </div>
            `;
        });
}

function testConnection() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 测试中...';
    btn.disabled = true;

    fetch('/qq-sync/bot/test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('连接测试成功！');
            checkConnectionStatus();
        } else {
            alert('连接测试失败：' + (data.error || '未知错误'));
        }
    })
    .catch(error => {
        alert('测试失败：' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function generateToken() {
    const token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
    document.getElementById('webhook_token').value = token;
}

function copyWebhookUrl() {
    const input = event.target.closest('.input-group').querySelector('input');
    input.select();
    document.execCommand('copy');
    
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    setTimeout(() => {
        btn.innerHTML = originalHtml;
    }, 1000);
}

function syncAllGroups() {
    if (confirm('确定要立即同步所有QQ群吗？这可能需要一些时间。')) {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 同步中...';
        btn.disabled = true;

        fetch('/qq-sync/sync/all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('同步任务已启动！');
            } else {
                alert('启动同步失败：' + (data.error || '未知错误'));
            }
        })
        .catch(error => {
            alert('操作失败：' + error.message);
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
}

function viewLogs() {
    window.open('/qq-sync/logs', '_blank');
}
</script>
@endpush