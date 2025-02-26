<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class IDEHelperALLCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ide-helper:all {--f|force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate all the helper files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (getenv('COMPOSER_DEV_MODE') || $this->option('force')) {
            Artisan::call('ide-helper:generate', [], $this->getOutput());
            Artisan::call('ide-helper:meta', [], $this->getOutput());
            Artisan::call('ide-helper:models', ['--nowrite' => true], $this->getOutput());
        }
    }
}
