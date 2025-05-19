<?php

namespace App\Console\Commands\Tenancy;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class MigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'tenants:migrate {database_name? : The name of the specific database to migrate}';
    //php artisan tenants:migrate
    //php artisan tenants:migrate database_name
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for all tenants or a specific tenant database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $database_name = $this->argument('database_name');

        if ($database_name) {
            $this->migrateSingleDatabase($database_name);
        } else {
            $this->migrateAllDatabases();
        }
    }

    /**
     * Migrate a single database.
     *
     * @param string $database_name
     */
    private function migrateSingleDatabase(string $database_name)
    {
        $this->migrateDatabase($database_name);
    }

    /**
     * Migrate all tenant databases.
     */
    private function migrateAllDatabases()
    {
        $tenants_dbs = DB::connection('pgsql')->table('tenants')->pluck('database_name')->toArray();

        foreach ($tenants_dbs as $tenant_db) {
            $this->migrateDatabase($tenant_db);
        }
    }

    /**
     * Perform migration for a given database.
     *
     * @param string $tenant_db
     */
    private function migrateDatabase(string $tenant_db)
    {
        try {
            // Switch connection to the tenant database
            Tenant::switchingDBConnection($tenant_db);

            $this->info($tenant_db . ' - Migration start');

            // First, try using the tenant_db as connection name
            try {
                Artisan::call('migrate', [
                    '--database' => $tenant_db,
                    '--force' => true,
                    '--path' => 'database/migrations/tenancy',
                ]);
            } catch (\Exception $e) {
                $this->line("Falling back to 'tenant' connection for {$tenant_db}");

                // If that fails, try using the 'tenant' connection
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--force' => true,
                    '--path' => 'database/migrations/tenancy',
                ]);
            }

            $this->info($tenant_db . ' - Migration end');
        } catch (\Exception $e) {
            $this->error($tenant_db . ' ' . $e->getMessage());
            $this->error($tenant_db . ' - Migration failed');
        }
    }
}
