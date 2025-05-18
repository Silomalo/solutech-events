<?php

namespace App\Providers;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessing;

class TenantQueueServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        Queue::createPayloadUsing(function () {
            $tenantId = DB::connection()->getDatabaseName();
            //switch to central database to store jobs
            // Tenant::switchingDBConnection(env('DB_DATABASE', 'unify_central'));
            // Log::info("Active connection in : " . DB::connection()->getDatabaseName());
            //switching to tenant database is done in queue database connection

            return [
                'tenant_id' => $tenantId,
                'db_connection' => Config::get('database.default')
            ];
        });

        Queue::before(function (JobProcessing $event) {
            if (isset($event->job->payload()['tenant_id'])) {
                $tenantId = $event->job->payload()['tenant_id'];
                $dbConnection = $event->job->payload()['db_connection'];

                // switch to tenant database
                Tenant::switchingDBConnection($tenantId);
                // Log::info("Active connection: " . DB::connection()->getDatabaseName());
            }
        });
    }
}
