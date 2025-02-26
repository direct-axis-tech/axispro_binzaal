<?php

use App\Models\SentHistory;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Send a billing email
 *
 * @param string $emailTo
 * @param array $context
 * @param array $attachments
 * @return bool
 */
function sendBillingEmail($emailTo, $context, $attachments = [], $subject = null, $body = null)
{
    $config = $GLOBALS['SysPrefs']->email_configs['billing'];

    if (is_null($subject)) {
        $subject = pref('axispro.email_subject');
    }

    if (is_null($body)) {
        $body = html_specials_decode(base64_decode(pref('axispro.email_template')));
    }

    if (
           empty($config['username'])
        || empty($config['password'])
        || empty($body)
        || empty($subject)
        || !filter_var($emailTo, FILTER_VALIDATE_EMAIL)
    ) {
        return false;
    }

    // Plain text convert new line and tabs
    $additional_replacements = [];
    if (strpos($body, '<div') === false) {
        $additional_replacements = [
            "\n" => '<br>',
            "\t" => '&nbsp;&nbsp;&nbsp;&nbsp;'
        ];
    }

    $link_label = data_get($context, 'link_label', data_get($context, 'reference', data_get($context, 'link', '')));
    $replacements = [
        '{companyName}' => data_get($context, 'company_name', ''),
        '{customerName}' => data_get($context, 'customer_name', ''),
        '{transType}' => data_get($context, 'type_name', ''),
        '{transRef}' => data_get($context, 'reference', ''),
        '{link}' => '<a href="'.data_get($context, 'link', '')."\">{$link_label}</a>",
        '{total}' => data_get($context, 'total', '')
    ];
    $subject = ucfirst(strtr($subject, $replacements));
    $body = strtr($body, array_merge($replacements, $additional_replacements));

    $embeddedImages = [
        'header_logo' => media_path('mail/header.png'),
        'footer_logo1' => media_path('mail/footer1.png'),
        'footer_logo2' => media_path('mail/footer2.png'),
        'footer_logo3' => media_path('mail/footer3.png'),
        'insta_logo' => media_path('mail/instagram.png'),
    ];

    $mail = new PHPMailer(true);
    $mail->isSMTP();                                              //Send using SMTP
    $mail->SMTPAuth   = true;         
    $mail->Host       = $config['smtp_host'];                     //Set the SMTP server to send through
    $mail->Username   = $config['username'];                      //SMTP username
    $mail->Password   = $config['password'];                      //SMTP password
    $mail->SMTPSecure = $config['protocol'];                      //Enable implicit TLS encryption
    $mail->Port       = $config['port'];

    $mail->setFrom($config['from'], $config['name']);              //Name is optional
    $mail->addAddress($emailTo);                                   //Name is optional
    $mail->isHTML(true);                                           //Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body    = $body;

    foreach ($embeddedImages as $key => $path) {
        if (strpos($body, "cid:$key") !== false) {
            $mail->addEmbeddedImage($path, $key);
        }
    }

    foreach ($attachments as $filePath => $readableFileName) {
        $mail->addAttachment($filePath, $readableFileName);
    }

    if ($result = $mail->send()) {
        SentHistory::create([
            'trans_type' => $context['type'],
            'trans_no' => $context['trans_no'],
            'trans_ref' => $context['reference'],
            'content' => "{$subject}\n\n{$body}",
            'sent_through' => NT_EMAIL,
            'sent_to' => $emailTo,
            'sent_at' => date(DB_DATETIME_FORMAT),
            'resource_ref' => 'true',
        ]);
    }

    return $result;
}

/**
 * Check if an email is already sent once
 *
 * @param string $transType
 * @param string $transRef
 * @return boolean
 */
function emailSentOnce($transType, $transRef)
{
    return isSentOnce($transType, $transRef, NT_EMAIL);
}

/**
 * Decide whether should send email for this transaction automatically
 *
 * @param array $trans
 * @return boolean
 */
function shouldSendEmailAutomatically($trans)
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
        pref('axispro.send_email_automatically')
        && in_array($trans['type'], explode(',', $trans['should_send_email'] ?: ''))
        && $isTransNew
        && $trans['tran_date'] == date(DB_DATE_FORMAT)
        && !empty($trans['customer_email'])
        && filter_var($trans['customer_email'], FILTER_VALIDATE_EMAIL)
        && !emailSentOnce($trans['type'], $trans['reference'])
    );
}