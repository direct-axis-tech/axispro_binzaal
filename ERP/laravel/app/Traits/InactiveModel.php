<?php

namespace App\Traits;

trait InactiveModel {

    /**
     * Scope this model to only include the active records
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('inactive', 0);
    }
}