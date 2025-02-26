<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputArgument;

class ReorderMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:reorder {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renames the migration to update the timestamp';

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
        $fileName = $this->argument('filename');
        $path = database_path('migrations');
        $fileToRename = $path . '/' . $fileName . '.php';

        if (File::exists($fileToRename)) {
            $newName = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}/', date('Y_m_d_His'), $fileName) . '.php';
            $newPath = $path . '/' . $newName;

            rename($fileToRename, $newPath);
            $this->info('Migration file renamed successfully.');
        } else {
            $this->error("Migration file '{$fileName}' not found.");
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['filename', InputArgument::REQUIRED, 'The name of the migration file'],
        ];
    }
}