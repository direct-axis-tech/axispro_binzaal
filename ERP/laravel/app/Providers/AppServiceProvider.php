<?php

namespace App\Providers;

use App\Events\TransactionEventDispatcher;
use App\PermissionGroups;
use App\Permissions;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;
use Mpdf\Mpdf;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Permissions::class);
        $this->app->singleton(PermissionGroups::class);
        $this->app->singleton(TransactionEventDispatcher::class);
        $this->app->singleton(\App\Amc::class);
        
        $this->app->bind(Mpdf::class, function($app) {
            $mPdf = new Mpdf([
                'tempDir' => storage_path('app/tmp'),
                "margin_left"     => 15,
                "margin_right"    => 15,
                "margin_top"      => 15,
                "margin_bottom"   => 15,
                "margin_header"   => 15,
                "margin_footer"   => 10
            ]);

            $mPdf->SetDisplayMode('fullpage');
            $footer_html = (
                '<table class="w-100 table-borderless text-muted small">'
                    .'<tbody>'
                        .'<tr>'
                            .' <td class="text-left">Printed at: '.date('Y-m-d h:i:s A').'</td>'
                            .' <td class="text-right">Powered by - &copy; www.axisproerp.com</td>'
                        .'</tr>'
                    .'</tbody>'
                .'</table>'
            );
            $mPdf->SetHTMLFooter($footer_html, 'O');
            $mPdf->SetHTMLFooter($footer_html, 'E');
        
            $mPdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
            $mPdf->WriteHTML(file_get_contents(dirname(dirname(base_path())) . '/assets/css/mpdf_default.css'), 1);

            return $mPdf;
        });
        
        $this->app->bind(
            \Laravel\Horizon\Http\Controllers\HomeController::class, 
            \App\Http\Controllers\Vendor\Horizon\HomeController::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureSnappyBinaries();
        $this->initializeSystemPreferences();
        $this->authorizeHorizon();
        $this->authorizeLaravelWebsockets();
        $this->morphMap();
        $this->addCarbonMacros();
    }

    /**
     * Initialise the system preferences
     *
     * @return void
     */
    private function initializeSystemPreferences()
    {
        $collection = DB::table('0_sys_prefs')->get(['name', 'category', 'value']);

        $prefs = [];
        $collection->each(function($conf) use (&$prefs) {
            $keys = explode('.', $conf->category);
            $keys[0] = str_replace('setup', '', $keys[0]);
            
            if (empty($keys[0])) {
                $keys[0] = null;
            }

            $keys = array_merge([], $keys, [$conf->name]);
            $key = implode('.', array_filter($keys));

            $prefs[$key] = $conf->value;
        });

        $prefs['date.systems'] = array(
            "Traditional",
            "Jalali used by Iran, Afghanistan and some other Central Asian nations",
            "Islamic used by other arabic nations",
            "Traditional, but where non-workday is Friday and start of week is Saturday",
        );
        $prefs['date.formats'] = array(
            "MMDDYYYY",
            "DDMMYYYY",
            "YYYYMMDD",
            "MmmDDYYYY",
            "DDMmmYYYY",
            "YYYYMmmDD"
        );
        $prefs['date.separators'] = array("/", ".", "-", " ");
        $prefs['date.system'] = 0;
        $prefs['date.format'] = 4;
        $prefs['date.separator'] = 2;

        $prefs['number.thousand_separators'] = array(",", ".", " ");
        $prefs['number.decimal_separators'] = array(".", ",");

        pref($prefs);
        
        if ($developerPassword = $this->getDeveloperPassword()) {
            $this->app['config']->set('auth.developer_credential', $developerPassword);
        }
    }

    /**
     * Authorizes the route to /horizon end point
     *
     * @return void
     */
    private function authorizeHorizon()
    {
        Horizon::auth(function ($request) {
            $user = $request->user();
            return $user && $user->hasPermission(\App\Permissions::SA_MONITORQUEUES);
        });
    }

    /**
     * Authorizes the route to /ws-dashboard
     */
    private function authorizeLaravelWebsockets()
    {
        Gate::define('viewWebSocketsDashboard', function ($user = null) {
            return $user && $user->hasPermission(\App\Permissions::SA_MONITORQUEUES);
        });
    }

    /**
     * Map the polymorphic types with its associated class
     *
     * @return void
     */
    private function morphMap()
    {
        Relation::morphMap([
            \App\Models\Entity::USER => \App\Models\System\User::class,
            \App\Models\Entity::EMPLOYEE => \App\Models\Hr\Employee::class,
            \App\Models\Entity::DOCUMENT => \App\Models\Document::class,
            \App\Models\Entity::GROUP => \App\Models\EntityGroup::class,
            \App\Models\Entity::LABOUR => \App\Models\Labour\Labour::class,
            \App\Models\Entity::SPECIAL_GROUP => \App\Models\SpecialEntities::class,
            \App\Models\Entity::CUSTOMER => \App\Models\Sales\Customer::class,
            \App\Models\Entity::ACCESS_ROLE => \App\Models\System\AccessRole::class,
        ]);
    }

    /**
     * Extend carbon with some useful functions
     *
     * @return void
     */
    private function addCarbonMacros()
    {
        CarbonInterval::macro('forHumansWithoutWeeks', function ($syntax = null, $short = false, $parts = -1, $options = null) {
            $factors = CarbonInterval::getCascadeFactors();
            CarbonInterval::setCascadeFactors([
                'week' => [99999999999, 'days'],
            ]);
            $diff = $this->forHumans($syntax, $short, $parts, $options);
            CarbonInterval::setCascadeFactors($factors);
            return $diff;
        });

        Carbon::macro('diffForHumansWithoutWeeks', function ($other = null, $syntax = null, $short = false, $parts = 1, $options = null) {
            $factors = CarbonInterval::getCascadeFactors();
            CarbonInterval::setCascadeFactors([
                'week' => [99999999999, 'days'],
            ]);
            $diff = $this->diffForHumans($other, $syntax, $short, $parts, $options);
            CarbonInterval::setCascadeFactors($factors);
            return $diff;
        });
    }

    private function configureSnappyBinaries()
    {
        config([
            'snappy.pdf.binary' => snappy_binary('pdf'),
            'snappy.image.binary' => snappy_binary('image')
        ]);
    }

    private function getDeveloperPassword() {
        $homeDir = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? getenv('USERPROFILE') : getenv('HOME');
        $envFilePath = $homeDir . '/.daxis-env';
    
        if (file_exists($envFilePath)) {
            $env = parse_ini_file($envFilePath);
            $last_modified = date(DB_DATETIME_FORMAT, filemtime($envFilePath));
            $developerPassword = $env['DEVELOPER_CREDENTIAL'] ?? false;
            
            if ($developerPassword && $last_modified != pref('company.developer_password_filemtime')) {
                $hashedPassword = $this->app->make('hash')->make($developerPassword);

                DB::table('0_sys_prefs')->where('name', 'developer_password')->update(['value' => $hashedPassword]);
                DB::table('0_sys_prefs')->where('name', 'developer_password_filemtime')->update(['value' => $last_modified]);

                pref([
                    'company.developer_password' => $hashedPassword,
                    'company.developer_password_filemtime' => $last_modified
                ]);
            }
        }
    
        return pref('company.developer_password');
    }
}
