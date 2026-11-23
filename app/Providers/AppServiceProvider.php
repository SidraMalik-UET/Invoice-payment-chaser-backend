<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Support\CurrentWorkspace;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(CurrentWorkspace::class);
    }

    public function boot(): void
    {
        //
    }
}