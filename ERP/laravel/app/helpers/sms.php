<?php

use App\Models\SentHistory;

/**
 * Send Text Message Using RouteMobile api
 *
 * @param string $mobileNo
 * @param string $message
 * @return bool
 */
function sendTextMessageRouteMobile($mobileNo, $message)
{
    $configs = config('sms.providers.route_mobile');

    // Check if the service is configured
    if (count($configs) != count(array_filter($configs, function ($v) {return !is_null($v);} ))) {
        return false;
    }

    $httpClient = new GuzzleHttp\Client();

    try {
        $response = $httpClient->post($configs['endpoint'], [
            'form_params' => [
                'username' => $configs['username'],
                'password' => $configs['password'],
                'source' => $configs['source'],
                'destination' => $mobileNo,
                'message' => $message
            ]
        ]);
    
        $result = explode('|', (string)$response->getBody());
        
        // success
        if ($result[0] == 1701) {
            return $result[2];
        }

        // failed
        else {
            return false;
        }
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // connection errors and stuff
        return false;
    }
    
    // failed for unexpected reasons
    return false;
}

/**
 * Send Text Message Using Reason8 api
 *
 * @param string $mobileNo
 * @param string $message
 * @return false|string
 */
function sendTextMessageReason8($mobileNo, $message)
{
    $configs = config('sms.providers.reason8');

    // Check if the service is configured
    if (count($configs) != count(array_filter($configs))) {
        return false;
    }

    $httpClient = new GuzzleHttp\Client();

    try {
        $response = $httpClient->post($configs['endpoint'], [
            'headers' => [
                'X-Reson8-ID' => $configs['id'],
                'X-Reson8-Token' => $configs['token'],
                'api-key' => $configs['api_key'],
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'from' => $configs['from'],
                'to' => $mobileNo,
                'text' => $message
            ]
        ]);
    
        $result = json_decode($response->getBody(), true);
        
        // success
        if (0 == ($result['errorLevel'] ?? -1)) {
            return $result['requestID'];
        }

        // failure
        else {
            return false;
        }
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // connection errors and stuff
        return false;
    }
    
    // failed for unexpected reasons
    return false;
}

/**
 * Send Text Message Using Etisalat api
 *
 * @param string $mobileNo
 * @param string $message
 * @return false|string
 */
function sendTextMessageEtisalat($mobileNo, $message)
{
    $configs = config('sms.providers.etisalat');

    // Check if the service is configured
    if (count($configs) != count(array_filter($configs, function ($v) {return !is_null($v);}))) {
        return false;
    }

    $httpClient = new GuzzleHttp\Client();

    try {
        $endPoint = rtrim($configs['endpoint'], '/');
        $response = $httpClient->post($endPoint.'/login/user/', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'username' => $configs['username'],
                'password' => $configs['password']
            ]
        ]);
    
        $result = json_decode($response->getBody(), true);
        
        // Check authorisation failure
        if (!$result['token']) {
            return false;
        }

        $response = $httpClient->post($endPoint.'/campaigns/submissions/sms/nb', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $result['token'],
            ],
            'json' => [
                "msgCategory"=> "4.5",
                "senderAddr"=> $configs['sender'],
                "recipient"=> $mobileNo,
                "msg"=> $message
            ]
        ]);
    
        $result = json_decode($response->getBody(), true);

        if ($result && $result['jobId']) {
            return $result['jobId'];
        }

        return false;
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // connection errors and stuff
        return false;
    }
    
    // failed for unexpected reasons
    return false;
}

/**
 * Send a text message
 *
 * @param string $mobileNo
 * @param array $context
 * @return bool
 */
function sendSMS($mobileNo, $context)
{
    if (empty($template = pref('axispro.sms_template'))) {
        return false;
    }

    if (empty($provider = config('sms.provider'))) {
        return false;
    }

    if (!preg_match(UAE_MOBILE_NO_PATTERN, $mobileNo)) {
        return false;
    }
    
    $method = 'sendTextMessage'.Str::studly($provider);

    $mobileNo = preg_replace(UAE_MOBILE_NO_PATTERN, "971$2", $mobileNo);
    $message = strtr($template, [
        '{companyName}' => $context['company_name'],
        '{transType}' => $context['type_name'],
        '{transRef}' => $context['reference'],
        '{link}' => $context['link'],
        '{total}' => $context['total']
    ]);

    if ($result = $method($mobileNo, $message)) {
        SentHistory::create([
            'trans_type' => $context['type'],
            'trans_no' => $context['trans_no'],
            'trans_ref' => $context['reference'],
            'content' => $message,
            'sent_through' => NT_SMS,
            'sent_to' => $mobileNo,
            'sent_at' => date(DB_DATETIME_FORMAT),
            'resource_ref' => $result,
        ]);

        return true;
    }

    return false;
}

/**
 * Check if an email or sms is already sent once
 *
 * @param string $transType
 * @param string $transRef
 * @return boolean
 */
function SMSSentOnce($transType, $transRef)
{
    return isSentOnce($transType, $transRef, NT_SMS);
}

/**
 * Decide whether should send sms for this transaction automatically
 *
 * @param array $trans
 * @return boolean
 */
function shouldSendSMSAutomatically($trans)
{
    $isTransNew = (
        $trans['version'] == 0
        && DB::query()
            ->from('0_refs')
            ->whereType($trans['type'])
            ->whereReference($trans['reference'])
            ->count() == 1
    );

    return (
        pref('axispro.send_sms_automatically')
        && in_array($trans['type'], explode(',', $trans['should_send_sms'] ?: ''))
        && $isTransNew
        && $trans['tran_date'] == date(DB_DATE_FORMAT)
        && preg_match(UAE_MOBILE_NO_PATTERN, $trans['customer_mobile'])
        && ($trans['customer_mobile'] = preg_replace(UAE_MOBILE_NO_PATTERN, '971$2', $trans['customer_mobile']))
        && !SMSSentOnce($trans['type'], $trans['reference'])
    );
}