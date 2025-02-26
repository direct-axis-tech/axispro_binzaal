<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 11/17/2018
 * Time: 5:17 PM
 */
$path_to_root = ".";
include_once($path_to_root . "/config_db.php");
$connection = $db_connections[0];
$conn = mysqli_connect($connection["host"], $connection["dbuser"], $connection["dbpassword"], "",
    !empty($connection["port"]) ? $connection["port"] : 3306); // default port in mysql is 3306

mysqli_select_db($conn, $connection["dbname"]);


function getExpiringList()
{
    global $conn;
    $sql = "select c.trans_no, c.reference, c.debtor_no, d.name customer, d.debtor_email,
    group_concat(distinct(a.description)) description, date(a.created_at) as created_at,
    DATE_ADD(date(a.created_at), INTERVAL b.expired_in_days DAY) expires_at,
    DATE_ADD(DATE_ADD(date(a.created_at), INTERVAL b.expired_in_days DAY), 
    INTERVAL -(b.notify_before_days) DAY) notification_start_date,
    b.notify_before_days  
    from 0_debtor_trans_details a 
    left join 0_stock_master b on b.stock_id=a.stock_id 
    left join 0_debtor_trans c on c.trans_no=a.debtor_trans_no and c.`type`=a.debtor_trans_type 
    left join 0_debtors_master d on d.debtor_no=c.debtor_no 
    where b.notify_customer=1 and a.debtor_trans_type = 10 and
    curdate() >= DATE_ADD(DATE_ADD(date(a.created_at), INTERVAL b.expired_in_days DAY), INTERVAL -(b.notify_before_days) DAY) and
    b.expired_in_days <> 0 
    group by a.stock_id, c.debtor_no,c.trans_no ";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $list[] = $row;
        }
    }
    return $list;
}


require 'laravel/bootstrap/autoload.php';

use \DrewM\MailChimp\MailChimp;

$api_key = "0abd1d9fab043fb74a24a8b79de31bfe";
$list_id = 'bbd8fbbe4c'; // list ID
$template_id = 41181; // input your template ID
$subject = 'Important Notification - Axis Pro';

try {

    $mc = new MailChimp($api_key);
    $expiring_array = getExpiringList();
    foreach ($expiring_array as $row) {

        if (!empty($row['debtor_email'])) {

            $email_address = $row['debtor_email']; // where to send
            $message = "<h5>Hello " . $row['customer'] . "</h5>";
            $message .= "<p>Following services are near to expire.!</p>";
            $services = explode(",", $row['description']);
            $message .= "<p><ul>";
            foreach ($services as $s) {
                $message .= "<li>" . $s . " (Expiring on : " . $row['expires_at'] . ")</li>";
            }
            $message .= "</ul></p>";


            # 0. subscribe user if not subscribed
            $subscriber_hash = $mc->subscriberHash($email_address);
            $result = $mc->get("lists/{$list_id}/members/{$subscriber_hash}");
            if (!isset($result['status']) || 'subscribed' !== $result['status']) {
                $result = $mc->post("lists/{$list_id}/members", [
                    'email_address' => $email_address,
                    'status' => 'subscribed',
                ]);
            }


            # 1. create campaign
            $result = $mc->post('campaigns', [
                'type' => 'regular',
                'recipients' => [
                    'list_id' => $list_id,
                    'segment_opts' => [
                        'match' => 'all',
                        'conditions' => [
                            [
                                'condition_type' => 'EmailAddress',
                                'field' => 'EMAIL',
                                'op' => 'is',
                                'value' => $email_address
                            ]
                        ]
                    ],
                ],
                'settings' => [
                    'subject_line' => $subject,
                    'from_name' => 'Axis Pro CRM',
                    'reply_to' => 'inbox.bpn@gmail.com',
                    'template_id' => $template_id,
                ],
            ]);
            if (!isset($result['id']) || !$result['id'])
                return;
            $campaign_id = $result['id'];


            // 2. update campaign
            $result = $mc->put("campaigns/{$campaign_id}/content", [
                'template' => [
                    'id' => $template_id,
                    'sections' => [
                        'body_content' => $message
                    ]
                ],
            ]);


            // 3. send campaign
            $result = $mc->post("campaigns/{$campaign_id}/actions/send");
            $success = is_bool($result) ? $result : false;

        }

    }
} catch (Exception $e) {
    echo "Failed sending notifications";
}

mysqli_close($conn);

