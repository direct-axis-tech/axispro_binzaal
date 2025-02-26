<?php

namespace App\Events;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class TransactionEventDispatcher
{
    protected $queuedEvents = [];

    public function dispatchAfterCommit($event)
    {
        if (is_in_transaction()) {
            $this->queuedEvents[] = $event;
        } else {
            Event::dispatch($event);
        }
    }

    public function flushQueuedEvents()
    {
        foreach ($this->queuedEvents as $event) {
            Event::dispatch($event);
        }
        $this->queuedEvents = [];
    }
}