<?php

namespace App\Models\Sales;

use App\Models\Accounting\Ledger;
use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use InactiveModel;
    
    const WALK_IN_CUSTOMER =  '1';
    const TYPE_CREDIT_CUSTOMER = 'CREDIT';
    const TYPE_CASH_CUSTOMER = 'CASH';
    const CATEGORY_TYPE_A = 'A';
    const CATEGORY_TYPE_B = 'B';
    const CATEGORY_TYPE_C = 'C';
    const CATEGORY_TYPE_D = 'D';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_debtors_master';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'debtor_no';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * All the transactions of this customer
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(\App\Models\Sales\CustomerTransaction::class, 'debtor_no');
    }

    /**
     * Returns the formatted_name attribute
     *
     * @return string
     */
    public function getFormattedNameAttribute()
    {
        return $this->debtor_ref . ' - ' . $this->name;
    }

    /**
     * Get the defualt branch of this customer
     *
     * @return CustomerBranch|null
     */
    public function getDefaultBranchAttribute()
    {
        return $this->branches->first();
    }

    /**
     * Get all the branches associated with this customer
     */
    public function branches()
    {
        return $this->hasMany(CustomerBranch::class, 'debtor_no');
    }

    /**
     * This customer's nationality
     *
     * @return void
     */
    public function country()
    {
        return $this->belongsTo(\App\Models\Country::class, 'nationality', 'code');
    }

    /**
     * Register an automatic customer
     *
     * @param array $data
     * @return static
     */
    public static function registerAutoCustomer($data)
    {
        $customer = static::find(static::WALK_IN_CUSTOMER);
        $branch = $customer->default_branch;
        $nextRef = static::getNextAutoCustomerRef();

        $new = $customer->replicate();
        $new->setRelations([]);
        $new->customer_type = Customer::TYPE_CASH_CUSTOMER;
        $new->name = $data['name'];
        $new->contact_person = $data['contact_person'] ?? '';
        $new->tax_id = $data['trn'] ?? '';
        $new->debtor_ref = $nextRef;
        $new->credit_limit = pref('gl.customer.default_credit_limit') ?: 0;
        $new->credit_days = null;
        $new->balance = 0;
        $new->mobile = $data['mobile'];
        $new->debtor_email = $data['email'];
        $new->iban_no = $data['iban_no'] ?? '';
        $new->cr_lmt_warning_lvl = pref('customer.dflt_cr_lmt_warning_lvl');
        $new->cr_lmt_notice_lvl = pref('customer.dflt_cr_lmt_notice_lvl');
        $new->created_by = authUser()->id;
        $new->created_at = date(DB_DATETIME_FORMAT);

        $newBranch = $branch->replicate();
        $newBranch->debtor_no = null;
        $newBranch->branch_ref = $nextRef;
        $newBranch->br_name = $data['name'];
        $newBranch->receivables_account = pref('gl.sales.walkin_receivable_act');

        DB::transaction(function() use($new, $newBranch) {
            $new->save();
            $new->branches()->save($newBranch);
        });

        return $new;
    }

    public static function getNextCustomerRef()
    {
        $customer_id_prefix = pref('axispro.customer_id_prefix') ?: '';

        $customerID = static::query()
            ->selectRaw(
                'MAX(CAST(REPLACE(debtor_ref, ?, "") AS UNSIGNED)) as cust_id',
                [$customer_id_prefix]
            )
            ->when($customer_id_prefix, function ($query, $customer_id_prefix) {
                return $query->whereRaw('debtor_ref REGEXP ?', ['^' . $customer_id_prefix . '[0-9]+$']);
            })
            ->value('cust_id');
        
        return $customerID ? $customer_id_prefix.str_pad(++$customerID, 4, "0", STR_PAD_LEFT) : $customer_id_prefix.'0001' ;
    }

    /**
     * Get the next reference number for automatically registering customer
     *
     * @return string
     */
    public static function getNextAutoCustomerRef()
    {
        $ref = DB::table('0_debtors_master')
            ->selectRaw(
                'CONCAT('
                    . '"AU",'
                    . 'LPAD('
                        . 'MAX(CAST(REPLACE(debtor_ref, "AU", "") AS UNSIGNED)) + 1,'
                        . 'GREATEST(LENGTH(MAX(CAST(REPLACE(debtor_ref, "AU", "") AS UNSIGNED)) + 1), 5),'
                        . '0'
                    . ')'
                . ') as debtor_ref'
            )
            ->where('debtor_ref', 'like', 'AU%')
            ->value('debtor_ref');
    
        return empty($ref) ? 'AU00001' : $ref;
    }

    /**
     * Query for retrieving the cached opening balance
     *
     * @param string $customerId
     * @param string|null $tillDate date formatted in 'Y-m-d' format
     * @return \Illuminate\Database\Query\Builder
     */
    public static function cachedOpeningBalanceQuery($customerId, $tillDate = null)
    {
        $subQuery = DB::table('0_customer_balances as cb')
            ->select('cb.id')
            ->where('cb.debtor_no', $customerId)
            ->orderByDesc('cb.from_date')
            ->take(1);

        if ($tillDate) {
            $subQuery->selectRaw("IF(cb.till_date < ?, cb.id, cb.previous_id) as _id", [$tillDate]);
        } else {
            $subQuery->addSelect('cb.previous_id as _id');
        }

        $query = DB::query()
            ->fromSub($subQuery, 'temp')
            ->leftJoin('0_customer_balances as cache', 'temp._id', 'cache.id')
            ->select(
                'temp.id as temp_id',
                'cache.*'
            );

        return $query;
    }
}