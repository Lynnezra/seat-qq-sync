<?php

namespace Lynnezra\Seat\QQSync;

use Seat\Services\AbstractSeatPlugin;
use Lynnezra\Seat\QQSync\Commands\QQSyncCommand;
use Lynnezra\Seat\QQSync\Jobs\SyncQQMembers;
use Lynnezra\Seat\QQSync\Services\NapcatService; // 添加这行

class QQSyncServiceProvider extends AbstractSeatPlugin
{
    public function boot()
    {
        $this->addRoutes();
        $this->addViews();
        $this->addMigrations();
        $this->addCommands();
        $this->addSchedule();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/qq-sync.php', 'qq-sync'
        );

        // 注册 NapcatService
        $this->app->singleton(NapcatService::class, function ($app) {
            return new NapcatService();
        });
    }

    private function addRoutes()
    {
        if (!$this->app->routesAreCached()) {
            include __DIR__ . '/Http/routes.php';
        }
    }

    private function addViews()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'qq-sync');
    }

    private function addMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    private function addCommands()
    {
        $this->commands([
            QQSyncCommand::class,
        ]);
    }

    private function addSchedule()
    {
        // 每5分钟检查一次群成员
        $this->app->booted(function () {
            $schedule = $this->app->make('Illuminate\Console\Scheduling\Schedule');
            $schedule->job(new SyncQQMembers())->everyFiveMinutes();
        });
    }

    public function getName(): string
    {
        return 'QQ Sync Plugin';
    }

    public function getPackageRepositoryUrl(): string
    {
        return 'https://github.com/Lynnezra/seat-qq-sync';
    }

    public function getVersion(): string
    {
        return config('qq-sync.version');
    }
}