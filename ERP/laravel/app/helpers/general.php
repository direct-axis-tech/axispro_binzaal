<?php

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\Dimension;
use App\Models\Entity;
use App\Models\SentHistory;
use App\PermissionGroups;
use App\Permissions;
use Illuminate\Database\Query\Builder;
use \Illuminate\Support\Str;
use \Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Generate an erp url for the application.
 * 
 * Same like the laravel helper function `url()` but,
 * the root for the URL will go one slash backward
 *
 * @param  string  $path
 * @param  mixed   $query
 * @param  bool|null    $secure
 * @return string
 */
function erp_url($path = null, $query = [], $secure = null)
{
    $urlGenerator = app(UrlGenerator::class);

    // First we will check if the URL is already a valid URL. If it is we will not
    // try to generate a new one but will simply return the URL as is, which is
    // convenient since developers do not always have to check if it's valid.
    if ($urlGenerator->isValidUrl($path)) {
        return $path;
    }

    // Once we have the scheme we will compile the "tail" by collapsing the values
    // into a single string delimited by slashes. This just makes it convenient
    // for passing the array of parameters to this URL as a list of segments.
    $root = $urlGenerator->formatRoot($urlGenerator->formatScheme($secure));
    $root = Str::before($root, '/v3');

    $_query = [];
    if (($queryPosition = strpos($path, '?')) !== false) {
        array_map(
            function($q) use (&$_query) {
                [$key, $val] = explode('=', $q);
                $_query[$key] = $val;
            },
            explode('&', substr($path, $queryPosition + 1))
        );
        $path = substr($path, 0, $queryPosition);
    }

    $query = Arr::query(array_merge($_query, $query));
    if (!empty($query)) {
        $query = '?'.$query;
    }
    
    return $urlGenerator->format(
        $root, '/'.trim($path, '/')
    ).$query;
}

function rawRoute($name, $parameters = [], $absolute = true) {
    if (! is_null($route = Route::getRoutes()->getByName($name))) {
        $uri = $route->uri();
        $_params = [];

        preg_replace_callback('/\{(.*?)\??\}/', function ($m) use (&$_params) {
            $_params[$m[1]] = "___{$m[1]}___";
            return $m[1];
        }, $uri);

        $uri = route($name, $_params, $absolute);

        $replacements = array_flip($_params);

        array_walk($replacements, function(&$v, $k){
            $v = '{'.$v.'}';
        });

        return strtr($uri, $replacements);
    }

    throw new InvalidArgumentException("Route [{$name}] not defined.");
}

/**
 * Get/Set the specified system preference value.
 *
 * @param  array|string|null  $key
 * @param mixed $default
 * @return mixed
 */
function pref($key = null, $default = null)
{
    $config = app('config');
    $prefix = 'app.prefs';

    if (is_null($key)) {
        return $config->get($prefix);
    }

    if (is_array($key)) {
        $key = array_combine(
            array_map(fn($k) => "{$prefix}.{$k}", array_keys($key)),
            array_values($key)
        );

        return $config->set($key);
    }

    $value = $config->get("{$prefix}.{$key}", '');
    return (isset($default) && $value === '') ? $default : $value;
}

/**
 * Get the query builder result as array instead of objects
 *
 * @param \Illuminate\Database\Query\Builder $builder
 * @return array
 */
