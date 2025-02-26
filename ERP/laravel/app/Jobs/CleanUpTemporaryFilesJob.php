<?php

namespace App\Jobs;

use Carbon\CarbonTimeZone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class CleanUpTemporaryFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $temporaryDownloadLinks = File::allFiles(storage_path('download'));
        foreach ($temporaryDownloadLinks as $file) {
            $lastModified = Carbon::createFromTimestamp(
                File::lastModified($file),
                CarbonTimeZone::create('utc')
            );
            if ($lastModified < now()->subMinutes(3)) {
                File::delete($file);
            }
        }
    }
}
