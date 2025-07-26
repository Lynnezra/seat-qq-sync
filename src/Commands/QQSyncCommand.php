<?php

namespace Lynnezra\Seat\QQSync\Commands;

use Illuminate\Console\Command;
use Lynnezra\Seat\QQSync\Jobs\SyncQQMembers;

class QQSyncCommand extends Command
{
    protected $signature = 'qq-sync:check {--group=* : 指定要检查的群ID}';
    protected $description = '手动执行QQ群成员同步检查';
    
    public function handle()
    {
        $this->info('开始执行QQ群成员同步检查...');
        
        $groupIds = $this->option('group');
        
        SyncQQMembers::dispatch($groupIds);
        
        $this->info('同步任务已加入队列');
    }
}