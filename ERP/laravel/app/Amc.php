<?php

namespace App;

use App\Exceptions\AmcExpiredException;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class Amc {
    /** 
     * @var \Carbon\CarbonImmutable
     */
    private $now = null;

    /** 
     * @var boolean
     */
    private $shouldShowExpiryMsg = false;

    /**
     * @var string
     */
    private $ackDueAt = null;
    
    /**
     * @var \Carbon\CarbonImmutable
     */
    private $lastRenewedTill = null;

    /**
     * @var \Carbon\CarbonImmutable
     */
    private $expiryDate = null;
    
    /**
     * @var \Carbon\CarbonImmutable
     */
    private $gracePeriodEndDate = null;
    
    /**
     * @var \Carbon\CarbonImmutable
     */
    private $localUpdatedAt = null;

    /** 
     * @var boolean
     */
    private $isExpiryApproaching = false;

    /** 
     * @var boolean
     */
    private $isExpiryImminent = false;
    
    /** 
     * @var boolean
     */
    private $isExpired = false;

    /**
     * @var array
     */
    private $config = [];

    /**
     * Instantiate the object of this class
     */
    public function __construct($now = null)
    {
        $this->now = new CarbonImmutable($now);

        $this->read();
    }

    /**
     * Read the current AMC status from the db
     *
     * @return void
     */
    private function read()
    {
        if (pref('axispro.amc_last_renewed_till')) {
            $this->lastRenewedTill = CarbonImmutable::parse(pref('axispro.amc_last_renewed_till'));
        }

        if (!$this->lastRenewedTill) {
            return;
        }

        $this->config = [
            'duration_in_months' => pref('axispro.amc_duration_in_months', 12),
            'early_notice_days' => pref('axispro.amc_early_notice_days', 30),
            'late_notice_days' => pref('axispro.amc_late_notice_days', 7),
            'grace_days_after_expiry' => pref('axispro.amc_grace_days_after_expiry', 0),
            'password_required' => pref('axispro.amc_pwd_req_for_local_update', ''),
        ];

        if ($localUpdatedAt = pref('axispro.amc_system_updated_at')) {
            $this->localUpdatedAt = CarbonImmutable::parse($localUpdatedAt);
        }

        $this->calculateExpiryStatus();

        $this->decideIfWeShouldShowExpiryMsg();
    }

    /**
     * Calculate the AMC (Annual Maintenance Contract) expiry status.
     *
     * @return void
     */
    private function calculateExpiryStatus()
    {
        $this->expiryDate = $this->lastRenewedTill
            ->addMonthsWithoutOverflow($this->config['duration_in_months'])
            ->startOfDay();
            
        $this->isExpiryApproaching = $this->now >= $this->expiryDate->subDays($this->config['early_notice_days']);
        $this->isExpiryImminent = $this->now >= $this->expiryDate->subDays($this->config['late_notice_days']);
        $this->isExpired = $this->now >= $this->expiryDate;
        $this->gracePeriodEndDate = $this->config['grace_days_after_expiry'] == -1
            ? null
            : $this->expiryDate->addDays($this->config['grace_days_after_expiry']);
    }

    /**
     * Decides whether to show the AMC expiry message to the user.
     *
     * This function checks the user's role and AMC expiry status to determine if an expiry message
     * should be shown. It also manages the acknowledgement times and due dates for the expiry message.
     *
     * @return void
     */
    private function decideIfWeShouldShowExpiryMsg()
    {
        // If the user is not one among the targeted roles or,
        // the amc is still far from expiring, no need to go any further
        if (
            !$this->isExpiryApproaching
            || !($user = authUser())
            || !in_array($user->role_id, explode(',', pref('axispro.amc_notify_to_roles', '')))
        ) {
            return ;
        }

        $user = $user->fresh();

        // Reset the counter if necessary
        if (
            $user->amc_expiry_times_ack != 0
            && !empty($user->amc_expiry_ack_at)
            && Carbon::parse($user->amc_expiry_ack_at)->format(DB_DATE_FORMAT) != date(DB_DATE_FORMAT)
        ) {
            $user->amc_expiry_times_ack = 0;

            // Update without modifying the timestamps
            $user->query()->whereId($user->id)->update(['amc_expiry_times_ack' => $user->amc_expiry_times_ack]);
        }

        $this->shouldShowExpiryMsg = true;

        // Calculate the next amc acknowledgement due date
        $lastAcknowledgedAt = $user->amc_expiry_ack_at ? CarbonImmutable::parse($user->amc_expiry_ack_at) : null;
        $this->ackDueAt = $user->amc_expiry_ack_due_at;

        // If not already set, or due date is old set it to current time stamp
        if (
            !$this->ackDueAt
            || substr($this->ackDueAt, 0, 10) < date(DB_DATE_FORMAT)
        ) {
            $this->ackDueAt = date(DB_DATETIME_FORMAT);
        }

        // If the expiry acknowledgement is due in the future, it means we have
        // already calculated it, don't need to calculate again.
        else if (Carbon::parse($this->ackDueAt) > $this->now) {
            $this->shouldShowExpiryMsg = false;
        }

        // If the user has already acknowledged the expiry msg within last 4 hours
        // and this is just a friendly reminder, no need to acknowledge again.
        else if ($lastAcknowledgedAt && !$this->isExpiryImminent) {
            if ($lastAcknowledgedAt->addHours(4) > $this->now) {
                $this->ackDueAt = $lastAcknowledgedAt->addHours(4)->toDateTimeString();
                $this->shouldShowExpiryMsg = false;
            }

            else if ($this->now->subHours(4) > $this->ackDueAt) {
                $this->ackDueAt = $this->now->toDateTimeString();
            }
        }

        // Otherwise, its a late notice, make the user acknowledge how many times as needed
        // Currently we set it to show every 1 hour.
        else if ($lastAcknowledgedAt) {
            if ($lastAcknowledgedAt->addHour() > $this->now) {
                $this->ackDueAt = $lastAcknowledgedAt->addHour()->toDateTimeString();
                $this->shouldShowExpiryMsg = false;
            }

            else if ($this->now->subHour() > $this->ackDueAt) {
                $this->ackDueAt = $this->now->toDateTimeString();
            }
        }


        // If there is any change in the value, update the user
        if ($this->ackDueAt != $user->amc_expiry_ack_due_at) {
            $user->amc_expiry_ack_due_at = $this->ackDueAt;
            $user->query()->whereId($user->id)->update(['amc_expiry_ack_due_at' => $this->ackDueAt]);
        }
    }

    /**
     * Enforce the AMC validity
     *
     * @return void
     * @throws \App\Exceptions\AmcExpiredException
     */
    public function enforceValidity()
    {
        if ($this->shouldFetchFromUpstream()) {
            $this->fetchFromUpstream();
        }

        if (
            $this->gracePeriodEndDate
            && $this->gracePeriodEndDate <= $this->now
            && request()->fullUrl() != url('/ERP?login_method=AJAX')
            && authUser()
            && !session()->get('isInDeveloperSession', false)
        ) {
            throw new AmcExpiredException("", 503);
        }
    }

    public function getAckDueAt()
    {
        return $this->ackDueAt;
    }

    public function shouldShowExpiryMsg()
    {
        return $this->shouldShowExpiryMsg;
    }

    public function getWaitTimeBeforeAck()
    {
        return $this->isExpiryImminent ? 9 : 0;
    }

    /**
     * Returns the readable description for days remaining
     *
     * @param \Carbon\CarbonInterface $date
     * @return string
     */
    private function getDaysRemainingForHuman(CarbonInterface $date = null) {
        if (is_null($date)) {
            return '';
        }

        $daysRemaining = $this->now->diffInDays($date, false);

        if ($daysRemaining < 0) {
            return '';
        }

        if ($daysRemaining == 0) {
            return '&nbsp;Today';
        }

        if ($daysRemaining == 1) {
            return '&nbsp;Tomorrow';
        }

        return "&nbsp;in {$daysRemaining} days";
    }

    public function getExpiryMsg()
    {
        if ($this->isExpired) {
            return "<p style=\"color: #dc3545;\">Your AWS Hosting has expired.<br>"
                . "You are currently in the grace period.<br>"
                . "Unless renewed, this instance will be discontinued<span style=\"font-weight: bolder;\">".$this->getDaysRemainingForHuman($this->gracePeriodEndDate).".</span></p>";
        }
        
        if ($this->isExpiryImminent) {
            return "<p>Your AWS Hosting is about to expire<span style=\"color: #dc3545;\">". $this->getDaysRemainingForHuman($this->expiryDate)."</span>.<br>"
                . "To avoid any potential service disruption, please renew your contract.<br>"
                . "<span style=\"color: #dc3545;\">By acknowledging this message,"
                . " you understand that: Unless renewed, you will lose access to the system.</span></p>";
        }

        if ($this->isExpiryApproaching) {
            return "<p>Your AWS Hosting is going to expire soon.<br>"
                . "To ensure smooth operation, Please renew at your earliest convenience.</p>";
        }
        
        return "";
    }

    public function shouldShowWarningBanner()
    {
        return $this->isExpiryImminent && $this->gracePeriodEndDate;
    }

    public function getWarningBannerMsg()
    {
        if ($this->isExpired) {
            return "The AWS Hosting is expired. This system is currently in the grace period and will be discontinued"
                . "<span style=\"font-weight: bolder;\">".$this->getDaysRemainingForHuman($this->gracePeriodEndDate)."</span>"
                . ". Please contact the service provider.";
        }
        
        if ($this->isExpiryImminent) {
            return "The AWS Hosting is about to expire"
                . "<span style=\"font-weight: bolder;\">".$this->getDaysRemainingForHuman($this->expiryDate)."</span>"
                . ". Please contact the service provider.";
        }

        return '';
    }

    /**
     * Determines whether data should be fetched from the upstream source.
     *
     * Returns true on these occasions:
     * - Right before the expiry message popup is shown.
     * - If data has never been fetched before.
     * - If the last fetch was performed on a different day.
     * - If the system is down due to amc expiry, every 30 minutes.
     * - If the last fetch resulted in a 404 error, retries after 4 hours.
     * - If the last fetch resulted in any other error, retries after 2 hours.
     *
     * @return bool True if data should be fetched from upstream, false otherwise.
     */
    private function shouldFetchFromUpstream()
    {
        $lastFetchedAt = pref('axispro.amc_last_fetched_at');
        $lastFetchResponseCode = pref('axispro.amc_last_fetch_result');
        $now = Carbon::now();

        // Check if previously fetched, If not, fetch
        if (!$lastFetchedAt) {
            return true;
        }

        $lastFetchedAt = CarbonImmutable::parse($lastFetchedAt);

        // If system is already down, check every 30 minutes for any update
        if (
            $this->gracePeriodEndDate
            && $this->gracePeriodEndDate <= $this->now
            && $lastFetchedAt->addMinutes(30) <= $now
        ) {
            return true;
        }

        // If the response was a 404 it means the resource is not
        // yet registered upstream, Try again after 4 hours
        if (
            $lastFetchResponseCode == Response::HTTP_NOT_FOUND
            && $lastFetchedAt->addHours(4) <= $now
        ) {
            return true;
        }

        // If the response was an error, Try again after 2 hours
        if (
            $lastFetchResponseCode != Response::HTTP_OK
            && $lastFetchResponseCode != Response::HTTP_NOT_FOUND
            && $lastFetchedAt->addHours(2) <= $now
        ) {
            return true;
        }

        // If last fetched result is outdated fetch again
        if ($lastFetchedAt->toDateString() != $now->toDateString()) {
            return true;
        }

        return false;
    }

    /**
     * Check if the response from the server is valid
     *
     * @param array $responseData
     * @return boolean
     */
    private function isResponseValid($responseData)
    {
        if (
            empty($responseData)
            || empty($responseData['last_renewed_till'])
            || empty($responseData['config_modified_at'])
        ) {
            return false;
        }

        foreach (array_keys($this->config) as $k) {
            if (!array_key_exists($k, $responseData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Decide if we should update our local values based on the server
     *
     * @param array $serverValues
     * @return boolean
     */
    private function shouldUpdateLocalValues($serverValues)
    {
        // If the local settings have been modified, prioritize the local modification time.
        // Sometimes, due to client requests, we may need to modify the settings locally for
        // immediate resolution. These modifications should not be overridden by the
        // server values if the server's contents are outdated. If necessary, we can
        // request an update to the server contents again, which will trigger the sync.
        if ($this->localUpdatedAt && $this->localUpdatedAt->isAfter($serverValues['config_modified_at'])) {
            return false;
        }

        if ($this->lastRenewedTill != Carbon::parse($serverValues['last_renewed_till'])) {
            return true;
        }

        foreach (array_keys($this->config) as $k) {
            if ($serverValues[$k] != $this->config[$k]) {
                return true;
            }
        }
    }

    /**
     * Update the local configurations to be same as the server
     * 
     * This function forces a re-read to resync this object with the current values
     *
     * @param array $serverValues
     * @return void
     */
    private function updateLocalValues($serverValues)
    {
        foreach ([
            'amc_last_renewed_till' => 'last_renewed_till',
            'amc_duration_in_months' => 'duration_in_months',
            'amc_early_notice_days' => 'early_notice_days',
            'amc_late_notice_days' => 'late_notice_days',
            'amc_grace_days_after_expiry' => 'grace_days_after_expiry',
            'amc_server_updated_at' => 'config_modified_at',
            'amc_pwd_req_for_local_update' => 'password_required'
        ] as $localKey => $remoteKey) {
            DB::table('0_sys_prefs')->where('name', $localKey)->update(['value' => $serverValues[$remoteKey] ?? '']);
            pref(["axispro.{$localKey}" => $serverValues[$remoteKey] ?? '']);
        }

        DB::table('0_sys_prefs')->where('name', 'amc_system_updated_at')->update(['value' => date(DB_DATETIME_FORMAT)]);

        $this->read();
    }

    /**
     * Update the last fetch result and time for bookkeeping
     *
     * @param int $code
     * @return void
     */
    private function updateLastFetch($code)
    {
        DB::table('0_sys_prefs')->where('name', 'amc_last_fetched_at')->update(['value' => date(DB_DATETIME_FORMAT)]);
        DB::table('0_sys_prefs')->where('name', 'amc_last_fetch_result')->update(['value' => $code]);
    }

    /**
     * Returns the upstream endpoint
     *
     * @return string|null
     */
    public function getUpstreamEndPoint()
    {
        return config('app.amc.upstream.endpoint');
    }

    /**
     * Fetch the configuration from upstream server, if configured
     *
     * @return void
     */
    public function fetchFromUpstream()
    {
        // If the endpoint is not defined, we can't fetch the details
        if (empty($endpoint = $this->getUpstreamEndPoint())) {
            return;
        }

        $httpClient = new \GuzzleHttp\Client(['timeout' => 2]);

        try {
            $response = $httpClient->post($endpoint, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.config('app.amc.upstream.secret_key'),
                ],
                'form_params' => [
                    'instance_url' => config('app.root_url'),
                ]
            ]);
        
            $data = json_decode($response->getBody()->getContents(), true) ?: [];

            if (!$this->isResponseValid($data)) {
                $this->updateLastFetch(
                    $response->getStatusCode() == Response::HTTP_OK
                        ? 417
                        : $response->getStatusCode()
                );

                return ;
            }

            if ($this->shouldUpdateLocalValues($data)) {
                $this->updateLocalValues($data);
            }
            
            $this->updateLastFetch(Response::HTTP_OK);
        } catch (\GuzzleHttp\Exception\TransferException $e) {
            $this->updateLastFetch($e->getCode() == Response::HTTP_OK ? 500 : $e->getCode());
        }
    }
}
