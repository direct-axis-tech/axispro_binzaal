<?php

namespace App\Traits;

use Throwable;

trait MigratesData {

    /**
     * Migrate the data for current migration
     * 
     * @param callable $callback
     * @return void
     */
    public function migrateData(callable $callback) {
        try {
            call_user_func($callback);
        }

        catch (Throwable $e) {
            $this->down();

            throw $e;
        }
    }
}