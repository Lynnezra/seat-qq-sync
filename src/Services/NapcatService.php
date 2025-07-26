<?php

namespace Lynnezra\Seat\QQSync\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lynnezra\Seat\QQSync\Models\QQBotConfig;

class NapcatService
{
    private $config;
    
    public function __construct()
    {
        $this->config = QQBotConfig::where('is_active', true)->first();
        
        if (!$this->config) {
            throw new \Exception('No active bot configuration found');
        }
    }
    
    /**
     * 获取群成员列表
     */
    public function getGroupMembers(string $groupId): array
    {
        try {
            $response = $this->makeRequest('get_group_member_list', [
                'group_id' => (int)$groupId
            ]);
            
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to get group members', [
                'group_id' => $groupId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * 踢出群成员
     */
    public function kickGroupMember(string $groupId, string $userId, bool $rejectAddRequest = true): bool
    {
        try {
            $response = $this->makeRequest('set_group_kick', [
                'group_id' => (int)$groupId,
                'user_id' => (int)$userId,
                'reject_add_request' => $rejectAddRequest
            ]);
            
            Log::info('Kicked group member', [
                'group_id' => $groupId,
                'user_id' => $userId,
                'success' => $response['status'] === 'ok'
            ]);
            
            return $response['status'] === 'ok';
        } catch (\Exception $e) {
            Log::error('Failed to kick group member', [
                'group_id' => $groupId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 同意入群申请
     */
    public function approveGroupRequest(string $flag, string $subType = 'add'): bool
    {
        try {
            $response = $this->makeRequest('set_group_add_request', [
                'flag' => $flag,
                'sub_type' => $subType,
                'approve' => true
            ]);
            
            Log::info('Approved group request', [
                'flag' => $flag,
                'success' => $response['status'] === 'ok'
            ]);
            
            return $response['status'] === 'ok';
        } catch (\Exception $e) {
            Log::error('Failed to approve group request', [
                'flag' => $flag,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 拒绝入群申请
     */
    public function rejectGroupRequest(string $flag, string $reason = '', string $subType = 'add'): bool
    {
        try {
            $response = $this->makeRequest('set_group_add_request', [
                'flag' => $flag,
                'sub_type' => $subType,
                'approve' => false,
                'reason' => $reason
            ]);
            
            Log::info('Rejected group request', [
                'flag' => $flag,
                'reason' => $reason,
                'success' => $response['status'] === 'ok'
            ]);
            
            return $response['status'] === 'ok';
        } catch (\Exception $e) {
            Log::error('Failed to reject group request', [
                'flag' => $flag,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 发送群消息
     */
    public function sendGroupMessage(string $groupId, string $message): bool
    {
        try {
            $response = $this->makeRequest('send_group_msg', [
                'group_id' => (int)$groupId,
                'message' => $message
            ]);
            
            return $response['status'] === 'ok';
        } catch (\Exception $e) {
            Log::error('Failed to send group message', [
                'group_id' => $groupId,
                'message' => $message,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 发送私聊消息
     */
    public function sendPrivateMessage(string $userId, string $message): bool
    {
        try {
            $response = $this->makeRequest('send_private_msg', [
                'user_id' => (int)$userId,
                'message' => $message
            ]);
            
            return $response['status'] === 'ok';
        } catch (\Exception $e) {
            Log::error('Failed to send private message', [
                'user_id' => $userId,
                'message' => $message,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 获取群信息
     */
    public function getGroupInfo(string $groupId): ?array
    {
        try {
            $response = $this->makeRequest('get_group_info', [
                'group_id' => (int)$groupId
            ]);
            
            return $response['data'] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to get group info', [
                'group_id' => $groupId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * 获取用户信息
     */
    public function getUserInfo(string $userId): ?array
    {
        try {
            $response = $this->makeRequest('get_stranger_info', [
                'user_id' => (int)$userId
            ]);
            
            return $response['data'] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to get user info', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * 检查机器人是否在线
     */
    public function isOnline(): bool
    {
        try {
            $response = $this->makeRequest('get_status');
            return $response['status'] === 'ok' && ($response['data']['online'] ?? false);
        } catch (\Exception $e) {
            Log::error('Failed to check bot status', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 执行API请求
     */
    private function makeRequest(string $action, array $params = []): array
    {
        $url = rtrim($this->config->napcat_api_url, '/') . '/' . $action;
        
        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'SeAT-QQ-Sync/1.0'
        ];
        
        // 如果配置了API token，添加到请求头
        if (!empty($this->config->api_token)) {
            $headers['Authorization'] = 'Bearer ' . $this->config->api_token;
        }
        
        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->post($url, $params);
        
        if (!$response->successful()) {
            throw new \Exception('HTTP request failed: ' . $response->status() . ' - ' . $response->body());
        }
        
        $data = $response->json();
        
        if (!isset($data['status'])) {
            throw new \Exception('Invalid response format: missing status field');
        }
        
        if ($data['status'] === 'failed') {
            throw new \Exception('API request failed: ' . ($data['msg'] ?? 'Unknown error'));
        }
        
        return $data;
    }
    
    /**
     * 批量检查用户是否在群内
     */
    public function checkUsersInGroup(string $groupId, array $userIds): array
    {
        $groupMembers = $this->getGroupMembers($groupId);
        $memberIds = array_column($groupMembers, 'user_id');
        
        $result = [];
        foreach ($userIds as $userId) {
            $result[$userId] = in_array((int)$userId, $memberIds);
        }
        
        return $result;
    }
    
    /**
     * 设置群管理员
     */
    public function setGroupAdmin(string $groupId, string $userId, bool $enable = true): bool
    {
        try {
            $response = $this->makeRequest('set_group_admin', [
                'group_id' => (int)$groupId,
                'user_id' => (int)$userId,
                'enable' => $enable
            ]);
            
            return $response['status'] === 'ok';
        } catch (\Exception $e) {
            Log::error('Failed to set group admin', [
                'group_id' => $groupId,
                'user_id' => $userId,
                'enable' => $enable,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}