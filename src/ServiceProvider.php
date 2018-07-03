<?php

namespace Sofa\DbQueriesAlert;

use Illuminate\Support\Facades\DB;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        if (config('db_queries_alert.enabled')) {
            $this->app['db']->enableQueryLog();
        }

        $this->publishes([
            __DIR__ . '/../config/db_queries_alert.php' => config_path('db_queries_alert.php'),
        ]);
    }
}
