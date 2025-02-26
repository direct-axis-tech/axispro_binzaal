<?php

use App\Models\EntityGroup;
use App\Notifications\Hr\GratuityAccrualNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

$path_to_root = "../ERP";

require_once __DIR__ . "/../ERP/includes/console_session.inc";
require_once __DIR__ . "/../ERP/hrm/helpers/gratuityAccrualHelpers.php";

if (
    empty(pref('hr.gratuity_payable_account'))
    || empty(pref('hr.gratuity_expense_account'))
) {
    die('Accounts not configured.');
}

$asOfDate = date(DB_DATE_FORMAT);
$transDate = Carbon::now()->endOfMonth()->format(DB_DATE_FORMAT);

$trans = GratuityAccrualHelpers::postGratuityAccruals($asOfDate, $transDate);

if ($trans) {
    $notification = new GratuityAccrualNotification([
        'as_of_date' => sql2date($asOfDate),
        'trans_date' => sql2date($transDate),
        'type' => $trans['type'],
        'trans_no' => $trans['trans_no'],
        'reference' => $trans['reference'],
        'view_link' => $trans['view_link']
    ]);

    $group = EntityGroup::find(EntityGroup::GRATUITY_ACCRUAL_NOTIFICATION);
    $users = $group->distinctMemberUsers();

    if (!blank($users)) {
        Notification::send($users, $notification);
    }
    echo "Accrual entries as of {$asOfDate} posted on {$transDate}. Ref: {$trans['reference']}";
    exit();
}

echo "Nothing to be posted";
exit();