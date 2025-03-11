<?php

namespace App\Providers;

use App\Repositories\EloquentHeartbeatRepository;
use App\Repositories\Interfaces\HeartbeatRepositoryInterface;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            HeartbeatRepositoryInterface::class,
            EloquentHeartbeatRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
