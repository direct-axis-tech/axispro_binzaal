<?php

namespace App\Facades;

use App\Events\TransactionEventDispatcher;
use Illuminate\Support\Facades\Event as FacadesEvent;

class Event extends FacadesEvent 
{
    public static function dispatchAfterCommit($event)
    {
        app(TransactionEventDispatcher::class)->dispatchAfterCommit($event);
    }
}