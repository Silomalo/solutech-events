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
        $requestDomain = explode('.', Request::getHost());
        $parts_count = count($requestDomain);
        if ($parts_count == 2 && ($requestDomain[1] == 'localhost')) {
            return $requestDomain[0];
        }
        //while using ussd and hence using https://expose.dev/dashboard
        if ($parts_count == 3 && ($requestDomain[1] == 'sharedwithexpose')) {
            // expected imput will be https://cag85mfv2z.sharedwithexpose.com/
            // after running expose share http://greemasta.test
            // this will default to the central domain and be used to test the ussd
            return null;
        }

        // Handle domains like sharemaster.co.ke and their subdomains
        if ($parts_count >= 2 && $requestDomain[$parts_count - 2] == 'co' && $requestDomain[$parts_count - 1] == 'ke') {
            if ($parts_count == 3) {
                // Central domain: sharemaster.co.ke
                return null;
            } else {
                // Subdomain: tenant1.sharemaster.co.ke
                return $requestDomain[0];
            }
        }
        //this will work for all domains as long as the domain is not an IP address
        return ($parts_count > 2 && $requestDomain[0] != 'unify') ? $requestDomain[0] : null;
    }

    public static function redirectToHome()
    {
        $domainData = [];
        $tenant_subdomain = self::tenantDomain();
        if (!$tenant_subdomain) {
            return $domainData;
        } else {
            // dd($tenant_subdomain);
            $domainData = Tenant::where('tenant_domain', $tenant_subdomain)->where('status', true)->first();
            // dd($domainData);
            if (!$domainData) {
                return redirect(self::getFullDomainProperty());
            } else {
                return $domainData;
            }
        }
    }

    private static $cachePrefix = 'dd_tenant_';
    private static $cacheTTL = 3600; // 1 hour

    public static function switchingDBConnection($db_name)
    {
        // dd($db_name);
        $cacheKey = self::$cachePrefix . $db_name;
        // Cache only the configuration, not the connection name
        $config = Cache::remember($cacheKey, self::$cacheTTL, function () use ($db_name) {
            return self::getConnectionConfig($db_name);
        });

        // Set the configuration for this unique connection
        Config::set("database.connections.{$db_name}", $config);

        // Establish a new connection
        DB::purge($db_name);
        $connection = DB::connection($db_name);
        try {
            $connection->getPdo();
        } catch (\Exception $e) {
            // Handle connection error
            info("Failed to connect to database: {$e->getMessage()}");
        }

        // Set as default connection
        DB::setDefaultConnection($db_name);

        return $connection;
    }

    private static function getConnectionConfig($db_name)
    {
        return [
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => $db_name,
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'driver' => env('DB_DRIVER', 'pgsql'),
            'charset' => env('DB_CHARSET', 'utf8'),
            'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ];
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
        // switch to the central database
        $db_name = env('DB_DATABASE');
        Tenant::switchingDBConnection($db_name);
        // get the registered company
        $domainData = Tenant::redirectToHome();

        if ($domainData) {
            return [
                'company_logo' => $domainData->company_logo,
                'tenant_domain' => $domainData->tenant_domain,
                'status' => $domainData->status,
                'tenant_name' => $domainData->tenant_name,
                'code' => 'central ',
                'location' => $domainData->address . '-' . $domainData->postal_code . ', ' . $domainData->city,
                'website' => $domainData->website,
                'box_number' => $domainData->postal_code,
                'description' => $domainData->description,
                'contact_email' => $domainData->email,
                'contact_phone' => $domainData->phone,
                'registration_no' => $domainData->registration_no,
                'kra_pin' => $domainData->kra_pin,
            ];
        } else {
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
    }

    public static function testFn()
    {
        return 'test';
    }
}