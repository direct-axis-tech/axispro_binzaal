<?php

namespace App\Jobs;

use App\Models\CalendarEvent;
use App\Models\CalendarEventType;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Event;

class TriggerCalendarEventsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $calendarEventTypes = CalendarEventType::all()->keyBy('id');
        $calendarEvents = CalendarEvent::query()
            ->whereNull('triggered_at')
            ->where('scheduled_at', '<=', (new Carbon())->toDateTimeString())
            ->get();

        foreach ($calendarEvents as $calendarEvent) {
            $calendarEvent->triggered_at = new Carbon();
            $calendarEvent->save();
            $eventClass = $calendarEventTypes->get($calendarEvent->type_id)->class;
            Event::dispatch(new $eventClass($calendarEvent));
        } 
    }
}
