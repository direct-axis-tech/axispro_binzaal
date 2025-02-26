<?php

namespace App\Models\Accounting;

use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\ServiceRequest;
use App\Models\Sales\Token;
use App\Permissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Dimension extends Model
{
    const AMER = '2';
    const TASHEEL = '-3';
    const RTA = '-4';
    const DHA = '-5';
    const YBC = '-6';
    const DUBAI_COURT = '-7';
    const DED = '-8';
    const ADHEED = '-9';
    const AMER_CBD = '-10';
    const TYPING = '-11';
    const ADHEED_OTH = '-12';
    const EJARI = '-13';
    const DED_OTH = '-14';
    const TAWJEEH = '-15';
    const TADBEER = '-16';
    const DOMESTIC_WORKER = '-30';
    const VIP_OFFICE = '-17';
    const CAFETERIA = '-18';
    const TYPING_WALKIN = '-19';
    const MEENA_LABS = '-21';
    const FILLET_KING = '-22';
    const MOFA = '-23';
    const TAP_CAFETERIA = '-24';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_dimensions';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Decides if this dimension require token or not
     *
     * @param boolean $value
     * @return boolean
     */
    public function isTokenRequired()
    {
        $authUser = auth()->user();
        return
            ($this->is_service_request_required && $authUser->doesntHavePermission(Permissions::SA_INVWTHOUTSRVRQST)) ||
            ($this->require_token && $authUser->doesntHavePermission(Permissions::SA_INVWTHOUTTKN));
    }

    /**
     * Validates the token based on dimension
     *
     * @param string $token
     * @return string|true
     */
    public function validateToken($token = null)
    {
        $isTokenRequired = $this->isTokenRequired();
        
        if (!$this->isHavingTokenFilter()) {
            return true;
        }

        if (empty($token)) {
            return $isTokenRequired ? "Please enter TOKEN NUMBER to proceed with invoice" : true;
        }

        $isTokenIssued = Token::ofToday()->where('token', $token)->count() > 0;

        // if token is required make sure its issued
        if ($isTokenRequired && !$isTokenIssued) {
            return "The token number is not valid";
        }

        // If service request is enabled for this dimension
        // Check that the token number specified have atleast one service request
        if ($isTokenRequired && $this->has_service_request) {
            $serviceRequests = ServiceRequest::ofToday()
                ->where('cost_center_id', $this->id)
                ->where('token_number', $token)
                ->count();

            return $serviceRequests < 1 ? "Cannot find any service request for this token" : true; 
        }

        // If token number needs to have a 1 to 1 relation ship with invoice. validate that
        if (
            $isTokenIssued &&
            $this->is_1to1_token &&
            CustomerTransaction
                ::ofType(CustomerTransaction::INVOICE)
                ->whereRaw('date(`transacted_at`) = ?', [now()->toDateString()])
                ->where('token_number', $token)
                ->count() > 0
        ) {
            return "This token number is already invoiced";
        }

        return true;
    }

    /**
     * Decides if this dimension has the token filter or not
     *
     * @return boolean
     */
    public function isHavingTokenFilter()
    {
        return (
            $this->has_service_request ||
            $this->has_token_filter ||
            $this->require_token
        );
    }

    /**
     * Decides if this dimension has service request or not
     *
     * @return boolean
     */
    public function isHavingServiceRequest()
    {
        return (
            $this->has_service_request ||
            $this->is_service_request_required
        );
    }

    /**
     * Get all the configured payment accounts for given payment methods
     * 
     * @return array[]
     */
    public function getPaymentAccountsAttribute()
    {
        $accounts = [
            'OnlinePayment' => array_values(array_filter(explode(',', $this->online_payment_accounts ?: ''))),
            'CustomerCard'  => array_values(array_filter(explode(',', $this->customer_card_accounts ?: ''))),
            'CenterCard'    => array_values(array_filter(explode(',', $this->center_card_accounts ?: ''))),
            'Cash'          => array_values(array_filter(explode(',', $this->cash_accounts ?: ''))),
            'BankTransfer'  => array_values(array_filter(explode(',', $this->bank_transfer_accounts ?: ''))),
            'CreditCard'    => array_values(array_filter(explode(',', $this->credit_card_accounts ?: '')))
        ];

        return $accounts;
    }
}