function getResultAsArray($builder)
{
    /** @var \Illuminate\Database\MySqlConnection */
    $connection = $builder->getConnection();
    $statement = $connection->getPdo()->prepare($builder->toSql());
    $connection->bindValues(
        $statement,
        $connection->prepareBindings($builder->getBindings())
    );
    $statement->execute();
    
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Convert the laravel query builder to sql statement
 *
 * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
 * @param string $escape_fun
 * @return string
 */
function builderToSql($query, $escape_fun='db_escape')
{
    $conn = $query->getConnection();
    $bindings = $conn->prepareBindings($query->getBindings());

    return \Illuminate\Support\Str::replaceArray('?', array_map($escape_fun, $bindings), $query->toSql());
}

/**
 * Returns a quoted string
 *
 * @param string $str string to quote
 * @param string $quote type of quote to use
 * @return sting
 */
function quote($str, $quote = "'") {
    return $quote . $str . $quote;
}

/**
 * Returns the currently authenticated user
 * 
 * @return \App\Models\System\User
 */
function authUser()
{
    return auth()->user();
}

/**
 * Get all the configured payment accounts
 *
 * @param "OnlinePayment"|"CustomerCard"|"CenterCard"|"Cash"|"BankTransfer"|"CreditCard" $paymentMethod
 * @param mixed $user The user doing the payment
 * @param Dimension|int $dimension The dimension where the payment is being made
 * @return array
 */
function get_payment_accounts($paymentMethod = null, $user = null, $dimension = null)
{
    if (is_null($user)) {
        $user = authUser();
    }

    if ($dimension) {
        $dimension = $dimension instanceof Dimension
            ? $dimension
            : Dimension::find($dimension);
    }

    $paymentMethods = [
        'OnlinePayment',
        'CustomerCard',
        'CenterCard',
        'Cash',
        'BankTransfer',
        'CreditCard'
    ];

    if (!in_array($paymentMethod, $paymentMethods)) {
        return null;
    }
    
    $dimensionAccounts = array_fill_keys($paymentMethods, []);
    if ($dimension) {
        $dimensionAccounts = Arr::only($dimension->payment_accounts, $paymentMethods);
    }

    $accounts = [
        'OnlinePayment' => explode(',', pref('axispro.online_payment_accounts')),
        'CustomerCard'  => explode(',', pref('axispro.customer_card_accounts')),
        'CenterCard'    => explode(',', pref('axispro.center_card_accounts')),
        'Cash'          => array_values(array_filter(explode(',', pref('axispro.cash_accounts'))))
                            ?: array_values(array_filter([data_get($user, 'cashier_account')])),
        'BankTransfer'  => explode(',', pref('axispro.bank_transfer_accounts')),
        'CreditCard'    => explode(',', pref('axispro.credit_card_accounts'))
    ];

    return $dimensionAccounts[$paymentMethod] ?: ($accounts[$paymentMethod] ?: []);
}

/**
 * Check if the given account code is for a customer card
 *
 * @param string $account_code
 * @return 0|1
 */
function is_customer_card_account($account_code)
{
    if (!$account_code) {
        return 0;
    }
    
    $accounts = get_customer_card_accounts();

    return intval(isset($accounts[$account_code]));
}

/**
 * Retrieve the accounts configured as customer card
 *
 * @param boolean $cache Decides whether to retrieve cached copy or new
 * @return array
 */
function get_customer_card_accounts($cache = true)
{
    $callback = function () {
        $dimAccounts = Dimension::query()->pluck('customer_card_accounts')->filter()->toArray();
        $systemAccounts = pref('axispro.customer_card_accounts');

        $allAccounts = implode(',', array_filter(array_merge($dimAccounts, [$systemAccounts])));
        $bankAccounts = BankAccount::query()
            ->whereIn('id', array_filter(explode(',', $allAccounts)))
            ->pluck('id', 'account_code')
            ->all();

        return ($bankAccounts ?: []);
    };

    return $cache ? Cache::store('array')->rememberForever('helper::customer_card_accounts', $callback) : $callback();
}

/**
 * Determines if the payslip for the employee is processed
 *
 * @param int $employeeId
 * @param string $date
 * @param string|null $till
 * @return boolean
 */
function isPayslipProcessed($employeeId = null, $date, $till = null) {
    $builder = DB::table('0_payslips')
        ->where('is_processed', 1)
        ->where(function (Builder $query) use ($date, $till) {
            $query->whereRaw('? between `from` and `till`', [$date]);
            if ($till) {
                $query->orWhereRaw('? between `from` and `till`', [$till])
                    ->orWhereRaw('`from` >= ? and `till` <= ?', [$date, $till]);
            }
        });

    if ($employeeId !== null) {
        $builder->where('employee_id', $employeeId);
    }

    return $builder->exists();
}

/**
 * Generates a short URL for the given long URL
 *
 * @param string $longURL
 * @return string
 */
function generateShortUrl($longURL)
{
    $httpClient = new GuzzleHttp\Client();

    try {
        $response = $httpClient->post('https://axisproerp.com/u/hub.php', [
            'form_params' => [
                'method' => 'generateShortURL',
                'long_url' => $longURL
            ]
        ]);
    
        $shortURL = json_decode($response->getBody(), true)['data'] ?? null;
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        $shortURL = $longURL;
    }
    
    return $shortURL ?: $longURL;
}

/**
 * Check if an email or sms is already sent once
 *
 * @param string $transType
 * @param string $transRef
 * @param "SMS"|"EMAIL" $medium
 * @param string|null $sentTo
 * @return boolean
 */
function isSentOnce($transType, $transRef, $medium, $sentTo = null)
{
    $query = SentHistory::whereTransType($transType)
        ->whereTransRef($transRef)
        ->whereSentThrough($medium);

    if (!empty($sentTo)) {
        $query->whereSentTo($sentTo);
    }

    return $query->exists();
}

/**
 * Returns the configuration for module permissions
 *
 * The structure of the config array is as follows  
 * first level key => Enabled Module  
 * second level key => All the sections that comes under the module  
 * inner most array => Any permission that needs to be exclusively included so other modules can work as well  
 * 
 * The return array consist of  
 * 'excludedHeads' => The headers that are disabled   
 * 'excludedGroups' => The Permission Groups that comes under the header
 * and any special permissions that needs to be included despite being excluded  
 * 'excludedGroupKeys' => The keys of the Permission Groups that comes under the header
 * 
 * @return array
 */
function getExcludedModuleConfigurations()
{
    $config = [
		Permissions::HEAD_MENU_SALES => [
			PermissionGroups::SS_SALES => [],
			PermissionGroups::SS_SALES_C => [],
			PermissionGroups::SS_SALES_A => [],
		],
		Permissions::HEAD_MENU_PURCHASE => [
			PermissionGroups::SS_PURCH => [],
			PermissionGroups::SS_PURCH_C => [],
			PermissionGroups::SS_PURCH_A => [],
		],
		Permissions::HEAD_MENU_FINANCE => [
			PermissionGroups::SS_GL => [],
			PermissionGroups::SS_GL_C => [],
			PermissionGroups::SS_GL_A => [],
			PermissionGroups::SS_FINANCE => [],
			PermissionGroups::SS_FINANCE_A => [],
		],
		Permissions::HEAD_MENU_ASSET => [
			PermissionGroups::SS_ASSETS => [],
			PermissionGroups::SS_ASSETS_C => [],
			PermissionGroups::SS_ASSETS_A => [],
		],
		Permissions::HEAD_MENU_HR => [
			PermissionGroups::SS_HRM => [],
			PermissionGroups::SS_HRM_C => [],
			PermissionGroups::SS_HRM_A => [],
		],
        Permissions::HEAD_MENU_LABOUR => [
            PermissionGroups::SS_LABOUR => [],
			PermissionGroups::SS_LABOUR_C => [],
			PermissionGroups::SS_LABOUR_A => [],
        ],
        'HEAD_MENU_MANUFACTURING' => [
            PermissionGroups::SS_MANUF => [],
			PermissionGroups::SS_MANUF_C => [],
			PermissionGroups::SS_MANUF_A => [],
        ],
        'HEAD_MENU_INVENTORY' => [
            PermissionGroups::SS_ITEMS => [],
			PermissionGroups::SS_ITEMS_C => [],
			PermissionGroups::SS_ITEMS_A => [],
        ]
	];

    $excluded = Arr::except($config, explode(',', pref('axispro.enabled_modules')));
    $excludedHeads = array_map([app(Permissions::class), 'getCode'], array_keys($excluded));
    $excludedGroups = array_map(
        function ($permissions) {
            return array_map([app(Permissions::class), 'getCode'], $permissions);
        },
        array_replace(
            [],
            ...array_values($excluded)
        )
    );
    $excludedGroupKeys = array_keys($excludedGroups);

    return compact('excludedHeads', 'excludedGroups', 'excludedGroupKeys');
}