<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use PDO;
use PDOException;
use App\Traits\UUID;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory, UUID,SoftDeletes;

    protected $fillable = [
        'serial_number',
        'company_logo',
        'active_package',
        'tenant_name',
        'tenant_domain',
        'phone',
        'email',
        'database_name',
        'database_host',
        'database_port',
        'database_username',
        'database_password',
        'status',
        'account_activated_at',
        'account_activated_by',
        'account_deactivated_at',
        'account_deactivated_by',
        'description',
        'registration_no',
        'kra_pin',
        'address',
        'postal_code',
        'city',
        'website',
        'contact_name',
        'contact_title',
        'contact_email',
        'contact_phone',
        'legal_entity',
    ];


    public static function getFullDomainProperty($sub_domain = null)
    {
        // $centralUrl = env('CENTRAL_URL', 'http://localhost:8000');
        $centralUrl = env('CENTRAL_URL', 'https://events.solutech.com');
        if (!$sub_domain) {
            return $centralUrl;
        }

        $parsedUrl = parse_url($centralUrl);
        // Construct the base domain (scheme + host + optional port)
        $baseDomain = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        if (!empty($parsedUrl['port'])) {
            $baseDomain .= ':' . $parsedUrl['port'];
        }

        // Construct the full domain for the tenant
        $fullDomain = str_replace('://', '://' . $sub_domain . '.', $baseDomain);
        // dd($fullDomain);

        // If there's a path in the original URL, append it
        // if (!empty($parsedUrl['path'])) {
        //     $fullDomain .= $parsedUrl['path'];
        // }

        return $fullDomain;
    }


    public static function tenantDomain()
    {
        try {
            // Check if running in console context
            if (app()->runningInConsole()) {
                Log::debug("Running in console, no tenant domain detected");
                return null;
            }

            // Get the host from request
            try {
                $host = Request::getHost();
                Log::debug("Request host: {$host}");
            } catch (\Exception $e) {
                Log::warning("Unable to get request host: " . $e->getMessage());
                return null;
            }

            // Handle various edge cases
            if (empty($host) || $host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
                Log::debug("Host is empty, localhost, or IP address. No tenant subdomain.");
                return null;
            }

            $requestDomain = explode('.', $host);
            $parts_count = count($requestDomain);
            Log::debug("Domain parts: " . json_encode($requestDomain) . " (count: {$parts_count})");

            // Handle localhost domains (e.g., tenant.localhost)
            if ($parts_count == 2 && ($requestDomain[1] == 'localhost')) {
                Log::debug("Detected localhost subdomain: {$requestDomain[0]}");
                return $requestDomain[0];
            }

            // Handle development domains (e.g. 127.0.0.1:8000)
            if ($parts_count == 1) {
                Log::debug("Single part domain, treating as central domain");
                return null;
            }

            // Handle expose.dev and similar development proxy domains
            if ($parts_count == 3 && in_array($requestDomain[1], ['sharedwithexpose', 'loca', 'ngrok', 'serveo'])) {
                Log::debug("Detected development proxy domain, treating as central domain");
                return null;
            }

            // Handle domains like sharemaster.co.ke and their subdomains
            if ($parts_count >= 2 && $requestDomain[$parts_count - 2] == 'co' && $requestDomain[$parts_count - 1] == 'ke') {
                if ($parts_count == 3) {
                    // Central domain: sharemaster.co.ke
                    Log::debug("Detected central domain with co.ke");
                    return null;
                } else {
                    // Subdomain: tenant1.sharemaster.co.ke
                    Log::debug("Detected subdomain with co.ke: {$requestDomain[0]}");
                    return $requestDomain[0];
                }
            }

            // General case for other domains
            $result = ($parts_count > 2 && $requestDomain[0] != 'unify') ? $requestDomain[0] : null;
            Log::debug("Tenant domain detection result: " . ($result ?? 'null'));
            return $result;
        } catch (\Exception $e) {
            Log::error("Error in tenantDomain: " . $e->getMessage());
            return null;
        }
    }

    public static function redirectToHome()
    {
        try {
            // Check if running in console context first
            if (app()->runningInConsole()) {
                Log::debug("Running in console, no redirection needed");
                return [];
            }

            $tenant_subdomain = self::tenantDomain();

            // Log for debugging
            Log::debug("Tenant domain from request: " . ($tenant_subdomain ?? 'null'));

            if (!$tenant_subdomain) {
                // No subdomain found, returning empty array instead of a redirect
                // to prevent issues in non-HTTP contexts
                Log::info("No tenant subdomain detected");
                return [];
            }

            // Query for the tenant
            $domainData = Tenant::where('tenant_domain', $tenant_subdomain)
                ->where('status', true)
                ->first();

            if (!$domainData) {
                Log::warning("No tenant found with domain '{$tenant_subdomain}'");

                // Check if we're in a web request context that can handle redirects
                if (request()->expectsJson()) {
                    // For API requests, don't redirect
                    Log::info("API request detected, returning empty array instead of redirecting");
                    return [];
                } else {
                    // For web requests that can handle redirects
                    try {
                        $fullDomain = self::getFullDomainProperty();
                        Log::info("Redirecting to central domain: {$fullDomain}");
                        return redirect(self::getFullDomainProperty());
                    } catch (\Exception $e) {
                        Log::warning("Failed to create redirect: " . $e->getMessage());
                        return [];
                    }
                }
            } else {
                Log::info("Found tenant: {$domainData->tenant_name} ({$domainData->id})");
                return $domainData;
            }
        } catch (\Exception $e) {
            Log::error("Error in redirectToHome: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return [];
        }
    }

    private static $cachePrefix = 'dd_tenant_';
    private static $cacheTTL = 3600; // 1 hour

    public static function switchingDBConnection($db_name)
    {
        // Validate the database name with detailed diagnostics
        if (empty($db_name)) {
            $error_message = "Database name cannot be empty. Check your environment configuration.";
            $env_db_name = env('DB_DATABASE');
            $config_db_name = config('database.connections.' . config('database.default') . '.database');

            $diagnostic_info = "Environment diagnostics: " .
                "DB_DATABASE=" . ($env_db_name ?: 'not set') . ", " .
                "DB_CONNECTION=" . (env('DB_CONNECTION') ?: 'not set') . ", " .
                "DB_DRIVER=" . (env('DB_DRIVER') ?: 'not set') . ", " .
                "Config default connection=" . config('database.default') . ", " .
                "Config database name=" . ($config_db_name ?: 'not set');

            Log::error($error_message);
            Log::error($diagnostic_info);

            // Try to use default database from config if not provided
            if (!empty($config_db_name)) {
                Log::info("Using config database name as fallback: {$config_db_name}");
                $db_name = $config_db_name;
            } else {
                throw new \Exception($error_message . " " . $diagnostic_info);
            }
        }

        Log::info("Attempting to switch to database: {$db_name}");

        $cacheKey = self::$cachePrefix . $db_name;

        // Cache only the configuration, not the connection name
        $config = Cache::remember($cacheKey, self::$cacheTTL, function () use ($db_name) {
            Log::debug("Generating new database connection config for {$db_name}");
            return self::getConnectionConfig($db_name);
        });

        // Log configuration for debugging
        Log::debug("Database config for {$db_name}: driver={$config['driver']}, host={$config['host']}");

        // Also update the 'tenant' connection to use this database
        Config::set("database.connections.tenant.database", $db_name);

        // Set all driver-specific settings
        Config::set("database.connections.tenant.driver", $config['driver']);
        Config::set("database.connections.tenant.host", $config['host']);
        Config::set("database.connections.tenant.port", $config['port']);
        Config::set("database.connections.tenant.username", $config['username']);
        Config::set("database.connections.tenant.password", $config['password']);

        // Set the configuration for this unique connection
        Config::set("database.connections.{$db_name}", $config);

        // Establish a new connection
        DB::purge($db_name);
        DB::purge('tenant');

        // Try to connect using the database name as connection
        $connection = null;
        try {
            Log::debug("Attempting to connect using {$db_name} connection name");
            $connection = DB::connection($db_name);
            $connection->getPdo();

            // Set as default connection
            DB::setDefaultConnection($db_name);
            Log::info("Successfully connected to database {$db_name}");

            return $connection;
        } catch (\Exception $e) {
            // Handle connection error
            Log::warning("Failed to connect to database {$db_name}: {$e->getMessage()}");

            // Try the 'tenant' connection as a fallback
            try {
                Log::debug("Attempting to connect using tenant connection as fallback");
                $tenantConnection = DB::connection('tenant');
                $tenantConnection->getPdo();

                // Set tenant as default connection
                DB::setDefaultConnection('tenant');
                Log::info("Successfully connected to tenant database {$db_name} using tenant connection");

                return $tenantConnection;
            } catch (\Exception $e2) {
                Log::error("Failed to connect to tenant database: {$e2->getMessage()}");
                Log::error("Connection trace: " . $e2->getTraceAsString());
                throw new \Exception("Could not connect to database {$db_name}: {$e2->getMessage()}", 0, $e2);
            }
        }
    }

    private static function getConnectionConfig($db_name)
    {
        // Get the default connection from config
        $defaultConnectionName = config('database.default');
        Log::debug("Default connection from config: {$defaultConnectionName}");

        // Try to determine the driver with fallbacks
        $driver = env('DB_DRIVER') ?? env('DB_CONNECTION') ?? $defaultConnectionName ?? 'pgsql';

        // Log driver detection for debugging
        Log::debug("Database driver detection: DB_DRIVER=" . env('DB_DRIVER') .
            ", DB_CONNECTION=" . env('DB_CONNECTION') .
            ", Config default=" . $defaultConnectionName .
            ", Using: {$driver}");

        // Get from database.php config if environment variables aren't set
        $defaultConfig = config('database.connections.' . $driver, []);

        // If default config is empty, try the default connection
        if (empty($defaultConfig) && !empty($defaultConnectionName)) {
            $defaultConfig = config('database.connections.' . $defaultConnectionName, []);
            if (!empty($defaultConfig)) {
                $driver = $defaultConnectionName;
                Log::debug("Using default connection config for driver: {$driver}");
            }
        }

        // Get port default based on driver
        $defaultPort = '5432'; // Default for PostgreSQL
        if ($driver === 'mysql' || $driver === 'mariadb') {
            $defaultPort = '3306';
        } elseif ($driver === 'sqlsrv') {
            $defaultPort = '1433';
        }

        // Get username default based on driver
        $defaultUsername = 'postgres';
        if ($driver === 'mysql' || $driver === 'mariadb') {
            $defaultUsername = 'root';
        } elseif ($driver === 'sqlsrv') {
            $defaultUsername = 'sa';
        }

        // Build configuration with fallbacks to config file values
        $config = [
            'driver' => $driver, // Ensure driver is always defined
            'host' => env('DB_HOST', $defaultConfig['host'] ?? '127.0.0.1'),
            'port' => env('DB_PORT', $defaultConfig['port'] ?? $defaultPort),
            'database' => $db_name,
            'username' => env('DB_USERNAME', $defaultConfig['username'] ?? $defaultUsername),
            'password' => env('DB_PASSWORD', $defaultConfig['password'] ?? ''),
            'charset' => env('DB_CHARSET', $defaultConfig['charset'] ?? 'utf8'),
            'collation' => env('DB_COLLATION', $defaultConfig['collation'] ?? 'utf8_unicode_ci'),
            'prefix' => $defaultConfig['prefix'] ?? '',
            'prefix_indexes' => $defaultConfig['prefix_indexes'] ?? true,
            'strict' => $defaultConfig['strict'] ?? false,
            'engine' => $defaultConfig['engine'] ?? null,
        ];

        // Add specific settings based on driver
        if ($driver === 'pgsql') {
            $config['search_path'] = $defaultConfig['search_path'] ?? 'public';
            $config['sslmode'] = $defaultConfig['sslmode'] ?? 'prefer';
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            $config['modes'] = $defaultConfig['modes'] ?? [
                'ONLY_FULL_GROUP_BY',
                'STRICT_TRANS_TABLES',
                'NO_ZERO_IN_DATE',
                'NO_ZERO_DATE',
                'ERROR_FOR_DIVISION_BY_ZERO',
                'NO_ENGINE_SUBSTITUTION',
            ];
        } elseif ($driver === 'sqlsrv') {
            $config['trust_server_certificate'] = $defaultConfig['trust_server_certificate'] ?? false;
        }

        // Log full configuration for debugging
        Log::debug("Final connection config: " . json_encode($config));

        return $config;
    }


    //creating db
    public static function createDatabase(string $databaseName): bool
    {
        // Log::info("Job fired creating database: $databaseName");
        $pdo = null;
        try {
            // Get the default connection details from .env
            $host = env('DB_HOST');
            $port = env('DB_PORT');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            // Connect to PostgreSQL using the 'postgres' database
            $pdo = new PDO("pgsql:host=$host;port=$port;dbname=postgres", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if the database already exists
            $stmt = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = :dbname");
            $stmt->execute(['dbname' => $databaseName]);

            if ($stmt->fetch()) {
                // Log::info("Database $databaseName already exists.");
                return false;
            }

            // Create the new database
            $pdo->exec("CREATE DATABASE \"$databaseName\"");
            Log::info("Database $databaseName created successfully");
            // Call php artisan tenants:migrate database_name and php artisan tenants:seed database_name
            try {
                Log::info("Migrating and seeding database: $databaseName");
                Artisan::call('tenants:migrate', [
                    'database_name' => $databaseName,
                ]);
                Log::info("Seeding database: $databaseName");
                Artisan::call('tenants:seed', [
                    'database_name' => $databaseName,
                ]);
            } catch (\Exception $e) {
                // Log::error("Database creation failed in function: " . $e->getMessage());
                return false;
            }
            // Artisan::call('tenants:migrate', [
            //     'database_name' => $databaseName,
            // ]);
            // Artisan::call('tenants:seed', [
            //     'database_name' => $databaseName,
            // ]);

            return true;
        } catch (PDOException $e) {
            // Log::error("Database creation failed in function: " . $e->getMessage());
            return false;
        } finally {
            // Close the connection if it was opened
            if ($pdo !== null) {
                $pdo = null;
            }
        }
    }



    public static function forceDeleteDatabase(string $databaseName): bool
    {
        $pdo = null;
        try {
            // Get the default connection details from .env
            $host = env('DB_HOST');
            $port = env('DB_PORT');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');

            // Connect to PostgreSQL using the 'postgres' database
            $pdo = new PDO("pgsql:host=$host;port=$port;dbname=postgres", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Force close all other connections to the database
            $pdo->exec("SELECT pg_terminate_backend(pid)
                    FROM pg_stat_activity
                    WHERE datname = '$databaseName' AND pid <> pg_backend_pid()");

            // Drop the database
            $pdo->exec("DROP DATABASE IF EXISTS \"$databaseName\"");

            // Log::info("Database '$databaseName' forcefully deleted.");
            return true;
        } catch (PDOException $e) {
            // Log::error("Failed to delete database '$databaseName': " . $e->getMessage());
            return false;
        } finally {
            // Close the connection if it was opened
            if ($pdo !== null) {
                $pdo = null;
            }
        }
    }

    public static function centralCompany()
    {
        try {
            // Get the central database name from environment with fallbacks
            $db_name = env('DB_DATABASE');
            $default_connection = config('database.default');
            $connection_config = config('database.connections.' . $default_connection);

            // Log detailed diagnostic information
            Log::debug("DB_DATABASE env var: " . ($db_name ?: 'not set'));
            Log::debug("Default connection: " . $default_connection);
            Log::debug("Connection config database: " . ($connection_config['database'] ?? 'not set'));

            // Check if database name is empty and try fallbacks
            if (empty($db_name)) {
                // Try to get from config
                if (!empty($connection_config['database'])) {
                    $db_name = $connection_config['database'];
                    Log::info("Using database name from config: {$db_name}");
                } else {
                    Log::error("Central database name is empty. Check your .env file for DB_DATABASE setting.");
                    return self::getDefaultCompanyData();
                }
            }

            // Switch to the central database
            try {
                Tenant::switchingDBConnection($db_name);
            } catch (\Exception $e) {
                Log::error("Failed to switch database connection: " . $e->getMessage());
                return self::getDefaultCompanyData();
            }

            // Get the registered company
            $domainData = Tenant::redirectToHome();

            // Check if domainData is a Redirect response
            if (is_object($domainData) && method_exists($domainData, 'getStatusCode')) {
                Log::info("Received redirect response from redirectToHome()");
                return self::getDefaultCompanyData();
            }

            // Check if domainData is not a valid tenant object or is empty
            if (empty($domainData) || !is_object($domainData) || !isset($domainData->company_logo)) {
                Log::warning("Invalid or empty domain data returned");
                return self::getDefaultCompanyData();
            }

            // Ensure all properties are set to prevent undefined property errors
            $address = $domainData->address ?? '';
            $postal_code = $domainData->postal_code ?? '';
            $city = $domainData->city ?? '';

            return [
                'company_logo' => $domainData->company_logo,
                'tenant_domain' => $domainData->tenant_domain,
                'status' => $domainData->status,
                'tenant_name' => $domainData->tenant_name,
                'code' => 'central',
                'location' => $address . '-' . $postal_code . ', ' . $city,
                'website' => $domainData->website ?? '',
                'box_number' => $postal_code,
                'description' => $domainData->description ?? '',
                'contact_email' => $domainData->email ?? '',
                'contact_phone' => $domainData->phone ?? '',
                'registration_no' => $domainData->registration_no ?? '',
                'kra_pin' => $domainData->kra_pin ?? '',
            ];
        } catch (\Exception $e) {
            Log::error("Error in centralCompany: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return self::getDefaultCompanyData();
        }
    }

    protected static function getDefaultCompanyData()
    {
        return [
            'name' => 'Not Available',
            'code' => 'not available',
            'location' => 'Not Available',
            'website' => env('APP_URL'),
            'box_number' => 'P.O. Box 0000',
            'description' => 'Company Description',
            'contact_email' => 'null@account.com',
            'contact_phone' => '0700000000',
            'registration_no' => '0000000',
            'kra_pin' => '0000000',
            'company_logo' => 'default.png',
            'tenant_domain' => 'default',
            'status' => false,
        ];
    }

    public static function testFn()
    {
        return 'test';
    }
}
