<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class V3LinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v3:link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a symbolic link from "[app.dir]/v3" to "public"';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $v3Path = dirname(dirname(base_path())).'/v3';
        if (file_exists($v3Path)) {
            return $this->error('The "v3" directory already exists.');
        }

        $this->laravel->make('files')->link(
            public_path(), $v3Path
        );

        $this->info('The [<app.dir>/v3] directory has been linked.');
    }
}
