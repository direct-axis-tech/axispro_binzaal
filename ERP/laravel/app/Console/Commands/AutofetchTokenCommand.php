<?php

namespace App\Console\Commands;

use App\Factories\AutofetchTokenFactory;
use Illuminate\Console\Command;
use Illuminate\Container\Container;

class AutofetchTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autofetch:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a token for autofetch';

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
        $token = Container::getInstance()
            ->make(AutofetchTokenFactory::class)
            ->make(['use-autofetch']);

        echo json_encode($token, JSON_PRETTY_PRINT);
    }
}
