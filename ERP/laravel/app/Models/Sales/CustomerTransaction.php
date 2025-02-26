<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class CustomerTransaction extends Model
{
    /** @var string INVOICE Transaction Type - Sales Invoice */
    const INVOICE = 10;

    /** @var string CREDIT Transaction Type - Credit Note */
    const CREDIT = 11;

    /** @var string PAYMENT Transaction Type - Customer Payment */
    const PAYMENT = 12;

    /** @var string DELIVERY Transaction Type - Sales Delivery */
    const DELIVERY = 13;

    /** @var string REFUND Transaction Type - Customer Payment Refund */
    const REFUND = 14;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_debtor_trans';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Scopes this query with the type of transaction
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scopes this query with the activeness of transaction
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereRaw('(ov_amount + ov_gst + ov_freight + ov_freight_tax + ov_discount) <> 0');
    }

    /**
     * Get the link for printing the transaction
     */
    public function getPrintLinkAttribute()
    {
        switch($this->type) {
            case static::INVOICE:
                return erp_url(
                    'ERP/invoice_print/',
                    [
                        'PARAM_0' => "{$this->trans_no}-10",
                        'PARAM_1' => "{$this->trans_no}-10",
                        'PARAM_2' => '',
                        'PARAM_3' => '0',
                        'PARAM_4' => '',
                        'PARAM_5' => '',
                        'PARAM_6' => '',
                        'PARAM_7' => '0',
                        'REP_ID' => '107'
                    ]    
                );
            default: 
                return null; 
        }
    }

    /**
     * Get the link for updating the transaction id
     */
    public function getUpdateTransactionIdLinkAttribute()
    {
        if ($this->type != static::INVOICE) {
            return null;
        }

        return erp_url('/ERP/sales/customer_invoice.php', [
            'ModifyInvoice' => $this->trans_no
        ]);
    }

    /**
     * Calculate the total value from overall values
     */
    public function getTotalAttribute()
    {
        return $this->ov_amount + $this->ov_gst + $this->ov_discount + $this->ov_freight + $this->ov_freight_tax;
    }

    /**
     * The line details associated with this line
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details()
    {
        return $this->hasMany(CustomerTransactionDetail::class, 'debtor_trans_no', 'trans_no')
            ->where('debtor_trans_type', $this->type)
            ->where('quantity', '<>', 0);
    }

    /**
     * The customer associated with this transaction
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'debtor_no', 'debtor_no');
    }
}
