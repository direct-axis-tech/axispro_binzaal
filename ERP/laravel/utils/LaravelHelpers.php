<?php

use App\Http\Kernel;
use App\Jobs\Sales\AggregateCustomerBalancesJob;
use App\Jobs\Sales\CalculateCustomerBalanceJob;
use App\Jobs\Sales\CalculateRunningCustomerBalancesJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

class LaravelHelpers {

    /**
     * Bootup the laravel application
     *
     * Esentially, this will bootup all the serviceProviders
     * defined inside laravel so that we can use them like we use inside laravel
     * but from FA
     * 
     * @param \Illuminate\Foundation\Application $app
     * @param \Illuminate\Http\Request $request
     * 
     * @return void
     */
    public static function bootstrapLaravel($app, $request) {
        $app->instance('request', $request);
        Facade::clearResolvedInstance('request');
        $app->bootstrapWith(app(Kernel::class)->getBootstrappers());
    }

    /**
     * Capture the Http Requst
     *
     * @param \Illuminate\Foundation\Application $app
     * 
     * @return \Illuminate\Http\Request
     */
    public static function captureRequest($app) {
        $scriptFileName = $_SERVER['SCRIPT_FILENAME'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $requestURI = $_SERVER['REQUEST_URI'] ?? null;
        $phpSelf = $_SERVER['PHP_SELF'];
        $redirectURL = $_SERVER['REDIRECT_URL'] ?? null;

        // Dirty Hack - This could break something. who knows
        $realScriptFileName = str_replace('\\', '/', realpath($scriptFileName));
        $currentFile = str_replace('\\', '/', __FILE__);
        $realScriptName = implode(
            '/',
            array_intersect(
                explode('/', $realScriptFileName),
                explode('/', $scriptName)
            )
        );
        $documentRoot = Str::before($realScriptFileName, $realScriptName);
        $relativePath = Str::after($currentFile, $documentRoot);
        $appDir = dirname(dirname(dirname(dirname($relativePath))));
        $appDir = str_replace('\\', '/', $appDir);

        $virtualPath = Str::before($scriptName, $realScriptName);
        if (!empty($virtualPath) && $virtualPath != $scriptName) {
            $appDir = join_paths($virtualPath, $appDir);
        }

        $laravelEntryPoint = '/v3/index.php';
        $_requestURI = $requestURI;
        if ($appDir !== '/') {
            $laravelEntryPoint = $appDir . $laravelEntryPoint;
            $_requestURI = Str::after($_requestURI, $appDir);
        }
        if (Str::startsWith($_requestURI, '/index.php')) {
            $_requestURI = str_replace('/index.php', '/', $_requestURI);
        }
        $_requestURI = dirname($laravelEntryPoint) . $_requestURI;

        // Lets fool laravel so laravel would think this one as a normal request
        $_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'] . $laravelEntryPoint;
        $_SERVER['SCRIPT_NAME'] = $laravelEntryPoint;
        $_SERVER['PHP_SELF'] = $laravelEntryPoint;
        $_SERVER['REDIRECT_URL'] = $_requestURI;
        $_SERVER['REQUEST_URI'] = $_requestURI;

        $request = \Illuminate\Http\Request::capture();

        $_SERVER['SCRIPT_FILENAME'] = $scriptFileName;
        $_SERVER['SCRIPT_NAME'] = $scriptName;
        $_SERVER['PHP_SELF'] = $phpSelf;
        $_SERVER['REQUEST_URI'] = $requestURI;
        if ($redirectURL) {
            $_SERVER['REDIRECT_URL'] = $redirectURL;
        }

        return $request;
    }

    /**
     * Store the current URL for the request if necessary.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @param boolean $isFromLaravel
     * @return void
     */
    public static function storeCurrentUrl($request, $session, $isFromLaravel = false)
    {
        $url = $request->fullUrl();

        if ($request->method() === 'GET' &&
            ! $request->ajax() &&
            ! $request->prefetch() &&
            ! Str::contains($url, 'login') &&
            ! Str::contains($url, 'logout')) {

            // Remove the v3 prefix from the URL that we added to fool laravel
            if (!$isFromLaravel) {
                $url = preg_replace('#/v3#', '', $url);
                $_SESSION['_previous']['url'] = $url;
            }

            $session->setPreviousUrl($url);
        }
    }

    /**
     * Starts the laravel session
     * 
     * In order for FA to set post request to Laravel routes we need the csrf token which
     * laravel stores in the session.
     *
     * @param \Illuminate\Session\SessionManager $manager
     * @param \Illuminate\Http\Request $request
     * 
     * @return void
     */
    public static function startLaravelSession($manager, $request) {
        $session = $manager->driver();
        $session->setId($request->cookies->get($session->getName()));
        $session->setRequestOnHandler($request);
        $session->start();

        $request->setLaravelSession($session);
        $request->setUserResolver(function() {
            $currentUser =  $_SESSION['wa_current_user'];
            return $currentUser ? $currentUser->get_user_model() : null;
        });

        static::registerMiddlewares();
    }

    /**
     * Register class bindings
     *
     * @param \Illuminate\Container\Container $app
     * @return void
     */
    public static function registerBindings($app)
    {
        $app->singleton(
            API_Call::class,
            function() {
                $path_to_root = $GLOBALS['path_to_root'];
                include_once $path_to_root . "/API/API_Call.php";
        
                return new API_Call();
            }
        );

        $app->singleton(
            Logger::class,
            function() {
                if (empty($GLOBALS['db'])) {
                    set_global_connection();
                }
        
                $mysqliHandler =  new MySQLiLogHandler($GLOBALS['db']);
                $psrProcessor = new PsrLogMessageProcessor(DB_DATETIME_FORMAT);
        
                $channel = 'UserActivity';
                if ($_SESSION['wa_current_user']->is_developer_session) {
                    $channel = 'DeveloperActivity';
                }
        
                return new Logger($channel, [$mysqliHandler], [$psrProcessor]);
            }
        );
        
        $app->singleton(AfterMiddleware::class);

        // Aliases
        $app->alias(Logger::class, 'activityLogger');
        $app->alias(API_Call::class, 'api');
    }

    /**
     * Register middlewares for frontaccounting
     */
    protected static function registerMiddlewares()
    {
        $afterMiddleware = app(AfterMiddleware::class);

        // Store the previous url in the session
        $afterMiddleware->register(function() {
            static::storeCurrentUrl(request(), session());
        });

        // Calculate the customer balance
        $afterMiddleware->register(function() {
            if (!config()->has('shouldCalculateCustomerBalance')) {
                return;
            }

            foreach (config()->get('shouldCalculateCustomerBalance') as $customerId => $dates) {
                $keys = array_filter(array_map(function ($date) {
                    $today = Carbon::now()->startOfDay();
                    $date = Carbon::parse($date)->startOfDay();
                    
                    if ($date < $today) {
                        return AggregateCustomerBalancesJob::getKeyForDate($date);
                    }
                    
                    return null;
                }, $dates));
                
                foreach (array_unique($keys) as $key) {
                    AggregateCustomerBalancesJob::dispatchNow(
                        ['debtor_no' => $customerId, 'key' => $key],
                        false
                    );
                }
                
                CalculateRunningCustomerBalancesJob::dispatchNow([
                    'debtor_no' => $customerId
                ]);

                CalculateCustomerBalanceJob::dispatchNow($customerId);
            }
        });
    }

    /**
     * Inject the frontaccounting database connection to laravel
     */
    public static function injectFAConnectionToLaravel()
    {
        config()->set('database.connections.fa', array_merge(
            config('database.connections.mysql'),
            [
                'driver' => 'mysqli',
                'connection' => $GLOBALS['db'],
            ]
        ));

        $app = app();
        (new \LaravelEloquentMySQLi\MySQLiServiceProvider($app))->register();

        $app['db']->extend('mysqli', function (array $config, $name) use ($app) {
            $adapter = is_a($GLOBALS['db'] ?? null, \mysqli::class) ?
                $GLOBALS['db']
                : $app['db.connector.mysqli']->connect($config);
                
            return new \LaravelEloquentMySQLi\MySQLiConnection($adapter, $config['database'], $config['prefix'], $config);
        });
    }
}