<?php

namespace App\Jobs\Central;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class CreateDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $database_name;
    public function __construct($database_name)
    {
        $this->database_name = $database_name;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Log::info('Creating database: ' . $this->database_name);
        Tenant::createDatabase($this->database_name);
    }
}
