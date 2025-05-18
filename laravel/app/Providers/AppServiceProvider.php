<?php

namespace App\Providers;

use Exception;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // $this->loadGoogleDriver();

        if ($this->app->runningInConsole()) {
            return;
        }
        //before switching to the tenant database, get all file templates
        // $templates = ShareFiles::select('file_type as template_type', 'file_url as template_url')->get();
        // dd($templates);
        $tenant_details = Tenant::centralCompany();
        // dd($tenant_details);

        try {
            $domainData = Tenant::redirectToHome();
            // if  if get data from the database then switch to the tenant database
            if ($domainData) {
                Tenant::switchingDBConnection($domainData->database_name);
                $this->configureApplication($domainData);
            }
            // dd($domainData);
        } catch (Exception $e) {
            // Log::error('Tenant setup failed: ' . $e->getMessage());
            throw new NotFoundHttpException('Wrong access url');
            // create an email to notify the admin or send sms notification
        }


        // dd($domainData);
        // $company_logo = $domainData ? $domainData->company_logo : null;
        $title = $domainData ? $domainData->tenant_name : env('APP_NAME'); // used on loading app.blade.php

        $currency = 'Ksh. ';
        // View::share('currency', $currency, 'logo', $logo, 'title', $title);
        // View::share(compact('currency', 'company_logo', 'title', 'templates'));
        // View::share(compact('currency', 'title', 'tenant_details', 'templates'));
        // dd($templates);

        Blade::directive('currencyFormat', function ($expression) use ($currency) {
            return "<?php echo '$currency' . number_format($expression, 2, '.', ','); ?>";
        });

        // helper scripts
        Vite::useAggressivePrefetching();
        DB::prohibitDestructiveCommands(
            $this->app->isProduction(),
        );

    }


    private function configureApplication($domainData)
    {
        Config::set('app.url', $domainData->tenant_domain . '.sharemaster.co.ke');
        Config::set('app.name', $domainData->tenant_name . ' -ShareMaster');
        Config::set('cache.prefix', Str::slug($domainData->tenant_name, '_') . '_cache');
    }
}
