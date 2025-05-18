<?php

namespace App\Console\Commands\Tenancy;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:seed {database_name? : The name of the specific database to seed}';
    //php artisan tenants:seed
    //php artisan tenants:seed database_name
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed all tenant databases or a specific tenant database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $database_name = $this->argument('database_name');

        if ($database_name) {
            $this->seedSingleDatabase($database_name);
        } else {
            $this->seedAllDatabases();
        }
    }

    /**
     * Seed a single database.
     *
     * @param string $database_name
     */
    private function seedSingleDatabase(string $database_name)
    {
        $this->seedDatabase($database_name);
    }

    /**
     * Seed all tenant databases.
     */
    private function seedAllDatabases()
    {
        $tenants_dbs = DB::connection('pgsql')->table('tenants')->pluck('database_name')->toArray();

        foreach ($tenants_dbs as $tenant_db) {
            $this->seedDatabase($tenant_db);
        }
    }

    /**
     * Perform seeding for a given database.
     *
     * @param string $tenant_db
     */
    private function seedDatabase(string $tenant_db)
    {
        Tenant::switchingDBConnection($tenant_db);

        try {
            $this->info($tenant_db . ' - Seed start');
            Artisan::call('db:seed', [
                '--database' => $tenant_db,
                '--force' => true,
                '--class' => 'TenancySeeder',
            ]);
            $this->info($tenant_db . ' - Seed end');
        } catch (\Exception $e) {
            $this->error($tenant_db . ' ' . $e->getMessage());
            $this->error($tenant_db . ' - Seeding failed');
        }
    }
}