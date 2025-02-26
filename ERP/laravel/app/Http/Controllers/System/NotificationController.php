<?php

namespace App\Http\Controllers\System;

use App\Events\System\NotificationDeleted;
use App\Events\System\NotificationRead;
use App\Events\System\NotificationUnread;
use App\Http\Controllers\Controller;
use App\Models\DatabaseNotification;
use App\Models\Entity;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class NotificationController extends Controller {

    /**
     * Returns the paginated notifications
     */
    public function index(Request $request)
    {
        return $this->getBuilder($request)->paginate(6);
    }

    /**
     * Returns the paginated unread notifications
     */
    public function unread(Request $request)
    {
        return $this->getBuilder($request, true)->paginate();
    }

    /**
     * Marks a notification as read
     */
    public function markAsRead(DatabaseNotification $notification)
    {
        $notification->markAsRead();

        broadcast(new NotificationRead($notification));

        return response('', 204);
    }

    /**
     * Marks a notification as unread
     */
    public function markAsUnread(DatabaseNotification $notification)
    {
        $notification->markAsUnread();

        broadcast(new NotificationUnread($notification));

        return response('', 204);
    }

    /**
     * Remove a notification from the database
     */
    public function destroy(DatabaseNotification $notification)
    {
        $notification->delete();

        broadcast(new NotificationDeleted($notification));

        return response('', 204);
    }

    /**
     * Make the query builder for user notifications
     *
     * @param Request $request
     * @param boolean $unread
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBuilder(Request $request, $unread = false)
    {
        $builder = DatabaseNotification::query()
            ->from('0_notifications as notification')
            ->leftJoin('0_employees as employee', function (JoinClause $join) {
                $join->on('employee.id', '=', 'notification.notifiable_id')
                    ->where('notification.notifiable_type', '=', Entity::EMPLOYEE);
            })
            ->leftJoin('0_users as user', function (JoinClause $join) {
                $join->on('user.employee_id', '=', 'employee.id')
                    ->where('user.type', '=', Entity::EMPLOYEE);
            })
            ->selectRaw('notification.*, IFNULL(user.id, notification.notifiable_id) AS user_id')
            ->where(function($query) {
                $query->where('notification.notifiable_type', Entity::USER)
                    ->orWhereNotNull('user.id');
            })
            ->whereRaw('IFNULL(user.id, notification.notifiable_id) = ?', [$request->user()->id])
            ->orderBy('notification.created_at', 'desc');

        if ($unread) {
            $builder->whereNull('notification.read_at');
        }
        return $builder;
    }

    /**
     * Marks all notification as read
     */
    public function markAllAsRead(Request $request)
    {
        $request->validate([
            'notifications' => 'required|array',
            'notifications.*' => 'exists:0_notifications,id',
        ]);

        $notificationIds = $request->input('notifications');

        foreach ($notificationIds as $id) {
            $notification = DatabaseNotification::find($id);
            $notification->markAsRead();
            broadcast(new NotificationRead($notification));
        }

        return response('', 204);
    }

}